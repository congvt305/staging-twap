<?php
declare(strict_types=1);

namespace CJ\Catalog\Model;

use Magento\Framework\Exception\NoSuchEntityException;

/**
 * Class Config
 */
class Config
{
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
     * Get store Id
     *
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    protected function getStoreId(): int
    {
        return (int)$this->storeManager->getStore()->getId();
    }
}
