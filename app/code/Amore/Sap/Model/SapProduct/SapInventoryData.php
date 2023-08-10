<?php
/**
 * @author Eguana Team
 * @copyriht Copyright (c) 2020 Eguana {http://eguanacommerce.com}
 * Created by PhpStorm
 * User: raheel
 * Date: 18/3/21
 * Time: 6:14 PM
 */
declare(strict_types=1);

namespace Amore\Sap\Model\SapProduct;

use Amore\Sap\Logger\Logger as SapLogger;
use Amore\Sap\Model\Source\Config;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\HTTP\Client\Curl;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Store\Model\StoreManagerInterface;
use Psr\Log\LoggerInterface;

/**
 * To get currnent inventory stock data from SAP
 *
 * Class SapInventoryData
 */
class SapInventoryData
{
    /**
     * @var Config
     */
    private $sapConfig;

    /**
     * @var SapLogger
     */
    private $sapLogger;

    /**
     * @var Curl
     */
    private $curlClient;

    /**
     * @var Json
     */
    private $json;

    /**
     * @var ManagerInterface
     */
    private $eventManager;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @param Curl $curl
     * @param Json $json
     * @param Config $sapConfig
     * @param SapLogger $sapLogger
     * @param LoggerInterface $logger
     * @param ManagerInterface $eventManager
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        Curl $curl,
        Json $json,
        Config $sapConfig,
        SapLogger $sapLogger,
        LoggerInterface $logger,
        ManagerInterface $eventManager,
        StoreManagerInterface $storeManager
    ) {
        $this->json = $json;
        $this->logger = $logger;
        $this->sapConfig = $sapConfig;
        $this->sapLogger = $sapLogger;
        $this->curlClient = $curl;
        $this->eventManager = $eventManager;
        $this->storeManager = $storeManager;
    }

    /**
     * Get Allow Statuses
     *
     * @return string[]
     */
    public function getAllowedStatuses()
    {
        return [
            'preparing',
            'sap_processing',
            'processing_with_shipment',
            'sap_success',
            'sap_fail'
        ];
    }
}
