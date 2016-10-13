<?php
require 'start.php';

use Ruesin\Payments\Alipay;

echo '<pre>';
print_r($config);
die();

$alipay = new Alipay($config);

$order = array(
    'out_trade_no' => 'N'.time(),
    'name'         => '支付宝测试',
    'money'        => '0.01',
    'desc'         => '支付宝测试描述'
);

$html = $alipay->getPayForm($order);

echo $html;