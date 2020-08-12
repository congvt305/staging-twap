<?php
/**
 * @author Eguana Team
 * @copyriht Copyright (c) 2020 Eguana {http://eguanacommerce.com}
 * Created by PhpStorm
 * User: abbas
 * Date: 20. 6. 12
 * Time: 오전 10:22
 */

namespace Amore\CustomerRegistration\Model;

use Magento\Framework\App\ResourceConnection as AppResource;
use Magento\Framework\DB\Sequence\SequenceInterface;
use Magento\Framework\App\ResourceConnection;
use Magento\Store\Model\StoreManagerInterface;
use Amore\CustomerRegistration\Helper\Data;

/**
 * To create a customer sequence number like an incrmement id
 *
 * Class Sequence
 * @package Amore\CustomerRegistration\Model
 */
class Sequence implements SequenceInterface
{
    /**
     * Default pattern for Sequence
     */
    const DEFAULT_PATTERN  = "%s%'.08d";

    const DEFAULT_CUSTOMER_TYPE  = "online";

    /**
     * @var string
     */
    private $lastIncrementId;

    /**
     * @var Meta
     */
    private $meta;

    /**
     * @var false|\Magento\Framework\DB\Adapter\AdapterInterface
     */
    private $connection;

    /**
     * @var string
     */
    private $pattern;

    /**
     * @var string
     */
    private $customerType;

    /**
     * @var string
     */
    private $customerWebsiteId;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;
    /**
     * @var Data
     */
    private $configHelper;

    /**
     * @param AppResource $resource
     * @param string $pattern
     */
    public function __construct(
        Data $configHelper,
        AppResource $resource,
        StoreManagerInterface $storeManager,
        $pattern = self::DEFAULT_PATTERN,
        $customerType = self::DEFAULT_CUSTOMER_TYPE
    ) {
        $this->connection = $resource->getConnection(ResourceConnection::DEFAULT_CONNECTION);
        $this->pattern = $pattern;
        $this->customerType = $customerType;
        $this->storeManager = $storeManager;
        $this->configHelper = $configHelper;
    }

    /**
     * Retrieve current value
     *
     * @return string
     */
    public function getCurrentValue()
    {
        if (!isset($this->lastIncrementId)) {
            return null;
        }

        $channel = $this->customerType == 'online'?'2':'1';

        return sprintf(
            $this->pattern,
            $this->configHelper->getOfficeSalesCode().$channel,
            $this->calculateCurrentValue(),
            ''
        );
    }

    /**
     * Retrieve next value
     *
     * @return string
     */
    public function getNextValue()
    {
        $this->connection->insert($this->getCurrentWebsiteTable(), []);
        $this->lastIncrementId = $this->connection->lastInsertId($this->getCurrentWebsiteTable());
        return $this->getCurrentValue();
    }

    /**
     * Calculate current value depends on start value
     *
     * @return string
     */
    private function calculateCurrentValue()
    {
        return $this->lastIncrementId;/*($this->lastIncrementId - $this->meta->getActiveProfile()->getStartValue())
            * $this->meta->getActiveProfile()->getStep() + $this->meta->getActiveProfile()->getStartValue();*/
    }

    private function getCurrentWebsiteTable()
    {
        $websiteId = $this->customerWebsiteId?$this->customerWebsiteId:$this->storeManager->getStore()->getWebsiteId();
        return sprintf('sequence_customer_%s_%d', $this->customerType, $websiteId);
    }

    public function setCustomerType($customerType)
    {
        $this->customerType = $customerType;
    }

    public function setCustomerWebsiteid($webisteid)
    {
        $this->customerWebsiteId = $webisteid;
    }
}
