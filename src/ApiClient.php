<?php

namespace Febalist\LaravelSelectel;

use ArgentCrusade\Selectel\CloudStorage\Api\ApiClient as ArgentCrusadeApiClient;
use GuzzleHttp\Client;

class ApiClient extends ArgentCrusadeApiClient
{
    public function getHttpClient()
    {
        if (!is_null($this->httpClient)) {
            return $this->httpClient;
        }

        return $this->httpClient = new Client([
            'base_uri' => $this->storageUrl(),
            'headers' => [
                'X-Auth-Token' => $this->token(),
            ],
            'handler' => $this->createLoggingHandlerStack([
                '{method} {uri} HTTP/{version} {req_body}',
                'RESPONSE: {code} {res_header_Content-Length} - {res_body}',
            ]),
        ]);
    }

    protected function createLoggingHandlerStack(array $messageFormats)
    {
        $stack = \GuzzleHttp\HandlerStack::create();

        collect($messageFormats)->each(function ($messageFormat) use ($stack) {
            $stack->unshift(
                $this->createGuzzleLoggingMiddleware($messageFormat)
            );
        });

        return $stack;
    }

    private function createGuzzleLoggingMiddleware(string $messageFormat)
    {
        return \GuzzleHttp\Middleware::log(
            logger(),
            new \GuzzleHttp\MessageFormatter($messageFormat),
            'debug'
        );
    }
}
