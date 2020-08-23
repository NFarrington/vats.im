<?php

namespace App\Http\Controllers;

use App\Events\ShortUrlVisited;
use App\Services\UrlService;
use Exception;
use Illuminate\Contracts\Debug\ExceptionHandler;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class UrlController extends Controller
{
    private UrlService $urlService;

    public function __construct(UrlService $urlService)
    {
        $this->urlService = $urlService;
    }

    /**
     * Redirect a short URL to the intended URL.
     *
     * @param Request $request
     * @param string|null $prefix
     * @param string|null $shortUrl
     * @return Response
     * @throws Exception
     */
    public function redirect(Request $request, string $prefix = null, string $shortUrl = null): Response
    {
        if ($prefix && !$shortUrl) {
            $shortUrl = $prefix;
            $prefix = null;
        }

        $url = null;
        try {
            $ipAddressRegex = '(?:(?:25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.){3}(?:25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)';
            if (preg_match(sprintf('/^%s$/', $ipAddressRegex), $request->getHost()) === 1) {
                $url = $this->urlService->getRedirectForUrl(config('app.url').'/', $shortUrl, $prefix);
            } else {
                $url = $this->urlService->getRedirectForUrl($request->root().'/', $shortUrl, $prefix);
            }

            $response = redirect()->to($url->getRedirectUrl());
        } catch (NotFoundHttpException $e) {
            $response = app(ExceptionHandler::class)->render($request, $e);
        }

        event(new ShortUrlVisited($_SERVER['REQUEST_TIME'], $request, $response, $url ? $url->getId() : null));

        return $response;
    }
}
