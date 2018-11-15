<?php

namespace Febalist\LaravelSelectel;

use ArgentCrusade\Selectel\CloudStorage\Api\ApiClient as ArgentCrusadeApiClient;
use GuzzleHttp\Client;

class ApiClient extends ArgentCrusadeApiClient
{
    protected $logsEnabled;

    public function __construct(string $username, string $password, $logs = false)
    {
        parent::__construct($username, $password);

        $this->logsEnabled = $logs;
    }

    public function getHttpClient()
    {
        if (!$this->logsEnabled) {
            return parent::getHttpClient();
        }

        if (!is_null($this->httpClient)) {
            return $this->httpClient;
        }

        return $this->httpClient = new Client([
            'base_uri' => $this->storageUrl(),
            'headers' => [
                'X-Auth-Token' => $this->token(),
            ],
            'handler' => $this->createLoggingHandlerStack([
                '{method} {uri}',
                'RESPONSE: {code}',
            ]),
        ]);
    }

    public function request($method, $url, array $params = [])
    {
        $response = parent::request($method, $url, $params);

        if ($response->getStatusCode() == 401) {
            $this->token = null;
            $this->authenticate();

            return $this->request($method, $url, $params);
        }

        return $response;
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
