<?php
namespace Ruesin\Payments;

use Ruesin\Payments\Common\StringUtils;


/**
 * 移动支付宝支付
 *
 * @author Ruesin
 */
class Malipay extends PayBase
{
    
    use Common\SubmitForm;
    
    const SERVICE = 'alipay.wap.create.direct.pay.by.user';
    
    const GATEWAY = 'https://mapi.alipay.com/gateway.do?';
    
    const SIGN_TYPE = 'MD5';
    
    const CHARSET = 'utf-8';
    
    const TRANSPORT = 'http';
    
    //const HTTPS_VERIFY_URL = 'https://mapi.alipay.com/gateway.do?service=notify_verify&';
    //const HTTP_VERIFY_URL = 'http://notify.alipay.com/trade/notify_query.do?';
    
    public function __construct($params = []){
        $this->setConfig($params);
    }
    
    /**
     * 
     * @see https://doc.open.alipay.com/doc2/detail.htm?spm=a219a.7629140.0.0.2Z6TSk&treeId=60&articleId=103693&docType=1
     *
     * @author Ruesin
     */
    public function getPayForm($order,$params) {
        $parameter = array(
            "service"       => self::SERVICE,
            "partner"       => $this->config['partner'],
            "seller_id"     => trim($this->config['partner']),
            "payment_type"	=> $this->config['payment_type'],
            "notify_url"	=> $this->config['notify_url'],
            "return_url"	=> $this->config['return_url'],
            "_input_charset"	=> $this->config['input_charset'],
            "sign_type" => $this->config['sign_type'],
            "sign" => '',
            "out_trade_no"	=> $order['out_trade_no'],
            "subject"	=> $order['name'],
            "total_fee"	=> $order['money'],
            "show_url"	=> $order['show_url'], //
            //"app_pay"	=> "Y",//启用此参数能唤起钱包APP支付宝
            "body" => $order['desc'],
        );
        
        $fields = $this->buildRequestFields($signParam);
        
        $formParam = array(
            'action' => self::GATEWAY,
            'method' => 'get',
            'text' => 'Connect gateway...'
        );
        return $this->buildRequestForm($fields, $formParam);
    }
    
    /**
     * 生成请求数组
     *
     * @author Ruesin
     */
    public function buildRequestFields($para_temp)
    {
        $para_filter = StringUtils::paraFilter($para_temp, array('sign','sign_type'));
    
        $para_sort = StringUtils::argSort($para_filter);
    
        $mysign = $this->buildRequestMysign($para_sort);
    
        $para_sort['sign'] = $mysign;
        $para_sort['sign_type'] = strtoupper(trim($this->config['sign_type']));
    
        return $para_sort;
    }
    
    public function notify(){
        
    }
    public function back(){
        
    }
    
    private function setConfig($params)
    {
        if (! $params['sign_type'])
            $params['sign_type'] = self::SIGN_TYPE;
        if (! $params['input_charset'])
            $params['input_charset'] = self::CHARSET;
        $this->config = $params;
    }
}