<?php

namespace CJ\CustomCustomer\Helper;

use Magento\Framework\Exception\LocalizedException;

/**
 * Class Data
 */
class Data
{
    const XML_PATH_LOGGING_ENABLED = 'cjcustomer/group/logging';

    const XML_PATH_POS_CSTM_NO_SYN_ENABLED = 'cjcustomer/poscstmno/enable';

    const XML_PATH_POS_CSTM_NO_LAST_CSTM_ID = 'cjcustomer/poscstmno/last_customer';

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     */
    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Store\Model\StoreManagerInterface $storeManager
    ) {
        $this->storeManager = $storeManager;
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * @return bool
     */
    public function getLoggingEnabled(): bool
    {
        return (bool)$this->scopeConfig->getValue(self::XML_PATH_LOGGING_ENABLED);
    }

    /**
     * Return website id corresponding to the customer integration sequence
     *
     * @param ?string $cstmIntgSeq
     * @return int
     * @throws LocalizedException
     */
    public function getWebsiteIdByIntgSeq(?string $cstmIntgSeq): int
    {
        return $this->_getStoreByIntgSeq($cstmIntgSeq)->getWebsiteId();
    }

    /**
     * @throws LocalizedException
     */
    private function _getStoreByIntgSeq($cstmIntgSeq): \Magento\Store\Api\Data\StoreInterface
    {
        $store = null;

        foreach ($this->storeManager->getStores() as $_store) {
            $salesOfficeCode = $this->scopeConfig->getValue(\CJ\Middleware\Helper\Data::XML_PATH_MIDDLEWARE_SALES_OFF_CODE, 'store', $_store->getId());

            if ($salesOfficeCode == substr($cstmIntgSeq, 0, 4)) {
                $store = $_store;
                break;
            }
        }
        if ($store) {
            return $store;
        } else {
            throw new LocalizedException(__('Store is not found for this customer integration sequence "%1"', [$cstmIntgSeq]));
        }
    }

    /**
     * @param int|null $websiteId
     * @return bool
     */
    public function getPosCstmNOSynEnabled(?int $websiteId): bool {
        return (bool)$this->scopeConfig->getValue(self::XML_PATH_POS_CSTM_NO_SYN_ENABLED, 'website', $websiteId);
    }

    /**
     * @param int|null $websiteId
     * @return int
     */
    public function getPosCstmNOLastCstmId(?int $websiteId): int {
        return (int) $this->scopeConfig->getValue(self::XML_PATH_POS_CSTM_NO_LAST_CSTM_ID, 'website', $websiteId);
    }
}
