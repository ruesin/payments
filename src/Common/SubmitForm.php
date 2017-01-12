<?php
namespace Ruesin\Payments\Common;

trait SubmitForm {

    /**
     * 构造Form表单
     *
     * @author Ruesin
     */
    function buildRequestForm($fields, $params)
    {
        $method = $params['method'] ? $params['method'] : 'get';
        
        $sHtml = "<form id='paymentSubmitForm' name='paymentSubmitForm' action='" . $params['action'] . "' method='" . $method . "'>";
        
        while (list ($key, $val) = each($fields)) {
            $sHtml .= "<input type='hidden' name='" . $key . "' value='" . $val . "'/>" . PHP_EOL;
        }
        
        if (isset($params['button'])) {
            $sHtml .= "<input type='submit'  value='" . $params['button'] . "' style='display:none;'></form>";
        }
        
        if (isset($params['text'])) {
            $sHtml .= '<b>' . $params['text'] . '</b>';
        }
        
        $sHtml = $sHtml . "<script>document.forms['paymentSubmitForm'].submit();</script>";
        
        return $sHtml;
    }

    /**
     * 获取POST数据
     *
     * @author Ruesin
     */
    public function requestPostData()
    {
        return isset($_POST) ? $_POST : [];
    }
    
    /**
     * 获取GET数据
     *
     * @author Ruesin
     */
    public function requestGetData()
    {
        return isset($_GET) ? $_GET : [];
    }
}