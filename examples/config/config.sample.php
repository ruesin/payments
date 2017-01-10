<?php
return array(
    'alipay' => array(
        'notify_url' => 'http://local.payments.com/notify.php/alipay',
        'return_url' => 'http://local.payments.com/return.php/alipay',
        'partner'    => '2088123456789012',
        'input_charset'  => 'utf-8',
        'sign_type'      => 'MD5',
        'md5_key'        => 'abcdefghijklmnopqrstuvwxyz123456',
        'cacert'         => TEST_PATH.'config/cacert.pem',
    ),
    'malipay' => array(
        'notify_url' => 'http://local.payments.com/notify.php/malipay',
        'return_url' => 'http://local.payments.com/return.php/malipay',
        'partner'    => '2088123456789012',
        'input_charset'  => 'utf-8',
        'sign_type'      => 'MD5',
        'md5_key'        => 'abcdefghijklmnopqrstuvwxyz123456',
        'cacert'         => TEST_PATH.'config/cacert.pem',
        'app_pay'    => 'Y', //唤起钱包APP支付宝
    ),
    'wxnative' => array(
        'notify_url' => 'http://local.payments.com/notify.php/wxnative',
        'return_url' => 'http://local.payments.com/return.php/wxnative',
        'appid' => 'wx426b3015555a46be', //绑定支付的APPID，开户邮件中可查看
        'mch_id' => '1900009851', //商户号，开户邮件中可查看
        'key' => '8934e7d15453e97507ef794cf7b0519d', // 商户支付密钥，参考开户邮件设置，登录商户平台自行设置
    ),
    'unionpay' => [
        'notify_url' => 'http://cent.payments.com/notify.php/unionpay',
        'return_url' => 'http://cent.payments.com/return.php/unionpay',
        'merId'      => '777290058110048',//商户号
        'sign_cert_path'   => '/data/html/payments/examples/config/unionpay/700000000000001_acp.pfx',
        'sign_cert_pwd' => '000000'
    ]
);



