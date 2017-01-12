<?php

require './config/boot.php';

$payType = $_POST['payment'];

$payment = \Ruesin\Payments\PayLoader::init($payType,$config[$payType]);

$html = $payment->submit($_POST['order']);

echo $html;
