<?php

declare(strict_types=1);

namespace CJ\PageBuilder\Helper;

/**
 * Class Data
 */
class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * @var \CJ\PageBuilder\Model\Config\Config
     */
    protected $config;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @inheritDoc
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \CJ\PageBuilder\Model\Config\Config $config,
        \Magento\Store\Model\StoreManagerInterface $storeManager
    ) {
        $this->config = $config;
        $this->storeManager = $storeManager;
        parent::__construct($context);
    }

    /**
     * @return int
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    protected function getStoreId(): int
    {
        return (int)$this->storeManager->getStore()->getId();
    }

    /**
     * @return bool
     */
    public function getConfigEngNameVisible(): bool
    {
        try {
            return $this->config->getEnglishProductNameVisible('store', $this->getStoreId());
        } catch (\Exception $e) {
            return false;
        }

    }

    /**
     * @return bool
     */
    public function getConfigDescVisible(): bool
    {
        try {
            return $this->config->getProductDescriptionVisible('store', $this->getStoreId());
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Get config display description below title
     *
     * @return bool
     */
    public function getConfigDescriptionBelowTitleEnabled(): bool
    {
        try {
            return $this->config->getDescriptionBelowTitleEnabled('store', $this->getStoreId());
        } catch (\Exception $e) {
            return false;
        }
    }
}
