<?php
declare(strict_types=1);

namespace CJ\Catalog\Model;

use Magento\Framework\Exception\NoSuchEntityException;

/**
 * Class Config
 */
class Config
{
    const XML_PATH_DESC_UNDER_NAME_ENABLE = 'cj_custom_catalog/custom_catalog/description_under_name_enabled';

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
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
        $this->scopeConfig = $scopeConfig;
        $this->storeManager = $storeManager;
    }

    /**
     * If this setting is enabled then we will display
     * the description of the product under its name.
     * Otherwise, if this setting is disabled, the
     * description will be hidden - not be displayed
     * under product name.
     * Noted that we are discussing on PLP
     *
     * @return bool
     */
    public function getDescriptionUnderNameEnabled(): bool
    {
        try {
            $storeId = $this->getStoreId();
            return $this->scopeConfig->isSetFlag(self::XML_PATH_DESC_UNDER_NAME_ENABLE, 'store', $storeId);
        } catch (NoSuchEntityException $e) {
            return false;
        }
    }

    /**
     * Get store Id
     *
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    protected function getStoreId(): int
    {
        return (int)$this->storeManager->getStore()->getId();
    }
}
