<?php
namespace Ruesin\Payments\Lib;

use Ruesin\Payments\Common\StringUtils;

class Alipay extends PayBase
{
    
    use \Ruesin\Payments\Common\SubmitForm;
    
    // 接口名称
    const SERVICE = "create_direct_pay_by_user";
    // 支付宝网关地址
    const GATEWAY = 'https://mapi.alipay.com/gateway.do?';
    // 签名类型 DSA、RSA、MD5
    const SIGN_TYPE = 'MD5';
    // 字符编码格式
    const CHARSET = 'utf-8';

    const TRANSPORT = 'http';
    // 访问模式,根据自己的服务器是否支持ssl访问，若支持请选择https；若不支持请选择http
    // # HTTPS形式消息验证地址
    const HTTPS_VERIFY_URL = 'https://mapi.alipay.com/gateway.do?service=notify_verify&';
    // # HTTP形式消息验证地址
    const HTTP_VERIFY_URL = 'http://notify.alipay.com/trade/notify_query.do?';
    
    // 配置
    private $config = [];
    
    public function __construct($params = []){
        $this->setConfig($params);
    }

    /**
     * 获取支付表单数据
     *
     * https://doc.open.alipay.com/doc2/detail.htm?spm=a219a.7629140.0.0.kiX33I&treeId=62&articleId=103740&docType=1
     *
     * @author Ruesin
     */
    public function getPayForm($order,$params = [])
    {
        $signParam = array(
            "service" => self::SERVICE,
            "partner" => trim($this->config['partner']),
            "_input_charset" => trim($this->config['input_charset']),
            "notify_url" => $this->config['notify_url'],
            "return_url" => $this->config['return_url'],
            "sign_type" => $this->config['sign_type'],
            "sign" => '', // 签名
            "out_trade_no" => $order['out_trade_no'],
            "subject"   => $order['name'],
            "total_fee" => $order['money'],
            "seller_id" => trim($this->config['partner']),
            "payment_type" => '1',
            "body" => $order['desc'],
            // "exter_invoke_ip"=>$alipay_config['exter_invoke_ip'],
            // "anti_phishing_key"=>$alipay_config['anti_phishing_key'],
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

    private function setConfig($params)
    {
        if (! $params['sign_type'])
            $params['sign_type'] = self::SIGN_TYPE;
        if (! $params['input_charset'])
            $params['input_charset'] = self::CHARSET;
        $this->config = $params;
    }
    
    /**
     * 异步通知 严格验证
     *
     * @author Ruesin
     */
    function notify()
    {
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
     * 同步通知 非严格验证
     *
     * @author Ruesin
     */
    function back()
    {
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
        //数据
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
        $veryfy_url = $veryfy_url . "partner=" . trim($this->config['partner']) . "&notify_id=" . urldecode($notify_id);
        
        $responseTxt = $this->getHttpResponseGET($veryfy_url, $this->config['cacert']);
        return $responseTxt;
    }
    
    /**
     * 远程获取数据，GET模式
     * 注意：
     * 1.使用Crul需要修改服务器中php.ini文件的设置，找到php_curl.dll去掉前面的";"就行了
     * 2.文件夹中cacert.pem是SSL证书请保证其路径有效，目前默认路径是：getcwd().'\\cacert.pem'
     * @param $url 指定URL完整路径地址
     * @param $cacert_url 指定当前工作目录绝对路径
     * return 远程输出的数据
     */
    function getHttpResponseGET($url, $cacert_url)
    {
        $curl = curl_init($url);
        curl_setopt($curl, CURLOPT_HEADER, 0); // 过滤HTTP头
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1); // 显示输出结果
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, true); // SSL证书认证
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 2); // 严格认证
        curl_setopt($curl, CURLOPT_CAINFO, $cacert_url); // 证书地址
        $responseText = curl_exec($curl);
        
        curl_close($curl);
        
        return $responseText;
    }
}