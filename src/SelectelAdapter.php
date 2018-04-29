<?php

namespace Febalist\LaravelSelectel;

use ArgentCrusade\Flysystem\Selectel\SelectelAdapter as ArgentCrusadeAdapter;
use ArgentCrusade\Selectel\CloudStorage\Api\ApiClient;
use ArgentCrusade\Selectel\CloudStorage\Container;
use ArgentCrusade\Selectel\CloudStorage\Exceptions\ApiRequestFailedException;
use DateTimeInterface;

class SelectelAdapter extends ArgentCrusadeAdapter
{
    /** @var Container $container */
    protected $container;
    protected $tempUrlKey;

    public function getTemporaryUrl($path, DateTimeInterface $expiration, array $options = [])
    {
        if (!$this->tempUrlKey) {
            $this->tempUrlKey = config('app.key');
            $this->setTempUrlKey($this->tempUrlKey);
        }

        $url = $this->getUrl($path);
        $expiration = $expiration->getTimestamp();
        $sig = $this->sigTempUrl($path, $expiration, $this->tempUrlKey);

        $res = $url.'?temp_url_sig='.$sig.'&temp_url_expires='.$expiration;
        if ($options['name'] ?? null) {
            $res .= '&filename='.urlencode($options['name']);
        }

        return $res;
    }

    /** @return ApiClient */
    protected function api()
    {
        return $this->container->apiClient();
    }

    protected function setTempUrlKey($key)
    {
        $url = $this->api()->storageUrl().'/'.$this->container->name();
        $response = $this->api()->request('POST', $url, [
            'headers' => [
                'X-Auth-Token' => $this->api()->token(),
                'X-Container-Meta-Temp-URL-Key' => $key,
            ],
        ]);

        if ($response->getStatusCode() !== 202) {
            throw new ApiRequestFailedException('Unable to set container temp URL key.', $response->getStatusCode());
        }
    }

    protected function sigTempUrl($path, $expiration, $key)
    {
        $path = '/'.$this->container->name().str_start($path, '/');
        $sig_body = "GET\n$expiration\n$path";

        return hash_hmac('sha1', $sig_body, $key);
    }

}
