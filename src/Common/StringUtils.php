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
}
