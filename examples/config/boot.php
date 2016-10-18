<?php

define('TEST_PATH',substr(__DIR__, 0,strlen(__DIR__)-6));

define('RUESIN_PATH',substr(__DIR__, 0,strlen(__DIR__)-15));

require TEST_PATH.'vendor/autoload.php';

$config = require_once 'config.php';

$factory = require_once 'factory.php';