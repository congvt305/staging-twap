<?php
/**
 * @author Eguana Team
 * @copyriht Copyright (c) 2021 Eguana {http://eguanacommerce.com}
 * Created by PhpStorm
 * User: arslan
 * Date: 24/5/21
 * Time: 5:46 PM
 */
namespace Eguana\RedInvoice\Model;

use Eguana\RedInvoice\Logger\Logger;
use Magento\Framework\Serialize\Serializer\Json;
use Eguana\RedInvoice\Model\RedInvoiceConfig\RedInvoiceConfig;
use Magento\Store\Model\StoreManagerInterface;

/**
 * To add the message & params in the debug log file for API calls
 * Class RedInvoiceLogger
 */
class RedInvoiceLogger
{
    /**
     * @var Json
     */
    private $json;

    /**
     * Logging instance
     * @var Logger
     */
    private $logger;

    /**
     * @var RedInvoiceConfig
     */
    private $redInvoiceConfig;

    /**
     * @var StoreManagerInterface
     */
    private $storeManage;

    /**
     * RedInvoiceLogger constructor.
     * @param Json $json
     * @param Logger $logger
     * @param RedInvoiceConfig $redInvoiceConfig
     */
    public function __construct(
        Json $json,
        Logger $logger,
        RedInvoiceConfig $redInvoiceConfig,
        StoreManagerInterface $storeManage
    ) {
        $this->logger = $logger;
        $this->json = $json;
        $this->redInvoiceConfig = $redInvoiceConfig;
        $this->storeManage = $storeManage;
    }

    /**
     * Add red invoice info in log file
     * @param $message
     * @param $redInvoiceInfo
     */
    public function logRedInvoiceInfo($message, $redInvoiceInfo)
    {
        $websiteId = $this->storeManage->getWebsite()->getId();
        $isModuleEnabled = $this->redInvoiceConfig->getEnableValue($websiteId);
        $isDebugEnabled = $this->redInvoiceConfig->getDebugEnableValue($websiteId);
        if ($isModuleEnabled && $isDebugEnabled) {
            $this->logger->info($message);
            $this->logger->info($this->json->serialize($redInvoiceInfo));
        }
    }
}
