<?php
namespace Ruesin\Payments;

/**
 * Payments Class Loader 
 *
 * @method string submit()  提交
 * @method array back() 同步
 * @method array notify() 异步
 * 
 * @author Ruesin
 */
class PayLoader
{

    const SPACE = '\Ruesin\Payments\Lib';

    private static $types = [
        'alipay'   => 'Alipay',
        'malipay'  => 'Malipay',
        'wxnative' => 'WxNative'
    ];
    
    private static $_type = '';
    private static $_config = [];
    private static $_params = [];

    private static $_instance = null;

    private function __construct()
    {}

    private function __clone()
    {}

    /**
     * 获取实例
     *
     * @author Ruesin
     */
    private static function getInstance($type = '', $config = [], $params = [])
    {
        
        if (self::$_instance) return self::$_instance;
        
        $ltype = strtolower($type);
        
        $class = isset(self::$types[$ltype]) ? (self::SPACE . '\\' . self::$types[$ltype]) : (self::SPACE . '\\' . ucfirst($type));
        
        if (! class_exists($class)) {
            throw new \Exception('Payment does not exist!');
        }
        
        self::$_instance = new $class($config);
        
        return self::$_instance;
    }
    
    /**
     * 初始化
     *
     * @author Ruesin
     */
    public static function init($type = '', $config = [], $params = [])
    {
        if ($type != self::$_type || $config != self::$_config) {
            self::clearInstance();
        }
        
        return self::getInstance($type, $config, $params);
    }
    
    /**
     * 清除实例
     *
     * @author Ruesin
     */
    public static function clearInstance()
    {
        self::$_instance = null;
    }
    
    public function __set($name, $value)
    {
        $instance = self::getInstance();
        $instance->$name = $value;
    }
    
    public function __get($name)
    {
        $instance = self::getInstance();
        return $instance->$name;
    }
    
    public function __call($method, $parameters)
    {
        $instance = self::getInstance();
        return call_user_func_array([$instance, $method], $parameters);
    }
    
    public static function __callStatic($method, $parameters)
    {
        $instance = self::getInstance();
        return call_user_func_array([$instance, $method], $parameters);
    }
    
    
}