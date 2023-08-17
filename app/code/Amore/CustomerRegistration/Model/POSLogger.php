<?php
/**
 * @author Eguana Team
 * @copyriht Copyright (c) 2020 Eguana {http://eguanacommerce.com}
 * Created by PhpStorm
 * User: abbas
 * Date: 20. 6. 23
 * Time: 오후 2:47
 */

namespace Amore\CustomerRegistration\Model;

use Amore\CustomerRegistration\Logger\Logger;
use Amore\CustomerRegistration\Helper\Data;
use Magento\Framework\Serialize\Serializer\Json;

/**
 * To add the message in the debug log file for API calls
 *
 * Class POSLogger
 * @package Amore\CustomerRegistration\Model
 */
class POSLogger
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
     * @param $message
     * @param $url
     * @param $parameters
     * @return void
     */
    public function addAPICallLog($message, $url, $parameters)
    {
        if ($this->confg->getDebug()) {
            $this->logger->info($message);
            $this->logger->info($url);
            $temp = json_encode($parameters);
            $this->logger->info($this->json->serialize($parameters));
        }
    }

    /**
     * @param $message
     * @return void
     */
    public function addExceptionMessage($message)
    {
        if ($this->confg->getDebug()) {
            $this->logger->info($message);
        }
    }

    /**
     * @param $message
     * @param $parameters
     * @return void
     */
    public function addAPILog($message, $parameters){
        if ($this->confg->getDebug()) {
            $this->logger->info($message);
            $this->logger->info($this->json->serialize($parameters));
        }
    }
}