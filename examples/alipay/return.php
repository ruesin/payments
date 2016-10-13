<?php
require 'start.php';

use Ruesin\Payments\Alipay;

$alipay = new Alipay($config);
$res = $alipay->back();

var_dump($res);
