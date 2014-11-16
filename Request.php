<?php
namespace vkapi;

use vkapi\exceptions\UnknownPropertyException;

class Request
{
    private $_params = [];
    private $method;

    public function __construct($method)
    {
        $this->method = $method;
    }

    public function perform()
    {
        return VkApi::getInstance()->performRequest($this);
    }

    /**
     * @param $params
     *
     * @return $this
     * @throws UnknownPropertyException
     */
    public function setParams($params)
    {
        foreach ($params as $name => $value) {
            $this->set($name, $value);
        }
        return $this;
    }

    public function set($name, $value)
    {
        if ($this->hasArgument($name)) {
            $this->_params[$name] = $value;
        } else {
            throw new UnknownPropertyException('Setting unknown property: ' . get_class($this) . '::' . $name);
        }
        return $this;
    }

    public function count($count)
    {
        $this->set('count', $count);
        return $this;
    }

    public function offset($offset)
    {
        $this->set('offset', $offset);
        return $this;
    }

    public function getParams()
    {
        return $this->_params;
    }

    public function getMethod()
    {
        return $this->method;
    }

    public function hasArgument($name)
    {
        return true;
    }

    public function getResponseObject()
    {
        return null;
    }
}