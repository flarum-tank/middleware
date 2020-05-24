<?php
/**
 * (c) 2019  Matthew Kilgore <matthew@kilgore.dev>
 *
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 *
 */

namespace Tank\Middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class InsertDNSPrefetch implements MiddlewareInterface {

    /**
     * @inheritDoc
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $response = $handler->handle($request);
        $body = $response->getBody()->getContents();
        preg_match_all('#\bhttps?://[^\s()<>]+(?:\([\w\d]+\)|([^[:punct:]\s]|/))#', $body, $matches, PREG_OFFSET_CAPTURE);

        $dnsPrefetch = collect($matches[0])->map(function ($item) {
            $replace = [
                '/https:/' => '',
                '/http:/' => ''
            ];
            $domain = preg_replace(array_keys($replace), array_values($replace), $item[0]);
            $domain = explode(
                '/',
                str_replace('//', '', $domain)
            );
            return "<link rel=\"dns-prefetch\" href=\"//{$domain[0]}\">";
        })->unique()->implode("\n");

        $replace = [
            '#<head>(.*?)#' => "<head>\n{$dnsPrefetch}"
        ];
        $newBody = preg_replace(array_keys($replace), array_values($replace), $body);
        $response->getBody()->rewind();
        $response->getBody()->write($newBody);
        return $response;
    }
}
