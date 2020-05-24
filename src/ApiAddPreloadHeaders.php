<?php

namespace Tank\Middleware;

use Illuminate\Support\Str;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class ApiAddPreloadHeaders implements MiddlewareInterface {

    /**
     * @inheritDoc
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $response = $handler->handle($request);
        $body = $response->getBody()->getContents();
        preg_match('#(?:"avatarUrl":)(?="h).+?(?=")#', $body, $matches);
        $assets = collect($matches)->map(function ($item) {
            $replace = [
                '/https:/' => '',
                '/http:/' => '',
                '/"/' => '',
                '/\\\\/' => '',
                '/avatarUrl:/' => ''
            ];
            $domain = preg_replace(array_keys($replace), array_values($replace), $item);
            return $domain;
        })->unique();

        $dnsPrefetch = "";
        foreach ($assets as $asset) {
            $linkTypeMap = [
                '.CSS'  => 'style',
                '.JS'   => 'script',
                '.BMP'  => 'image',
                '.GIF'  => 'image',
                '.JPG'  => 'image',
                '.JPEG' => 'image',
                '.PNG'  => 'image',
                '.SVG'  => 'image',
                '.TIFF' => 'image',
                '.WEBP' => 'image'
            ];
            $type = collect($linkTypeMap)->first(function ($type, $extension) use ($asset) {
                return Str::contains(strtoupper($asset), $extension);
            });
            $dnsPrefetch .= "<{$asset}>; rel=preload; as={$type}; ";
        }

        return $response->withAddedHeader('Link', $dnsPrefetch);
    }
}
