<?php
namespace Ruesin\Payments\Lib;

use Ruesin\Payments\Common\StringUtils;

class Unionpay extends PayBase
{
    use \Ruesin\Payments\Common\SubmitForm;
    
    // 前台交易请求地址
    const FRONT_TRANS_URL = 'https://101.231.204.80:5000/gateway/api/frontTransReq.do';
    
    private $config = [];
    
    public function __construct($config = [])
    {
        $this->setConfig($config);
    }
    
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
            'frontUrl' => $this->config['return_url'],  //前台通知地址~
            'backUrl' => $this->config['notify_url'],	  //后台通知地址
            'accessType' => '0',//接入类型
            'merId' => $this->config['merId'],//商户代码
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
        $params['certId'] = $this->getSignCert('certId');
        
        if (!$this->buildSign($params)) return false;
        
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
        
        $public_key = $this->getVerifyCert('key');
        
        $signature_str = $data ['signature'];
        unset ( $data ['signature'] );
        $signature = base64_decode ( $signature_str );
        
        $params_str = StringUtils::createLinkstring(StringUtils::argSort($data));
        
        $params_sha1x16 = sha1 ( $params_str, false );
        
        $isSuccess = openssl_verify ( $params_sha1x16, $signature, $public_key, OPENSSL_ALGO_SHA1 );
        
        if (!$isSuccess) return false;
        
        if ($data['respCode'] == '00' || $data['respCode'] == 'A6') {} else {}
        
        return $data;
    }
    
    /**
     * 签名
     *
     * @author Ruesin
     */
    private function buildSign(&$params)
    {
        if(isset($params['signature'])) {
            unset($params['signature']);
        }
        
        $params = StringUtils::argSort($params);
        
        $params_str = StringUtils::createLinkstring($params);
        
        $params_sha1x16 = sha1 ( $params_str, FALSE );//摘要
        
        $private_key = $this->getSignCert('key');
        
        // 签名
        $sign_falg = openssl_sign ( $params_sha1x16, $signature, $private_key, OPENSSL_ALGO_SHA1 );
        
        if (!$sign_falg) return false;
        
        $params ['signature'] = base64_encode ( $signature );
        
        return true;
    }
    
    /**
     * 获取证书信息
     *
     * @author Ruesin
     */
    private function getSignCert($key = '')
    {
        $result = [];
        $pkcs12certdata = file_get_contents($this->config['sign_cert_path']);
        if ($pkcs12certdata === false) {
            return false;
        }
    
        openssl_pkcs12_read($pkcs12certdata, $certs, $this->config['sign_cert_pwd']);
        $x509data = $certs['cert'];
    
        openssl_x509_read($x509data);
        $certdata = openssl_x509_parse($x509data);
        $result['certId'] = $certdata['serialNumber'];
    
        $result['key'] = $certs['pkey'];
        $result['cert'] = $x509data;
    
        return $key ? $result[$key] : $result;
    }
    
    /**
     * 延签证书信息
     *
     * @author Ruesin
     * @date 2017年1月11日
     */
    private function getVerifyCert($key = '')
    {
        $result = [];
        $x509data = file_get_contents($this->config['encrypt_cert_path']);
        if($x509data === false ){
            return false;
        }
        openssl_x509_read($x509data);
        $certdata = openssl_x509_parse($x509data);
        $result['certId'] = $certdata ['serialNumber'];
        $result['key']    = $x509data;
        
        return $key ? $result[$key] : $result;
    }
    
}