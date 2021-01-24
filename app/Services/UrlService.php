<?php

namespace App\Services;

use App\Entities\Url;
use App\Exceptions\CacheFallbackException;
use App\Repositories\UrlRepository;
use Aws\DynamoDb\DynamoDbClient;
use Doctrine\DBAL\DBALException;
use Doctrine\ORM\EntityManagerInterface;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class UrlService
{
    protected EntityManagerInterface $em;
    protected ?UrlRepository $urlRepository;
    protected DynamoDbClient $dynamo;

    const dynamoTableName = 'VatsimUrlShortenerUrls';

    public function __construct(DynamoDbClient $dynamo, EntityManagerInterface $em, ?UrlRepository $urlRepository)
    {
        $this->dynamo = $dynamo;
        $this->em = $em;
        $this->urlRepository = $urlRepository;
    }

    public function getRedirectForUrl(string $domain, string $url = null, string $prefix = null): Url
    {
        if ($this->urlRepository === null) {
            return $this->findModelInCacheOrFail($domain, $url, $prefix);
        }

        $urlEntity = null;
        try {
            $urlEntity = $this->urlRepository->findByDomainAndUrlAndPrefix($domain, $url ?: '/', $prefix);
        } catch (DBALException $e) {
            report($e);

            return $this->findModelInCacheOrFail($domain, $url, $prefix);
        }

        if (!$urlEntity) {
            throw new NotFoundHttpException();
        }

        return $urlEntity;
    }

    private function findModelInCacheOrFail(string $domain, ?string $url, ?string $prefix): Url
    {
        $urlEntity = $this->fallbackToCache($domain, $url, $prefix);
        if ($urlEntity) {
            return $urlEntity;
        } else {
            throw new CacheFallbackException('Database unavailable and URL not in fallback cache.');
        }
    }

    private function fallbackToCache(string $domain, string $url = null, string $prefix = null): ?Url
    {
        Log::warning(
            'Failed to retrieve URL from database, attempting to retrieve from cache.',
            ['domain' => $domain, 'prefix' => $prefix, 'url' => $url]
        );

        $cachedModel = $this->getCachedUrl($domain, $url, $prefix);
        if ($cachedModel) {
            Log::info(
                'Successfully retrieved cached version of URL.',
                [
                    'domain' => $domain,
                    'prefix' => $prefix,
                    'url' => $url,
                    'last_updated' => $cachedModel->getUpdatedAt(),
                ]
            );
        } else {
            Log::error(
                'Failed to retrieve cached version of URL.',
                ['domain' => $domain, 'prefix' => $prefix, 'url' => $url]
            );
        }

        return $cachedModel;
    }

    public function getCachedUrl(string $domain, string $url = null, string $prefix = null): ?Url
    {
        return Cache::get(sprintf(Url::URL_CACHE_KEY, $domain, $prefix, $url));
    }

    public function addUrlToCache(Url $url): void
    {
        $key = sprintf(
            Url::URL_CACHE_KEY,
            $url->getDomain()->getUrl(),
            $url->isPrefixed() ? $url->getOrganization()->getPrefix() : null,
            $url->getUrl()
        );

        Cache::set($key, $url);

        $this->dynamo->putItem([
            'TableName' => self::dynamoTableName,
            'Item' => [
                'key' => [
                    'S' => $url->getFullUrl(),
                ],
                'value' => [
                    'S' => $url->getRedirectUrl(),
                ],
                'updated_at' => [
                    'N' => (string) Carbon::now()->getTimestamp(),
                ],
            ],
        ]);
    }

    public function removeUrlFromCache(Url $url)
    {
        Cache::forget(
            sprintf(
                Url::URL_CACHE_KEY,
                $url->getDomain()->getUrl(),
                $url->isPrefixed() ? $url->getOrganization()->getPrefix() : null,
                $url->getUrl()
            )
        );

        $this->dynamo->deleteItem([
            'TableName' => self::dynamoTableName,
            'Key' => [
                'key' => [
                    'S' => $url->getFullUrl(),
                ],
            ],
        ]);
    }
}
