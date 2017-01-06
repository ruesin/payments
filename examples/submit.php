<?php

require './config/boot.php';

$payType = $_POST['payment'];

$payment = \Ruesin\Payments\PayLoader::init($payType,$config[$payType]);

$html = $payment->getPayForm($_POST['order']);

echo $html;
