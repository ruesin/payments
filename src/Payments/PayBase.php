<?php
namespace Ruesin\Payments;

abstract class PayBase
{
    abstract function getPayForm($order,$params);
    abstract function notify();
    abstract function back();
}