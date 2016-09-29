<?php
namespace Ruesin\Payments;

trait SubmitForm {
    
    
    /**
     * 构造Form表单
     *
     * @author Ruesin
     */
    function buildRequestForm($fields, $params) {
        
        $sHtml = "<form id='paymentSubmitForm' name='paymentSubmitForm' action='".$params['action']."' method='".$params['method'] ? $params['method'] : 'get'."'>";
        
        while (list ($key, $val) = each ($fields)) {
            $sHtml.= "<input type='hidden' name='".$key."' value='".$val."'/>";
        }
        
        if ($params['button']){
            $sHtml .= "<input type='submit'  value='".$params['button']."' style='display:none;'></form>";
        }
        
        if ($params['text']) {
            $sHtml .= '<b>'.$params['text'].'</b>';
        }
    
        $sHtml = $sHtml."<script>document.forms['paymentSubmitForm'].submit();</script>";
    
        return $sHtml;
    }
    
}