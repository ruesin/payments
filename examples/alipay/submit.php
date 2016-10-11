<?php

require __DIR__.'/../../autoload.php';

use Ruesin\Payments\Alipay;

$params = array(
    'notify_url' => 'http://localhost/alipay/notify.html',
    'return_url' => 'http://localhost/alipay/return.html',
    'alipay_partner' => '2088111111111111',
    'input_charset'  => 'utf-8',
    'sign_type'      => 'MD5',
    'md5_key'        => 'abcdefghijklmnopqrst'
);

$alipay = new Alipay($params);

$order = array(
    'out_trade_no' => 'N123456789',
    'name'         => '2016秋季新款格子衫衬衣',
    'money'        => '59.80',
    'desc'         => '新款潮流格子衫，引领时尚！'
);


$html = $alipay->getPayForm($order);

echo '<pre>';
var_dump($html);
die();