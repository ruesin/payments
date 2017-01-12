<?php
namespace Ruesin\Payments\Lib;

abstract class PayBase
{
    private $config = [];
    
    public function __construct($config = [])
    {
        $this->setConfig($config);
    }
    
    protected function setConfig($config = [])
    {
        $this->config = $config;
    }
    
    protected function getConfig($key = '')
    {
        return $key ? (isset($this->config[$key]) ? $this->config[$key] : '') : $this->config;
    }
    
    abstract function buildRequestHtml($order,$params);
    abstract function notify();
    abstract function back();
}