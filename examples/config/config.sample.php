<?php
return array(
    'alipay' => array(
        'notify_url' => 'http://local.payments.com/notify.php/alipay',
        'return_url' => 'http://local.payments.com/return.php/alipay',
        'partner'    => '2088123456789012',
        'input_charset'  => 'utf-8',
        'sign_type'      => 'MD5',
        'md5_key'        => 'abcdefghijklmnopqrstuvwxyz123456',
        'cacert'         => TEST_PATH.'alipay/config/cacert.pem',
    ),
    'alipay' => array(
        'notify_url' => 'http://local.payments.com/notify.php/malipay',
        'return_url' => 'http://local.payments.com/return.php/malipay',
        'partner'    => '2088123456789012',
        'input_charset'  => 'utf-8',
        'sign_type'      => 'MD5',
        'md5_key'        => 'abcdefghijklmnopqrstuvwxyz123456',
        'cacert'         => TEST_PATH.'alipay/config/cacert.pem',
    )
);



