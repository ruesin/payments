<?php

require './config/boot.php';

$payType = pathinfo($_SERVER['PHP_SELF'],PATHINFO_BASENAME);

$payment = new $factory[$payType]($config[$payType]);

$res = $payment->notify();

file_put_contents(TEST_PATH.'temp/sin'.time().'.txt', serialize($res));


