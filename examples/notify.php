<?php

require './config/boot.php';

$payType = pathinfo($_SERVER['PHP_SELF'],PATHINFO_BASENAME);

$payment = \Ruesin\Payments\PayLoader::init($payType,$config[$payType]);

$res = $payment->notify();

file_put_contents(TEST_PATH.'temp/sin'.time().'.txt', serialize($res));

