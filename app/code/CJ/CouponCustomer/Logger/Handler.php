<?php

namespace CJ\CouponCustomer\Logger;

use MonoLog\Logger;

class Handler extends \Magento\Framework\Logger\Handler\Base
{
    protected $loggerType = Logger::INFO;

    protected $fileName = '/var/log/coupon-wallet.log';
}

