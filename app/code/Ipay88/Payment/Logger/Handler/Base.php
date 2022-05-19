<?php

namespace Ipay88\Payment\Logger\Handler;

class Base extends \Magento\Framework\Logger\Handler\Base
{
    public function __construct(
        \Magento\Framework\Filesystem\DriverInterface $filesystem
    )
    {
        $date = date('Y-m-d');

        $fileName = "/var/log/ipay88-payment-{$date}.log";

        parent::__construct($filesystem, null, $fileName);
    }
}