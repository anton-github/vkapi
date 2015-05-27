<?php
namespace vkapi;

use vkapi\exceptions\NoResponseException;
use vkapi\exceptions\VkApiException;

class VkApi extends Singleton
{
    private $apiUrl = "https://api.vk.com/method/";
    protected $version = '5.33';
    protected $token;
    protected $tokensRange;
    protected $connectionTimeout = 30;
    protected $retriesConnectionCount = 3;
    protected $maxRequestsPerSecond = null;

    private $askPeriod;
    private $curl;
    private $lastAskTime;

    public function setToken($token)
    {
        $this->token = $token;

        return $this;
    }

    public function setTokensRange(array $tokens)
    {
        $this->tokensRange = $tokens;

        return $this;
    }

    protected function getToken()
    {
        $result = null;
        if (isset($this->token)) {
            $result = $this->token;
        } elseif ($this->tokensRange) {
            $count = count($this->tokensRange);
            $i = rand(0, $count - 1);
            $result = $this->tokensRange[$i];
        }

        return $result;
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

    public function setCurl($curl)
    {
        $this->curl = $curl;

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
        if ($token = $this->getToken()) {
            $params['access_token'] = $token;
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
            curl_setopt($curl, CURLOPT_TIMEOUT, 30);
            curl_setopt($curl, CURLOPT_VERBOSE, true);
            if (isset($this->connectionTimeout)) {
                curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, $this->connectionTimeout);
            }
            $this->curl = $curl;
        }

        return $this->curl;
    }

    private function isRequestFrequencyLimited()
    {
        return $this->maxRequestsPerSecond !== null;
    }

    private function curlAskExec($ch)
    {
        if ($this->isRequestFrequencyLimited()) {
            $this->checkRest();
        }
        $result = curl_exec($ch);
        if ($this->isRequestFrequencyLimited()) {
            $this->updatePoint();
        }

        return $result;
    }

    private function checkRest()
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
    }

    private function updatePoint()
    {
        $this->lastAskTime = $this->getMillisecondsTime();
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