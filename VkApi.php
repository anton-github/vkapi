<?php
namespace vkapi;

use vkapi\exceptions\NoResponseException;
use vkapi\exceptions\VkApiException;

class VkApi extends Singleton
{
    private $apiUrl = "https://api.vk.com/method/";
    protected $version = '5.25';
    protected $token;
    protected $connectionTimeout = 30;
    protected $retriesConnectionCount = 3;
    protected $maxRequestsPerSecond = 3;

    private $askPeriod;
    private $curl;
    private $lastAskTime;

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
        if ($request->isSeparate()) {
            $url = $method;
        } else {
            $url = $this->apiUrl . $method;
        }
        $params = $request->getParams();
        $params['v'] = $this->version;
        if (isset($this->token)) {
            $params['access_token'] = $this->token;
        }
        $url = $url . '?' . http_build_query($params);

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
        $curl = $this->getCurl();
        curl_setopt($curl, CURLOPT_URL, $url);
        $maxCount = 1;
        if (isset($this->retriesConnectionCount)) {
            $maxCount = $this->retriesConnectionCount;
        }

        $result = false;
        $count = 0;
        while ($result == false && $count < $maxCount) {
            $result = $this->curlAskExec($curl);
            $count++;
        }

        if ($result == false) {
            throw new NoResponseException("Try to perform request to url {$url} {$count} times with no result");
        }

        return $result;
    }

    private function getCurl()
    {
        if (!isset($this->curl)) {
            $curl = curl_init();
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
            curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
            if (isset($this->connectionTimeout)) {
                curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, $this->connectionTimeout);
            }
            $this->curl = $curl;
        }

        return $this->curl;
    }

    private function curlAskExec($ch)
    {
        if (isset($this->lastAskTime)) {
            $period = $this->getAskPeriod();
            $lastTime = $this->lastAskTime;
            $now = $this->getMillisecondsTime();
            $nextTime = $lastTime + $period;
            if ($now < $nextTime) {
                $wait = $nextTime - $now;
                usleep($wait * 1000);
            }
        }
        $result = curl_exec($ch);
        $this->lastAskTime = $this->getMillisecondsTime();

        return $result;
    }

    private function getMillisecondsTime()
    {
        return round(microtime(true) * 1000);
    }

    private function getAskPeriod()
    {
        if (!isset($this->askPeriod)) {
            if ($this->maxRequestsPerSecond <= 0) {
                throw new VkApiException('Max requests per second less than 1!');
            }
            $this->askPeriod = round(1000 / $this->maxRequestsPerSecond);
        }

        return $this->askPeriod;
    }
}