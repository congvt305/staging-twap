<?php

namespace CJ\DataExport\Model\Config;

use Magento\Store\Model\ScopeInterface;

/**
 * Class Config
 */
class Config
{
    const XML_PATH_ENABLE = 'cj_scheduleexport/general/enable';
    const XML_PATH_REDEMPTION_DURATION = 'cj_scheduleexport/redemption/duration_minutes';
    const XML_PATH_REDEMPTION_STORE_IDS = 'cj_scheduleexport/redemption/store_ids';
    const XML_PATH_ORDER_DURATION = 'cj_scheduleexport/order/duration_minutes';
    const XML_PATH_ORDER_STORE_IDS = 'cj_scheduleexport/order/store_ids';
    const XML_PATH_RMA_DURATION = 'cj_scheduleexport/rma/duration_minutes';
    const XML_PATH_RMA_STORE_IDS = 'cj_scheduleexport/rma/store_ids';

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
    ) {
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * Get Value of Configurations from Admin
     *
     * @param $field
     * @param null $storeId
     * @return mixed
     */
    public function getConfigValue($field, $storeId = null)
    {
        return $this->scopeConfig->getValue(
            $field, ScopeInterface::SCOPE_STORE, $storeId
        );
    }

    /**
     * @param null $storeId
     * @return mixed
     */
    public function getRedemptionDurationMinutes($storeId = null)
    {
        return $this->getConfigValue(self::XML_PATH_REDEMPTION_DURATION, $storeId);
    }

    /**
     * @param null $storeId
     * @return mixed
     */
    public function getOrderDurationMinutes($storeId = null)
    {
        return $this->getConfigValue(self::XML_PATH_ORDER_DURATION, $storeId);
    }

    /**
     * @param null $storeId
     * @return mixed
     */
    public function getModuleEnable($storeId = null)
    {
        return $this->getConfigValue(self::XML_PATH_ENABLE, $storeId);
    }

    /**
     * @param null $storeId
     * @return mixed
     */
    public function getRmaDurationMinutes($storeId = null)
    {
        return $this->getConfigValue(self::XML_PATH_RMA_DURATION, $storeId);
    }

    /**
     * @param null $storeId
     * @return mixed
     */
    public function getRedemptionStoreIds($storeId = null)
    {
        return $this->getConfigValue(self::XML_PATH_REDEMPTION_STORE_IDS, $storeId);
    }

    /**
     * @param null $storeId
     * @return mixed
     */
    public function getOrderStoreIds($storeId = null)
    {
        return $this->getConfigValue(self::XML_PATH_ORDER_STORE_IDS, $storeId);
    }

    /**
     * @param null $storeId
     * @return mixed
     */
    public function getRmaStoreIds($storeId = null)
    {
        return $this->getConfigValue(self::XML_PATH_RMA_STORE_IDS, $storeId);
    }
}
