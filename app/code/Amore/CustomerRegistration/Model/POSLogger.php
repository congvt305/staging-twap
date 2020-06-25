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

use Psr\Log\LoggerInterface;
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

    public function __construct(
        Data $confg,
        LoggerInterface $logger,
        Json $json
    ) {
        $this->confg = $confg;
        $this->logger = $logger;
        $this->json = $json;
    }

    public function addAPICallLog($message, $url, $parameters)
    {
        $this->logger->info('POS info test outside');
        $this->logger->debug('POS debug Test outside');
        if ($this->confg->getDebug()) {
            $this->logger->info('POS info test inside');
            $this->logger->debug($message);
            $this->logger->debug($url);
            $this->logger->debug($this->json->serialize($parameters));
        }
    }

    public function addExceptionMessage($message)
    {
        $this->logger->debug($message);
    }
}