<?php

namespace App\Events;

use App\Domain\ShortUrlVisitDto;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ShortUrlVisited
{
    use Dispatchable;

    public ShortUrlVisitDto $shortUrlVisit;

    public function __construct(string $requestTime, Request $request, Response $response, ?int $urlId = null)
    {
        $this->shortUrlVisit = new ShortUrlVisitDto($requestTime, $request, $response, $urlId);
    }
}
