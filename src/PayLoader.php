<?php
namespace Ruesin\Payments;

class PayLoader
{
    const SPACE = '\Ruesin\Payments\Lib';
    
    private static $types = [
        'alipay' => 'Alipay',
        'malipay' => 'Malipay',
        'wxnative' => 'WxNative',
    ];
    
    public static function init($type = '', $config = [], $params = [])
    {
        $ltype = strtolower($type);
        
        $class = isset(self::$types[$ltype]) ? (self::SPACE.'\\'.self::$types[$ltype]) : (self::SPACE.'\\'.ucfirst($type));
        
        if (!class_exists($class)) {
            throw new \Exception('Payment does not exist!');
        }
        
        return new $class($config); 
    }
    
    private static function formatParams($params)
    {
        return $params;
    }
}