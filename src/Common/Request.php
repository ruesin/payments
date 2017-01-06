<?php
namespace Ruesin\Payments\Common;

class Request
{
    public static function curl($url = '', $params = '', $ssl = '', $cacert_url = '', $second = 30)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_TIMEOUT, $second);
        curl_setopt($ch, CURLOPT_URL, $url);
        
        curl_setopt($ch, CURLOPT_HEADER, false); // 过滤HTTP头
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);  // 显示输出结果
        
        if (!$ssl) {
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        } else {
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true); // SSL证书认证
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, $ssl); // 严格认证
        }
        
        if ($params) {
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
        }
        
        if ($cacert_url) {
            curl_setopt($ch, CURLOPT_CAINFO, $cacert_url); // 证书地址
        }   
        
        $responseText = curl_exec($ch);
        curl_close($ch);
        
        return $responseText;
    }
}


