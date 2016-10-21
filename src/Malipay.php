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
    public function getPayForm($order,$params = []) {
        
        $signParam = array(
            "service"       => self::SERVICE,
            "partner"       => $this->config['partner'],
            "seller_id"     => trim($this->config['partner']),
            "payment_type"	=> 1, //支付类型 仅支持：1（商品购买）
            "notify_url"	=> $this->config['notify_url'],
            "return_url"	=> $this->config['return_url'],
            "_input_charset"	=> $this->config['input_charset'],
            "sign_type" => $this->config['sign_type'],
            "sign" => '',
            "out_trade_no"	=> $order['out_trade_no'],
            "subject"	=> $order['name'],
            "total_fee"	=> $order['money'],
            "show_url"	=> $order['show_url'], //
            "app_pay"	=> $this->config['app_pay'] != 'Y' ? '' : 'Y',
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
    
    /**
     * 生成签名结果
     *
     * @author Ruesin
     */
    private function buildRequestMysign($para_sort)
    {
        $prestr = StringUtils::createLinkstring($para_sort);
        $mysign = "";
        switch (strtoupper(trim($this->config['sign_type']))) {
            case "MD5":
                $mysign = md5($prestr . $this->config['md5_key']);
                break;
            default:
                $mysign = "";
        }
    
        return $mysign;
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