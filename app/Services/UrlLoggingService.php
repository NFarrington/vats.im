<?php

namespace App\Services;

use App\Domain\ShortUrlVisitDto;
use App\Entities\Url;
use App\Entities\UrlAnalytics;
use App\Entities\User;
use App\Repositories\UrlRepository;
use Doctrine\ORM\EntityManagerInterface;

class UrlLoggingService
{
    private EntityManagerInterface $entityManager;
    private UrlRepository $urlRepository;

    public function __construct(EntityManagerInterface $entityManager, UrlRepository $urlRepository)
    {
        $this->entityManager = $entityManager;
        $this->urlRepository = $urlRepository;
    }

    public function logShortUrlVisit(ShortUrlVisitDto $shortUrlVisit)
    {
        /** @var Url|null $url */
        $url = null;
        if ($urlId = $shortUrlVisit->getUrlId()) {
            $url = $this->urlRepository->find($urlId);
        }

        /** @var User|null $user */
        $user = null;
        if ($userId = $shortUrlVisit->getUserId()) {
            $user = $this->entityManager->getReference(User::class, $userId);
        }

        if ($url && $url->isAnalyticsDisabled()) {
            return;
        }

        $urlAnalytic = new UrlAnalytics();
        $urlAnalytic->setUrl($url);
        $urlAnalytic->setUser($user);
        $urlAnalytic->setRequestTime($shortUrlVisit->getRequestTime());
        $urlAnalytic->setHttpHost($shortUrlVisit->getRequestRoot());
        $urlAnalytic->setHttpReferer($shortUrlVisit->getRefererHeader());
        $urlAnalytic->setHttpUserAgent($shortUrlVisit->getUserAgent());
        $urlAnalytic->setRemoteAddr($shortUrlVisit->getRequestIp());
        $urlAnalytic->setRequestUri($shortUrlVisit->getRequestPath());
        $urlAnalytic->setGetData($shortUrlVisit->getQuery());
        $urlAnalytic->setResponseCode($shortUrlVisit->getResponseStatus());

        $this->entityManager->persist($urlAnalytic);
        $this->entityManager->flush();
    }
}
