<?php
/**
 * @author Eguana Team
 * @copyriht Copyright (c) 2020 Eguana {http://eguanacommerce.com}
 * Created by PhpStorm
 * User: abbas
 * Date: 20. 6. 25
 * Time: 오후 9:17
 */

namespace Amore\CustomerRegistration\Logger;

use Monolog\Logger;

/**
 * PLEASE ENTER ONE LINE SHORT DESCRIPTION OF CLASS
 * Class Handler
 * @package Amore\CustomerRegistration\Logger
 */
class Handler extends \Magento\Framework\Logger\Handler\Base
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
    protected $fileName = '/var/log/pos.log';
}