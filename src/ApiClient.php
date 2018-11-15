<?php

namespace Febalist\LaravelSelectel;

use ArgentCrusade\Selectel\CloudStorage\Api\ApiClient as ArgentCrusadeApiClient;
use ArgentCrusade\Selectel\CloudStorage\Exceptions\AuthenticationFailedException;
use GuzzleHttp\Client;
use RuntimeException;

class ApiClient extends ArgentCrusadeApiClient
{
    protected $logsEnabled;

    public function __construct(string $username, string $password, $logsEnabled = false)
    {
        parent::__construct($username, $password);

        $this->logsEnabled = $logsEnabled;
    }

    public function token()
    {
        return cache("selectel:$this->username:token");
    }

    public function storageUrl()
    {
        return cache("selectel:$this->username:storageUrl");
    }

    public function authenticate()
    {
        $response = $this->authenticationResponse();

        if (!$response->hasHeader('X-Auth-Token')) {
            throw new AuthenticationFailedException('Given credentials are wrong.', 403);
        }

        if (!$response->hasHeader('X-Storage-Url')) {
            throw new RuntimeException('Storage URL is missing.', 500);
        }

        $expiration = floor($response->getHeaderLine('X-Expire-Auth-Token') / 60);

        cache()->put("selectel:$this->username:token", $response->getHeaderLine('X-Auth-Token'), $expiration);
        cache()->put("selectel:$this->username:storageUrl", $response->getHeaderLine('X-Storage-Url'), $expiration);

        $this->httpClient = null;
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

        if ($response->getStatusCode() === 401) {
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
