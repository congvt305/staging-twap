<?php
declare(strict_types=1);
namespace CJ\CustomCustomer\Helper;

use Magento\Framework\Exception\LocalizedException;

/**
 * Class Data
 */
class Data
{
    const XML_PATH_LOGGING_ENABLED = 'cjcustomer/group/logging';

    const XML_PATH_POS_CSTM_NO_LIMIT = 'cjcustomer/poscstmno/limit';

    const XML_PATH_POS_CSTM_NO_CRON_ENABLED = 'cjcustomer/poscstmno/enable_cron';

    const XML_PATH_MEMBERSHIP_BENEIFTS_URL = 'cjcustomer/general/membership_benefits_url';

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
        return (int)$this->_getStoreByIntgSeq($cstmIntgSeq)->getWebsiteId();
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
     * @return int
     */
    public function getPosCstmNOLimit(): int {
        return (int) $this->scopeConfig->getValue(self::XML_PATH_POS_CSTM_NO_LIMIT);
    }

    /**
     * @return bool
     */
    public function getPosCstmNOCronEnabled(): bool {
        return (bool) $this->scopeConfig->getValue(self::XML_PATH_POS_CSTM_NO_CRON_ENABLED);
    }
    /**
     * @return string
     */
    public function getMembershipBenefitsUrl()
    {
        return $this->scopeConfig->getValue(self::XML_PATH_MEMBERSHIP_BENEIFTS_URL, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

}
