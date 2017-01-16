<?php
namespace Ruesin\Payments\Lib;

use Ruesin\Payments\Common\StringUtils;
use Ruesin\Payments\Common\Request;
use Ruesin\Payments\Common\SignUtils;

class Alipay extends PayBase
{
    
    use \Ruesin\Payments\Common\SubmitForm;
    
    const SERVICE = "create_direct_pay_by_user";
    
    const GATEWAY = 'https://mapi.alipay.com/gateway.do?';
    
    const SIGN_TYPE = 'MD5';
    
    const CHARSET = 'utf-8';

    const TRANSPORT = 'http';
    
    const HTTPS_VERIFY_URL = 'https://mapi.alipay.com/gateway.do?service=notify_verify&';
    
    const HTTP_VERIFY_URL = 'http://notify.alipay.com/trade/notify_query.do?';
    
    
    protected function setConfig($config = [])
    {
        if (! $config['sign_type'])
            $config['sign_type'] = self::SIGN_TYPE;
        if (! $config['input_charset'])
            $config['input_charset'] = self::CHARSET;
        parent::setConfig($config);
    }
    
    /**
     * 获取支付表单数据
     *
     * https://doc.open.alipay.com/doc2/detail.htm?spm=a219a.7629140.0.0.kiX33I&treeId=62&articleId=103740&docType=1
     *
     * @author Ruesin
     */
    public function buildRequestHtml($order = [], $params = [])
    {
        $signParam = array(
            "service"      => self::SERVICE,
            "partner"      => trim($this->getConfig('partner')),
            "_input_charset" => trim($this->getConfig('input_charset')),
            "notify_url"   => $this->getConfig('notify_url'),
            "return_url"   => $this->getConfig('return_url'),
            "sign_type"    => $this->getConfig('sign_type'),
            "sign"         => '', // 签名
            "out_trade_no" => $order['out_trade_no'],
            "subject"      => $order['name'],
            "total_fee"    => $order['money'],
            "seller_id"    => $this->getConfig('seller_id') ? $this->getConfig('seller_id') : $this->getConfig('partner'),
            "payment_type" => '1',
            "body"         => $order['desc'],
            "exter_invoke_ip"   => isset($params['ip']) ? $params['ip'] : '',
            "anti_phishing_key" => $this->getConfig('anti_phishing_key') ? $this->getConfig('anti_phishing_key') : '',
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
        $para_filter = StringUtils::paraFilter($para_temp, array(
            'sign',
            'sign_type'
        ));
        
        $fields = StringUtils::argSort($para_filter);
        
        $mysign = $this->buildSign(StringUtils::createLinkstring($fields));
        
        $fields['sign'] = $mysign;
        $fields['sign_type'] = strtoupper(trim($this->getConfig('sign_type')));
        
        return $fields;
    }
    
    /**
     * 异步通知
     *
     * @author Ruesin
     */
    public function notify()
    {
        $data = $this->verify($this->requestPostData(),['strict'=>true]);
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
    public function back()
    {
        $data = $this->verify($this->requestGetData(),['strict'=>false]);
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
     * @author Ruesin
     */
    private function verify($data = [], $params = [])
    {
        if (empty($data)) return false;
    
        $para_filter = StringUtils::paraFilter($data, array('sign','sign_type'));
        $str = StringUtils::createLinkstring(StringUtils::argSort($para_filter));
        
        if ($this->verifySign($str,$data['sign']) !== true) return false;
        
        if (isset($params['strict']) && $params['strict'] == true){
            $responseTxt = 'false';
            if (! empty($data["notify_id"])) $responseTxt = $this->getResponse($data["notify_id"]);
            if (! preg_match("/true$/i", $responseTxt)) return false;
        }
    
        if ($data['trade_status'] == 'TRADE_FINISHED' || $data['trade_status'] == 'TRADE_SUCCESS') {} else {}
        
        return $data;
    }
    
    /**
     * 生成签名结果
     *
     * @author Ruesin
     */
    private function buildSign($data = '')
    {
        $mysign = "";
        switch (strtoupper(trim($this->getConfig('sign_type')))) {
            case "MD5":
                $mysign = SignUtils::md5Sign($data . $this->getConfig('md5_key'));
                break;
            case "RSA" :
                $mysign = SignUtils::rsaSign($data, $this->getConfig('rsa_private_path'));
                break;
            default:
                $mysign = "";
        }
        
        return $mysign;
    }
    
    /**
     * 校验签名
     *
     * @author Ruesin
     */
    private function verifySign($data = '', $sign = '')
    {
        switch (strtoupper(trim($this->getConfig('sign_type')))) {
            case "MD5":
                return SignUtils::md5Verify($data . $this->getConfig('md5_key'), $sign);
                break;
            case "RSA" :
                return SignUtils::rsaVerify($data,$this->getConfig('rsa_public_path'),$sign);
                break;
            default:
                return false;
        }
        return false;
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
    function getResponse($notify_id = '')
    {
        $veryfy_url = '';
        if (self::TRANSPORT == 'https') {
            $veryfy_url = self::HTTPS_VERIFY_URL;
        } else {
            $veryfy_url = self::HTTP_VERIFY_URL;
        }
        $veryfy_url = $veryfy_url . "partner=" . trim($this->getConfig('partner')) . "&notify_id=" . urldecode($notify_id);
        
        $responseTxt = Request::curl($veryfy_url,'',2,$this->getConfig('cacert'),3);
        return $responseTxt;
    }
    
}