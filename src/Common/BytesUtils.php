<?php
namespace Ruesin\Payments\Common;

/**
 * 字节数组操作类
 */
class ByteUtils
{

    /**
     * 转换字符串为字节数组
     */
    public static function strToBytes($string)
    {
        $bytes = array();
        for ($i = 0; $i < strlen($string); $i ++) {
            $bytes[] = ord($string[$i]);
        }
        return $bytes;
    }

    /**
     * 字节数组转为字符串
     */
    public static function bytesToStr($bytes)
    {
        $str = '';
        foreach ($bytes as $ch) {
            $str .= chr($ch);
        }
        
        return $str;
    }

    /**
     * 十进制字符串转为十六进制字符串
     */
    public static function strToHex($string)
    {
        $hex = "";
        for ($i = 0; $i < strlen($string); $i ++) {
            $tmp = dechex(ord($string[$i]));
            if (strlen($tmp) == 1) {
                $hex .= "0";
            }
            $hex .= $tmp;
        }
        $hex = strtolower($hex);
        return $hex;
    }

    /**
     * 转换16进制字符串为字节数组
     */
    public static function hexStrToBytes($hexString)
    {
        $bytes = array();
        for ($i = 0; $i < strlen($hexString) - 1; $i += 2) {
            $bytes[$i / 2] = hexdec($hexString[$i] . $hexString[$i + 1]) & 0xff;
        }
        
        return $bytes;
    }

    /**
     * 字节数组转十六进制
     */
    public static function bytesToHex($bytes)
    {
        $str = self::bytesToStr($bytes);
        return self::strToHex($str);
    }

    /**
     * ASC转为十六进制
     */
    public static function ascToHex($asc, $AscLen)
    {
        $i = 0;
        $Hex = array();
        for ($i = 0; 2 * $i < $AscLen; $i ++) {
            
            /* A:0x41(0100 0001),a:0x61(0110 0001),右移4位后都是0001,加0x90等0xa */
            $Hex[$i] = (chr($asc[2 * $i]) << 4);
            if (! (chr($asc[2 * $i]) >= '0' && chr($asc[2 * $i]) <= '9')) {
                $Hex[$i] += 0x90;
            }
            
            if (2 * $i + 1 >= $AscLen) {
                break;
            }
            
            $Hex[$i] |= (chr($asc[2 * $i + 1]) & 0x0f);
            if (! (chr($asc[2 * $i + 1]) >= '0' && chr($asc[2 * $i + 1]) <= '9')) {
                $Hex[$i] += 0x09;
            }
        }
        return $Hex;
    }

    /**
     * 将一个整数转字节数组
     */
    public static function integerToBytes($val)
    {
        $byt = array();
        $byt[0] = ($val >> 24 & 0xff);
        $byt[1] = ($val >> 16 & 0xff);
        $byt[2] = ($val >> 8 & 0xff);
        $byt[3] = ($val & 0xff);
        return $byt;
    }

    /**
     * 从字节数组中指定的位置读取一个整型数据
     * 
     * @param $bytes 字节数组            
     * @param $position 指定的开始位置            
     * @return 一个Integer类型的数据
     */
    public static function bytesToInteger($bytes, $position)
    {
        $val = 0;
        $val = $bytes[$position + 3] & 0xff;
        $val <<= 8;
        $val |= $bytes[$position + 2] & 0xff;
        $val <<= 8;
        $val |= $bytes[$position + 1] & 0xff;
        $val <<= 8;
        $val |= $bytes[$position] & 0xff;
        return $val;
    }

    /**
     * 转换字节数组为整型数据
     */
    public static function bytesToInteger($bytes)
    {
        $val = 0;
        for ($i = 0; $i < count($bytes); $i ++) {
            // $val += (($bytes [$i] & 0xff) << (8 * (count ( $bytes ) - 1 - $i)));
            $val += $bytes[$i] * pow(256, count($bytes) - 1 - $i);
        }
        return $val;
    }

    /**
     * 转换字节数组为大整型数据
     */
    public static function bytesToBigInteger($bytes)
    {
        $val = 0;
        for ($i = 0; $i < count($bytes); $i ++) {
            $val = bcadd($val, bcmul($bytes[$i], bcpow(256, count($bytes) - 1 - $i)));
        }
        return $val;
    }

    /**
     * 转换字节数组为整型数据
     * 
     * @param $b 字节数组            
     * @param $offset 位游方式            
     */
    public static function byteArrayToInt($b, $offset)
    {
        $value = 0;
        for ($i = 0; $i < 4; $i ++) {
            $shift = (4 - 1 - $i) * 8;
            $value = $value + ($b[$i + $offset] & 0x000000FF) << $shift; // 往高位游
        }
        return $value;
    }

    /**
     * 转换sort字符串为字节数组
     */
    public static function shortToBytes($val)
    {
        $byt = array();
        $byt[0] = ($val & 0xff);
        $byt[1] = ($val >> 8 & 0xff);
        return $byt;
    }

    /**
     * 从字节数组指定位置读取short类型数据
     * 
     * @param $bytes 字节数组            
     * @param $position 指定的开始位置            
     * @return 一个Short类型的数据
     */
    public static function bytesToShort($bytes, $position)
    {
        $val = 0;
        $val = $bytes[$position + 1] & 0xFF;
        $val = $val << 8;
        $val |= $bytes[$position] & 0xFF;
        return $val;
    }

    /**
     * 十六进制转二进制
     */
    public static function hexTobin($hexstr)
    {
        $n = strlen($hexstr);
        $sbin = "";
        $i = 0;
        while ($i < $n) {
            $a = substr($hexstr, $i, 2);
            $c = pack("H*", $a);
            if ($i == 0) {
                $sbin = $c;
            } else {
                $sbin .= $c;
            }
            $i += 2;
        }
        return $sbin;
    }
}


