<?php
namespace vkapi;

use vkapi\exceptions\ResponseErrorException;
use vkapi\exceptions\UnknownResponseException;

/**
 * Class Response
 *
 * @package vkapi
 */
class Response
{
    private $stringResponse;
    private $arrayResponse;

    private $requestUrl;

    public function  __construct($stringResponse)
    {
        $this->stringResponse = $stringResponse;
    }

    public function setRequestUrl($url)
    {
        $this->requestUrl = $url;
    }

    public function getStringResponse()
    {
        return $this->stringResponse;
    }

    /**
     * @return array
     * @throws UnknownResponseException
     */
    public function getArrayResponse()
    {
        if (!isset($this->arrayResponse)) {
            $arrayData = json_decode($this->getStringResponse(), true);
            $arrayResponse = $this->prepareResponse($arrayData);
            $this->arrayResponse = $arrayResponse;
        }

        return $this->arrayResponse;
    }

    private function prepareResponse($data)
    {
        $result = [];
        if (isset($data['error'])) {
            $this->handleError($data['error']);
        } elseif (isset($data['response'])) {
            $result = $data['response'];
        } elseif (isset($data['access_token'])) {
            $result = $data['access_token'];
        } else {
            $string = $this->getStringResponse();
            throw new UnknownResponseException("Response was {$string} on request url {$this->requestUrl}");
        }

        return $result;
    }

    private function handleError($error)
    {
        $message = '';
        if (isset($error['error_msg'])) {
            $message = $error['error_msg'];
        }
        $code = 0;
        if (isset($error['error_code'])) {
            $code = $error['error_code'];
        }
        $requestParams = [];
        if (isset($error['request_params'])) {
            $requestParams = $error['request_params'];
        }
        $requestParams = json_encode($requestParams);
        $message = $message . " with params {$requestParams}";
        throw new ResponseErrorException($message, $code);
    }
}