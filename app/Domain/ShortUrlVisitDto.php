<?php

namespace App\Domain;

use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Symfony\Component\HttpFoundation\Response;

class ShortUrlVisitDto
{
    private string $requestTime;
    private array $query;
    private ?int $urlId;
    private string $requestRoot;
    private ?string $refererHeader;
    private ?string $userAgent;
    private ?string $requestIp;
    private string $requestPath;
    private int $responseStatus;
    private ?int $userId;

    public function __construct(string $requestTime, Request $request, Response $response, ?int $urlId)
    {
        $this->requestTime = $requestTime;
        $this->requestRoot = $request->root();
        $this->refererHeader = $request->headers->get('referer');
        $this->userAgent = $request->userAgent();
        $this->requestIp = $request->ip();
        $this->requestPath = $request->path();
        $this->responseStatus = $response->getStatusCode();
        $this->urlId = $urlId;
        $this->userId = $request->user() ? $request->user()->getId() : null;
        $this->query = Arr::wrap($request->query());
    }

    public function getRequestTime(): string
    {
        return $this->requestTime;
    }

    public function getQuery()
    {
        return $this->query;
    }

    public function getUrlId(): ?int
    {
        return $this->urlId;
    }

    public function getRequestRoot(): string
    {
        return $this->requestRoot;
    }

    public function getUserAgent(): ?string
    {
        return $this->userAgent;
    }

    public function getRequestIp(): ?string
    {
        return $this->requestIp;
    }

    public function getRequestPath(): string
    {
        return $this->requestPath;
    }

    public function getResponseStatus(): int
    {
        return $this->responseStatus;
    }

    public function getRefererHeader()
    {
        return $this->refererHeader;
    }

    public function getUserId(): ?int
    {
        return $this->userId;
    }
}
