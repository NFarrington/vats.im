<?php

namespace App\Listeners;

use App\Events\ShortUrlVisited;
use App\Services\UrlLoggingService;
use Illuminate\Contracts\Queue\ShouldQueue;

class LogShortUrlVisit implements ShouldQueue
{
    protected UrlLoggingService $urlLoggingService;

    public function __construct(UrlLoggingService $urlLoggingService)
    {
        $this->urlLoggingService = $urlLoggingService;
    }

    public function handle(ShortUrlVisited $event)
    {
        $this->urlLoggingService->logShortUrlVisit($event->shortUrlVisit);
    }
}
