<?php

require __DIR__.'/../config/config.php';

require TEST_PATH.'vendor/autoload.php';

$config = array(
    'notify_url' => 'http://local.payments.com/alipay/notify.php',
    'return_url' => 'http://local.payments.com/alipay/return.php',
    'partner'    => '2088123456789012',
    'input_charset'  => 'utf-8',
    'sign_type'      => 'MD5',
    'md5_key'        => 'abcdefghijklmnopqrstuvwxyz123456',
    'cacert'         => TEST_PATH.'alipay/config/cacert.pem',
);
