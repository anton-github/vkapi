<?php
namespace vkapi;

use vkapi\exceptions\NoResponseException;

class VkApi extends Singleton
{
    private $apiUrl = "https://api.vk.com/method/";
    protected $version = '5.25';
    protected $token;
    protected $connectionTimeout = 30;
    protected $retriesConnectionCount = 3;

    private $curl;

    public function setToken($token)
    {
        $this->token = $token;
        return $this;
    }

    public function setTimeout($timeout)
    {
        $this->connectionTimeout = $timeout;
        return $this;
    }

    public function setRetriesCount($count)
    {
        $this->retriesConnectionCount = $count;
        return $this;
    }

    /**
     * @param Request $request
     *
     * @return array
     */
    public function performRequest(Request $request)
    {
        $url = $this->buildRequestUrl($request);
        $stringResponse = $this->ask($url);
        $response = new Response($stringResponse);
        $response->setRequestUrl($url);
        return $response->getArrayResponse();
    }

    private function buildRequestUrl(Request $request)
    {
        $method = $request->getMethod();
        $params = $request->getParams();
        $params['v'] = $this->version;
        if (isset($this->token)) {
            $params['access_token'] = $this->token;
        }
        $url = $this->apiUrl . $method . '?' . http_build_query($params);
        return $url;
    }

    private function ask($url)
    {
        return $this->curlAsk($url);
    }

    /**
     * @param string $url
     *
     * @return string
     * @throws NoResponseException
     */
    private function curlAsk($url)
    {
        if (!isset($this->curl)) {
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
            if (isset($this->connectionTimeout)) {
                curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $this->connectionTimeout);
            }
            $this->curl = $ch;
        }
        curl_setopt($this->curl, CURLOPT_URL, $url);
        $maxCount = 1;
        if (isset($this->retriesConnectionCount)) {
            $maxCount = $this->retriesConnectionCount;
        }

        $result = false;
        $count = 0;
        while ($result == false && $count < $maxCount) {
            $result = curl_exec($this->curl);
            $count++;
        }

        if ($result == false) {
            throw new NoResponseException("Try to perform request to url {$url} {$count} times with no result");
        }

        return $result;
    }
}