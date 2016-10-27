<?php
namespace Ruesin\Payments;

use Ruesin\Payments\Common\StringUtils;

class WxNative extends PayBase
{

    const UNIFIED_ORDER_URL = 'https://api.mch.weixin.qq.com/pay/unifiedorder';

    private $config = [];

    public function __construct($params = [])
    {
        $this->setConfig($params);
    }

    /*
     *
     * @see https://pay.weixin.qq.com/wiki/doc/api/native.php?chapter=9_1
     */
    public function getPayForm($order, $params = [])
    {
        $form = array(
            'appid' => $this->config['appid'],
            'mch_id' => $this->config['mch_id'],
            
            // 'device_info' => 'WEB',//终端设备号(门店号或收银设备ID)，注意：PC网页或公众号内支付请传"WEB"
            'nonce_str' => StringUtils::createNonceString(), // 随机字符串，不长于32位。
            'sign' => '', // 签名
            'body' => $order['subject'],
            // 'detail' => '',// json 格式商品详情
            // 'attach' => '微信扫码支付订单。', //附加数据，在查询API和支付通知中原样返回，该字段主要用于商户携带订单的自定义数据
            'out_trade_no' => $order['out_trade_no'], // 商户系统内部的订单号,32个字符内、可包含字母
                                                      
            // 'fee_type' => 'CNY', //货币类型
            'total_fee' => ceil($order['money'] * 100), // 订单总金额，单位为分
            'spbill_create_ip' => $_SERVER['REMOTE_ADDR'], // APP和网页支付提交用户端ip，Native支付填调用微信支付API的机器IP。
            'time_start' => date('YmdHis'), // 订单生成时间，格式为yyyyMMddHHmmss
            'time_expire' => date('YmdHis', time() + 7200), // 订单失效时间，格式为yyyyMMddHHmmss 最短失效时间间隔必须大于5分钟
                                                         // 'goods_tag' => 'test', //商品标记，代金券或立减优惠功能的参数
            'notify_url' => $this->config['notify_url'], // 接收微信支付异步通知回调地址，通知url必须为直接可访问的url，不能携带参数。
            'trade_type' => 'NATIVE', // 交易类型
            'product_id' => $order['order_id'],
            // trade_type=NATIVE，此参数必传。此id为二维码中包含的商品ID，商户自行定义
            // 'limit_pay' => 'no_credit', //no_credit--指定不能使用信用卡支付
        );
        $result = $this->unifiedOrder($form);
        if (! $result)
            return '';
        
        if ($params['type'] == 'src') {
            return 'http://paysdk.weixin.qq.com/example/qrcode.php?data=' . urlencode($result["code_url"]);
        }
        return '<img src="http://paysdk.weixin.qq.com/example/qrcode.php?data=' . urlencode($result["code_url"]) . '"/>';
    }

    protected function unifiedOrder($para_temp, $timeOut = 6)
    {
        $para_filter = StringUtils::paraFilter($para_temp, array('sign'));
        
        $para_sort = StringUtils::argSort($para_filter);
        
        $para_sort['sign'] = $this->buildRequestMysign($para_sort);
        
        $xml = StringUtils::arrayToXml($para_sort);
        
        // $startTimeStamp = self::getMillisecond();//请求开始时间
        $response = $this->postXmlCurl($xml, self::UNIFIED_ORDER_URL);
        $result = StringUtils::XmlToArray($response);
        
        if ($result['return_code'] != 'SUCCESS') {
            return false;
        }
        
        // 验签
        // ..
        
        // self::reportCostTime($url, $startTimeStamp, $result);//上报请求花费时间
        return $result;
    }

    /**
     * 签名
     *
     * @author Ruesin
     */
    public function buildRequestMysign($para_sort)
    {
        $string = StringUtils::createLinkstring($para_sort);
        $result = strtoupper(md5($string . "&key=" . $this->config['key']));
        return $result;
    }

    /**
     * 以post方式提交xml到对应的接口url
     *
     * @param string $xml 需要post的xml数据
     * @param string $url url
     * @param bool $useCert 是否需要证书，默认不需要
     * @param int $second url执行超时时间，默认30s
     */
    private function postXmlCurl($xml, $url, $useCert = false, $second = 30)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_TIMEOUT, $second);
        curl_setopt($ch, CURLOPT_URL, $url);
        // curl_setopt($ch,CURLOPT_SSL_VERIFYPEER,TRUE);
        // curl_setopt($ch,CURLOPT_SSL_VERIFYHOST,2);//严格校验
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
        curl_setopt($ch, CURLOPT_HEADER, FALSE);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        
        /*
         * if($useCert == true){
         * curl_setopt($ch,CURLOPT_SSLCERTTYPE,'PEM');
         * curl_setopt($ch,CURLOPT_SSLCERT, WxPayConfig::SSLCERT_PATH);
         * curl_setopt($ch,CURLOPT_SSLKEYTYPE,'PEM');
         * curl_setopt($ch,CURLOPT_SSLKEY, WxPayConfig::SSLKEY_PATH);
         * }
         */
        curl_setopt($ch, CURLOPT_POST, TRUE);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $xml);
        $data = curl_exec($ch);
        if ($data) {
            curl_close($ch);
            return $data;
        } else {
            $error = curl_errno($ch);
            curl_close($ch);
            return false;
        }
    }
    
    // **********************************
    private function setConfig($params)
    {
        $this->config = $params;
    }

    public function back()
    {}

    public function notify()
    {}
}