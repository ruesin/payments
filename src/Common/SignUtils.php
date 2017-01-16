<?php
namespace Ruesin\Payments\Common;

class SignUtils
{
    
    /**
     * MD5签名
     *
     * @author Ruesin
     */
    public static function md5Sign($data = '')
    {
        return md5($data);
    }
    
    /**
     * MD5验签
     *
     * @author Ruesin
     */
    public static function md5Verify($data = '',$signature = '')
    {
        return (bool)md5($data) == $signature;
    }

    /**
     * RSA签名
     *
     * @author Ruesin
     */
    public static function rsaSign($data = '', $private_path = '')
    {
        $cert = file_get_contents($private_path);
        if ($cert === false) {
            return false;
        }
        $private_key = openssl_get_privatekey($cert);
        if (! $private_key) {
            return false;
        }
        openssl_sign($data, $signature, $private_key);
        openssl_free_key($private_key);
        return base64_encode($signature);
    }

    /**
     * RSA验签
     *
     * @author Ruesin
     */
    public static function rsaVerify($data = '', $public_path = '', $signature = '')
    {
        $cert = file_get_contents($public_path);
        if ($cert === false) {
            return false;
        }
        $public_key = openssl_get_publickey($cert);
        if ($public_key) {
            $result = (bool) openssl_verify($data, base64_decode($signature), $public_key);
        } else {
            return false;
        }
        openssl_free_key($public_key);
        return $result;
    }

    /**
     * pfx转证书
     *
     * @author Ruesin
     */
    public static function getCertFromPfx($path = '', $password = '', $key = '')
    {
        $result = [];
        
        $pkcs12certdata = file_get_contents($path);
        if ($pkcs12certdata === false) {
            return false;
        }
        
        openssl_pkcs12_read($pkcs12certdata, $certs, $password);
        
        openssl_x509_read($certs['cert']);
        $certdata = openssl_x509_parse($certs['cert']);
        
        $result = [
            'certId' => $certdata['serialNumber'],
            'key' => $certs['pkey'],
            'cert' => $certs['cert']
        ];
        
        return $key ? $result[$key] : $result;
    }

    /**
     * 获取cert
     *
     * @author Ruesin
     */
    public static function getCertFromCer($path = '', $key = '')
    {
        $result = [];
        $x509data = file_get_contents($path);
        if ($x509data === false) {
            return false;
        }
        openssl_x509_read($x509data);
        $certdata = openssl_x509_parse($x509data);
        
        $result = [
            'certId' => $certdata['serialNumber'],
            'key' => $x509data
        ];
        
        return $key ? $result[$key] : $result;
    }
}