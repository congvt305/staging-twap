<?php
declare(strict_types=1);

namespace CJ\Catalog\ViewModel;

/**
 * Class CategoryDescription
 */
class CategoryDescription implements \Magento\Framework\View\Element\Block\ArgumentInterface
{
    /**
     * @var \Magento\Catalog\Helper\Output
     */
    protected $catalogHelper;

    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $coreRegistry = null;

    /**
     * @var \CJ\PageBuilder\Model\Config\Config
     */
    protected $configHelper;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @param \Magento\Catalog\Helper\Output $catalogHelper
     * @param \Magento\Framework\Registry $coreRegistry
     * @param \CJ\PageBuilder\Model\Config\Config $configHelper
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     */
    public function __construct(
        \Magento\Catalog\Helper\Output $catalogHelper,
        \Magento\Framework\Registry $coreRegistry,
        \CJ\PageBuilder\Model\Config\Config $configHelper,
        \Magento\Store\Model\StoreManagerInterface $storeManager
    ) {
        $this->catalogHelper = $catalogHelper;
        $this->configHelper = $configHelper;
        $this->coreRegistry = $coreRegistry;
        $this->storeManager = $storeManager;
    }

    /**
     * Get current category
     *
     * @return \Magento\Catalog\Model\Category|null
     */
    public function getCurrentCategory()
    {
        return $this->coreRegistry->registry('current_category');
    }

    /**
     * Get catalog helper
     *
     * @return \Magento\Catalog\Helper\Output
     */
    public function getCatalogHelper(): \Magento\Catalog\Helper\Output
    {
        return $this->catalogHelper;
    }

    /**
     * Check if the config shows the category description below the title is enabled
     *
     * @return bool
     */
    public function descriptionBelowTitleEnabled(): bool
    {
        try {
            return $this->configHelper->getDescriptionBelowTitleEnabled('store', (int)$this->getStoreId());
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @return int
     */
    protected function getStoreId(): int
    {
        return $this->storeManager->getStore()->getId();
    }
}
