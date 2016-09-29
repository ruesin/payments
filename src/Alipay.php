<?php

namespace Ruesin\Payments;

use Ruesin\Payments\Common\StringUtils;

class Alipay extends PayBase
{

    use SubmitForm;

    //接口名称
    const SERVICE = "create_direct_pay_by_user";
    //支付宝网关地址
    const GATEWAY = 'https://mapi.alipay.com/gateway.do?';
    //签名类型
    const SIGN_TYPE = 'MD5';
    //字符编码格式
    const CHARSET = 'utf-8';
    const TRANSPORT = 'http'; //访问模式,根据自己的服务器是否支持ssl访问，若支持请选择https；若不支持请选择http
    ## HTTPS形式消息验证地址
    const HTTPS_URL = 'https://mapi.alipay.com/gateway.do?service=notify_verify&';
    ## HTTP形式消息验证地址
    const HTTP_URL = 'http://notify.alipay.com/trade/notify_query.do?';

    //配置
    private $config = [];

    /**
     * 获取支付表单数据
     * 
     * https://doc.open.alipay.com/doc2/detail.htm?spm=a219a.7629140.0.0.kiX33I&treeId=62&articleId=103740&docType=1
     *
     * @author Ruesin
     */
    public function getPayForm($order, $params)
    {

        $this->setConfig($params);

        $signParam = array(
            "service" => self::SERVICE,
            "partner" => trim($this->config['alipay_partner']), //合作身份者id，以2088开头的16位纯数字
            "_input_charset" => self::CHARSET,
            "notify_url" => $this->config['notify_url'],
            "return_url" => $this->config['return_url'],
            "sign_type" => $this->config['sign_type'], //签名方式
            "sign" => '', //签名
            "out_trade_no" => $order['out_trade_no'],
            "subject" => $order['name'],
            "total_fee" => $order['money'],
            "seller_id" => trim($this->config['alipay_account']),
            "payment_type" => '1',
            "body" => $order['body'],
                //"exter_invoke_ip"=>$alipay_config['exter_invoke_ip'],
                //"anti_phishing_key"=>$alipay_config['anti_phishing_key'],
        );

        
        $fields = $this->buildRequestFields($signParam);

        $this->buildRequestForm($fields, $params);

        return [
            'form_url' => self::GATEWAY,
            'method' => 'get',
            'fields' => $this->buildRequestPara($signParam),
        ];
    }

    /**
     * 生成请求数组
     *
     * @author Ruesin
     */
    public static function buildRequestFields($para_temp)
    {

        $para_filter = StringUtils::paraFilter($para_temp, array('sign', 'sign_type'));

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
            case "MD5" :
                $mysign = md5($prestr . $this->config['key']);
                break;
            default :
                $mysign = "";
        }

        return $mysign;
    }

    private function setConfig($params)
    {
        if (!$params['sign_type'])
            $params['sign_type'] = self::SIGN_TYPE;
        $this->config = $params;
    }

    function notify()
    {
        ;
    }

    function back()
    {
        ;
    }

}
