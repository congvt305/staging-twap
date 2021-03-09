<?php
/**
 * @author Eguana Team
 * @copyriht Copyright (c) 2020 Eguana {http://eguanacommerce.com}
 * Created by PhpStorm
 * User: raheel
 * Date: 15/1/21
 * Time: 5:11 PM
 */
namespace Eguana\LinePay\Logger;

use Monolog\Logger;
use Magento\Framework\Logger\Handler\Base;

/**
 * Logger class Log handler
 *
 * Class Handler
 */
class Handler extends Base
{
    /**
     * Logging level
     * @var int
     */
    protected $loggerType = Logger::INFO;

    /**
     * File name
     * @var string
     */
    protected $fileName = '/var/log/linepay.log';
}
