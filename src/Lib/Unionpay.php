<?php
namespace Ruesin\Payments\Lib;

use Ruesin\Payments\Common\StringUtils;
use Ruesin\Payments\Common\SignUtils;

class Unionpay extends PayBase
{
    use \Ruesin\Payments\Common\SubmitForm;
    
    private static $private = [];
    private static $public = [];
    
    // 前台交易请求地址
    const FRONT_TRANS_URL = 'https://101.231.204.80:5000/gateway/api/frontTransReq.do';
    
    public function buildRequestHtml($order = [], $params = [])
    {
        $signParams = [
            'version' => '5.0.0',//版本号
            'encoding' => 'UTF-8',//编码方式
            'certId'  => '', //证书ID
            'signature' => '', //签名
            'signMethod' => '01',//签名方法
            'txnType' => '01',//交易类型
            'txnSubType' => '01',//交易子类
            'bizType' => '000201',//产品业务类型
            'channelType' => '07',//渠道类型，07-PC，08-手机
            'frontUrl' => $this->getConfig('return_url'),  //前台通知地址~
            'backUrl' => $this->getConfig('notify_url'),	  //后台通知地址
            'accessType' => '0',//接入类型
            'merId' => $this->getConfig('merId'),//商户代码
            'orderId' => $order["out_trade_no"],//商户订单号
            'txnTime' => date('YmdHis'),//订单发送时间
            'txnAmt' => intval($order['money'] * 100),//交易金额，单位分
            'currencyCode' => '156',//交易币种
            //'reqReserved' =>'透传信息',
        ];
        
        $fields = $this->buildRequestFields($signParams);
        
        $formParam = array(
            'action' => self::FRONT_TRANS_URL,
            'method' => 'post',
            'text' => 'Connect gateway...'
        );
        return $this->buildRequestForm($fields, $formParam);
    }
    
    /**
     * 生成请求字段
     *
     * @author Ruesin
     */
    private function buildRequestFields($params = [])
    {
        $params['certId'] = $this->getPrivate('certId');
        
        $para_filter = StringUtils::paraFilter($params, ['signature']);
        $params = StringUtils::argSort($para_filter);
        
        $params['signature'] = $this->buildSign(StringUtils::createLinkstring($params));
        
        return $params;
    }
    
    /**
     * 异步响应
     *
     * @author Ruesin
     */
    public function notify()
    {
        $data = $this->verify($this->requestPostData());
        if (! $data) {
            return false;
        }
        
        return [
            'out_trade_no' => $data['orderId'],
            'data' => $data
        ];
    }

    /**
     * 同步返回
     *
     * @author Ruesin
     */
    public function back()
    {
        $data = $this->verify($this->requestPostData());
        if (! $data) {
            return false;
        }
        
        return [
            'out_trade_no' => $data['orderId'],
            'data' => $data
        ];
    }
    
    /**
     * 校验通知请求
     *
     * @author Ruesin
     */
    private function verify($data = [])
    {
        if (empty($data)) return false;
        
        if (!isset($data['signature'])) return false;
        
        $params = StringUtils::argSort(StringUtils::paraFilter($data, ['signature']));
        
        $data = $this->verifySign(StringUtils::createLinkstring($params), base64_decode($data ['signature']));
        
        if (!$data) return false;
        
        if ($data['respCode'] == '00' || $data['respCode'] == 'A6') {} else {}
        
        return $data;
    }
    
    /**
     * 签名
     *
     * @author Ruesin
     */
    private function buildSign($data = '')
    {
        $sign_falg = openssl_sign (sha1($data, false), $signature, $this->getPrivate('key'), OPENSSL_ALGO_SHA1 );
        
        return $sign_falg ? base64_encode ( $signature ) : '';
    }
    
    /**
     * 验签
     *
     * @author Ruesin
     */
    private function verifySign($data = '', $sign = '')
    {
        $isSuccess = openssl_verify ( sha1($data, false), $sign, $this->getPublic('key'), OPENSSL_ALGO_SHA1 );
        
        return $isSuccess ? true : false;
    }
    
    /**
     * 获取证书信息
     *
     * @author Ruesin
     */
    private function getPrivate($key = '')
    {
        if (!self::$private) {
            self::$private = SignUtils::getCertFromPfx($this->getConfig('sign_cert_path'),$this->getConfig('sign_cert_pwd'));
        }
        
        if (!self::$private) return '';
        
        return $key ? self::$private[$key] : self::$private;
    }
    
    /**
     * 验签证书信息
     *
     * @author Ruesin
     */
    private function getPublic($key = '')
    {
        if (!self::$public) {
            self::$public = SignUtils::getCertFromCer($this->getConfig('encrypt_cert_path'));
        }
        if (!self::$public) return [];
        
        return $key ? self::$public[$key] : self::$public;
    }
    
}