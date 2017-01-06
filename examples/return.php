<?php

require './config/boot.php';

$payType = pathinfo($_SERVER['PHP_SELF'],PATHINFO_BASENAME);

$payment = \Ruesin\Payments\PayLoader::init($payType,$config[$payType]);

$res = $payment->back();

var_dump($res);

