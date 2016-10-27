<?php
namespace Ruesin\Payments\Common;

class StringUtils
{

    /**
     * 把数组所有元素，按照“参数=参数值”的模式用“&”字符拼接成字符串
     */
    public static function createLinkstring($para, $isUrlencode = false)
    {
        $arg = "";
        while (list ($key, $val) = each($para)) {
            $arg .= $key . "=" . $val . "&";
            if ($isUrlencode)
                $arg .= $key . "=" . urlencode($val) . "&";
        }
        $arg = substr($arg, 0, count($arg) - 2);
        
        if (get_magic_quotes_gpc())
            $arg = stripslashes($arg);
        
        return $arg;
    }

    /**
     * 把数组所有元素，按照“参数=参数值”的模式用“&”字符拼接成字符串，并对字符串做urlencode编码
     */
    function createLinkstringUrlencode($para)
    {
        $arg = "";
        while (list ($key, $val) = each($para)) {
            $arg .= $key . "=" . urlencode($val) . "&";
        }
        // 去掉最后一个&字符
        $arg = substr($arg, 0, count($arg) - 2);
        
        // 如果存在转义字符，那么去掉转义
        if (get_magic_quotes_gpc()) {
            $arg = stripslashes($arg);
        }
        
        return $arg;
    }

    /**
     * 除去数组中的空值和指定键名的值
     *
     * @param $arr 指定剔除键名的数组            
     */
    public static function paraFilter($para, $arr = array())
    {
        $para_filter = array();
        while (list ($key, $val) = each($para)) {
            if (in_array($key, $arr) || $val == "")
                continue;
            else
                $para_filter[$key] = $para[$key];
        }
        return $para_filter;
    }

    /**
     * 对数组排序
     */
    public static function argSort($para)
    {
        ksort($para);
        reset($para);
        return $para;
    }
    
    
    /**
     * 数组转xml
     **/
    public static function arrayToXml($arr)
    {
        if (! is_array($arr) || count($arr) <= 0) {
            return '<xml></xml>';
        }
        
        $xml = "<xml>";
        foreach ($arr as $key => $val) {
            if (is_numeric($val)) {
                $xml .= "<" . $key . ">" . $val . "</" . $key . ">";
            } else {
                $xml .= "<" . $key . "><![CDATA[" . $val . "]]></" . $key . ">";
            }
        }
        $xml .= "</xml>";
        return $xml;
    }
    
    /**
     * xml转数组
     */
    public static function XmlToArray($xml)
    {
        if(!$xml) return array();
    
        libxml_disable_entity_loader(true);
        $array = json_decode(json_encode(simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA)), true);
        return $array;
    }

    /**
     * 生成指定长度的随机字符串
     */
    public static function createNonceString($length = 32)
    {
        $chars = "abcdefghijklmnopqrstuvwxyz0123456789";
        $str = "";
        for ($i = 0; $i < $length; $i ++) {
            $str .= substr($chars, mt_rand(0, strlen($chars) - 1), 1);
        }
        return $str;
    }
    
}
