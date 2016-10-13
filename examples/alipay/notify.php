<?php

require 'start.php';

use Ruesin\Payments\Alipay;

$alipay = new Alipay($config);
$res = $alipay->notify();

file_put_contents(TEST_PATH.'temp/sin'.time().'.txt', serialize($res));


