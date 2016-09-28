<?php
namespace Ruesin\Payments;

abstract class PayBase
{
    abstract function getPayForm();
    abstract function notify();
    abstract function return();
    abstract function notifyUrl();
    abstract function returnUrl();
}