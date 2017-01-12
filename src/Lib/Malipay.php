<?php
namespace Ruesin\Payments\Lib;

use Ruesin\Payments\Common\StringUtils;
use Ruesin\Payments\Common\Request;


/**
 * 移动支付宝支付
 *
 * @author Ruesin
 */
class Malipay extends PayBase
{
    
    use \Ruesin\Payments\Common\SubmitForm;
    
    const SERVICE = 'alipay.wap.create.direct.pay.by.user';
    
    const GATEWAY = 'https://mapi.alipay.com/gateway.do?';
    
    const SIGN_TYPE = 'MD5';
    
    const CHARSET = 'utf-8';
    
    const TRANSPORT = 'http';
    const HTTPS_VERIFY_URL = 'https://mapi.alipay.com/gateway.do?service=notify_verify&';
    const HTTP_VERIFY_URL = 'http://notify.alipay.com/trade/notify_query.do?';
    
    /**
     * 
     * @see https://doc.open.alipay.com/docs/doc.htm?spm=a219a.7629140.0.0.yf7c4z&treeId=60&articleId=104790&docType=1
     *
     * @author Ruesin
     */
    public function buildRequestHtml($order = [], $params = []) {
        
        $signParam = array(
            "service"       => self::SERVICE,
            "partner"       => $this->getConfig('partner'),
            "seller_id"     => trim($this->getConfig('partner')),
            "payment_type"	=> 1, //支付类型 仅支持：1（商品购买）
            "notify_url"	=> $this->getConfig('notify_url'),
            "return_url"	=> $this->getConfig('return_url'),
            "_input_charset"	=> $this->getConfig('input_charset'),
            "sign_type" => $this->getConfig('sign_type'),
            "sign" => '',
            "out_trade_no"	=> $order['out_trade_no'],
            "subject"	=> $order['name'],
            "total_fee"	=> $order['money'],
            "show_url"	=> $order['show_url'],
            "app_pay"	=> $this->getConfig('app_pay') != 'Y' ? '' : 'Y',
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
        $para_sort['sign_type'] = strtoupper(trim($this->getConfig('sign_type')));
    
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
        switch (strtoupper(trim($this->getConfig('sign_type')))) {
            case "MD5":
                $mysign = md5($prestr . $this->getConfig('md5_key'));
                break;
            default:
                $mysign = "";
        }
    
        return $mysign;
    }
    
    /**
     * 异步通知
     *
     * @author Ruesin
     */
    public function notify(){
        $data = $this->verify();
        if (! $data) {
            echo 'fail';
            return false;
        }
        
        echo 'success';
        return array(
            'out_trade_no' => $data['out_trade_no'],
            'data' => $data
        );
    }
    
    /**
     * 同步通知
     *
     * @author Ruesin
     */
    public function back(){
        
        $data = $this->verify(false);
        if (! $data) {
            return false;
        }
        
        return array(
            'out_trade_no' => $data['out_trade_no'],
            'data' => $data
        );
    }
    
    /**
     * 校验通知请求
     *
     * @param bool $strict 是否严格验证
     *
     * @author Ruesin
     */
    private function verify($strict = true)
    {
        $data = isset($_POST) && !empty($_POST) ? $_POST : $_GET;
    
        if (empty($data)) return false;
    
        // 验签
        $para_filter = StringUtils::paraFilter($data, array('sign','sign_type'));
        $para_sort = StringUtils::argSort($para_filter);
        $mysign = $this->buildRequestMysign($para_sort);
    
        if ($mysign != $data['sign']) return false;
    
        if ($strict){
            $responseTxt = 'false';
            if (! empty($data["notify_id"])) $responseTxt = $this->getResponse($data["notify_id"]);
            if (! preg_match("/true$/i", $responseTxt)) return false;
        }
    
        if ($data['trade_status'] == 'TRADE_FINISHED' || $data['trade_status'] == 'TRADE_SUCCESS') {} else {}
    
        return $data;
    }
    
    /**
     * 获取远程服务器ATN结果,验证返回URL
     *
     * @param $notify_id 通知校验ID
     * @return 服务器ATN结果 验证结果集：
     * invalid命令参数不对 出现这个错误，请检测返回处理中partner和key是否为空
     * true 返回正确信息
     * false 请检查防火墙或者是服务器阻止端口问题以及验证时间是否超过一分钟
     */
    function getResponse($notify_id)
    {
        $veryfy_url = '';
        if (self::TRANSPORT == 'https') {
            $veryfy_url = self::HTTPS_VERIFY_URL;
        } else {
            $veryfy_url = self::HTTP_VERIFY_URL;
        }
        $veryfy_url = $veryfy_url . "partner=" . trim($this->getConfig('partner')) . "&notify_id=" . urldecode($notify_id);
        $responseTxt = Request::curl($veryfy_url,[],2,$this->getConfig('cacert'));
        return $responseTxt;
    }
    
    protected function setConfig($config = [])
    {
        if (! $config['sign_type'])
            $config['sign_type'] = self::SIGN_TYPE;
        if (! $config['input_charset'])
            $config['input_charset'] = self::CHARSET;
        parent::setConfig($config);
    }
}