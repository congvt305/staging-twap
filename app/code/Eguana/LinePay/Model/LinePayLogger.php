<?php
/**
 * @author Eguana Team
 * @copyriht Copyright (c) 2020 Eguana {http://eguanacommerce.com}
 * Created by PhpStorm
 * User: raheel
 * Date: 15/1/21
 * Time: 5:18 PM
 */
namespace Eguana\LinePay\Model;

use Eguana\LinePay\Logger\Logger;
use Eguana\LinePay\Helper\Data;
use Magento\Framework\Serialize\Serializer\Json;

/**
 * To add the message & params in the debug log file for API calls
 *
 * Class LinePayLogger
 */
class LinePayLogger
{
    /**
     * @var Json
     */
    private $json;

    /**
     * @var Data
     */
    private $confg;

    /**
     * @var Logger
     */
    private $logger;

    /**
     * @param Data $confg
     * @param Logger $logger
     * @param Json $json
     */
    public function __construct(
        Data $confg,
        Logger $logger,
        Json $json
    ) {
        $this->json = $json;
        $this->confg = $confg;
        $this->logger = $logger;
    }

    /**
     * Add request & response in log file
     *
     * @param $message
     * @param $parameters
     */
    public function addAPICallLog($message, $parameters)
    {
        if ($this->confg->getDebug()) {
            $this->logger->info($message);
            $this->logger->info($this->json->serialize($parameters));
        }
    }
}
