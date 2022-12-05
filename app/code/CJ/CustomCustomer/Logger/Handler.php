<?php

namespace CJ\CustomCustomer\Logger;

use Monolog\Logger;

/**
 * Class Handler
 */
class Handler extends \Magento\Framework\Logger\Handler\Base
{
    protected $loggerType = Logger::INFO;

    protected $fileName = '/var/log/cj_customergroup.log';
}
