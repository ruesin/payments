<?php

require './config/boot.php';

$payType = $_POST['payment'];

$payment = new $factory[$payType]($config[$payType]);

$html = $payment->getPayForm($_POST['order']);

echo $html;
