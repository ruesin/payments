<?php
namespace Ruesin\Payments;

class PayLoader
{

    const SPACE = '\Ruesin\Payments\Lib';

    private static $types = [
        'alipay' => 'Alipay',
        'malipay' => 'Malipay',
        'wxnative' => 'WxNative'
    ];

    private static $payment = null;

    private static $_instance = null;

    private function __construct()
    {}

    private function __clone()
    {}

    private static function getInstance()
    {
        if (! (self::$_instance instanceof self)) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }

    /**
     * 初始化
     *
     * @author Ruesin
     */
    public static function init($type = '', $config = [], $params = [])
    {
        self::initPayment($type, $config, $params);
        return self::getInstance();
    }

    /**
     * 重新初始化
     *
     * @author Ruesin
     */
    public static function reInit($type = '', $config = [], $params = [])
    {
        self::$payment = null;
        return self::init($type, $config, $params);
    }

    /**
     * 初始化支付方式
     *
     * @author Ruesin
     */
    private static function initPayment($type = '', $config = [], $params = [])
    {
        if (self::$payment) return true;
        
        $ltype = strtolower($type);
        
        $class = isset(self::$types[$ltype]) ? (self::SPACE . '\\' . self::$types[$ltype]) : (self::SPACE . '\\' . ucfirst($type));
        
        if (! class_exists($class)) {
            throw new \Exception('Payment does not exist!');
        }
        
        self::$payment = new $class($config);
    }

    /**
     * 请求
     *
     * @author Ruesin
     */
    public function submit($order = [], $params = [])
    {
        return self::$payment->buildRequestHtml($order, $params);
    }

    /**
     * 同步
     *
     * @author Ruesin
     */
    public function back()
    {
        return self::$payment->back();
    }

    /**
     * 异步
     *
     * @author Ruesin
     */
    public function notify()
    {
        return self::$payment->notify();
    }

    private static function formatParams($params)
    {
        return $params;
    }
}