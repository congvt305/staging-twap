<?php

namespace Amasty\Shopby\Block\Product\ProductList;

use Amasty\Shopby\Helper\Data;
use Amasty\ShopbyBase\Model\Detection\MobileDetect;
use Magento\Catalog\Model\Layer\Resolver;
use Magento\Framework\Module\Manager;
use Magento\Framework\Registry;
use Amasty\Shopby\Model\Layer\FilterList;
use \Magento\Framework\DataObject\IdentityInterface;
use \Magento\Catalog\Model\Product\ProductList\ToolbarMemorizer;
use Magento\Framework\View\Element\Template\Context;

/**
 * @api
 */
class Ajax extends \Magento\Framework\View\Element\Template implements IdentityInterface
{
    public const CACHE_TAG = 'client_';

    /**
     * @var Data
     */
    private $helper;

    /**
     * @var \Magento\Catalog\Model\Layer
     */
    private $layer;

    /**
     * @var Registry
     */
    private $registry;

    /**
     * @var Manager
     */
    private $moduleManager;

    /**
     * @var ToolbarMemorizer
     */
    private $toolbarMemorizer;

    /**
     * @var MobileDetect
     */
    private $mobileDetect;

    public function __construct(
        Context $context,
        Resolver $layerResolver,
        Data $helper,
        Registry $registry,
        Manager $moduleManager,
        ToolbarMemorizer $toolbarMemorizer,
        MobileDetect $mobileDetect,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->layer = $layerResolver->get();
        $this->helper = $helper;
        $this->registry = $registry;
        $this->moduleManager = $moduleManager;
        $this->toolbarMemorizer = $toolbarMemorizer;
        $this->mobileDetect = $mobileDetect;
    }

    /**
     * @return bool
     */
    public function isGoogleTagManager()
    {
        return $this->moduleManager->isEnabled('Magento_GoogleTagManager');
    }

    /**
     * @return bool
     */
    public function canShowBlock()
    {
        return $this->helper->isAjaxEnabled();
    }

    public function submitByClick(): int
    {
        return $this->helper->collectFilters();
    }

    /**
     * @return string[]
     */
    public function getIdentities()
    {
        return [self::CACHE_TAG . '_' . $this->mobileDetect->isMobile() ? 'mobile' : 'desktop'];
    }

    public function scrollUp(): int
    {
        return (int) $this->_scopeConfig->getValue('amshopby/general/ajax_scroll_up');
    }

    /**
     * Retrieve active filters
     *
     * @return array
     */
    protected function getActiveFilters()
    {
        $filters = $this->layer->getState()->getFilters();
        if (!is_array($filters)) {
            $filters = [];
        }
        return $filters;
    }

    /**
     * Retrieve Clear Filters URL
     *
     * @return string
     */
    public function getClearUrl()
    {
        return $this->helper->getAjaxCleanUrl($this->getActiveFilters());
    }

    public function getCurrentCategoryId(): int
    {
        return (int) $this->helper->getCurrentCategory()->getId();
    }

    public function isCategorySingleSelect(): int
    {
        $allFilters = $this->registry->registry(FilterList::ALL_FILTERS_KEY, []);
        foreach ($allFilters as $filter) {
            if ($filter instanceof \Amasty\Shopby\Model\Layer\Filter\Category) {
                return (int) !$filter->isMultiselect();
            }
        }

        return 0;
    }

    /**
     * Get config
     *
     * @param string $path
     * @return string
     */
    public function getConfig($path)
    {
        return $this->_scopeConfig->getValue($path, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

    /**
     * @return string
     */
    public function getGtmAccountId()
    {
        return $this->getConfig(\Magento\GoogleTagManager\Helper\Data::XML_PATH_CONTAINER_ID);
    }

    public function isMemorizingAllowed(): int
    {
        return (int) $this->toolbarMemorizer->isMemorizingAllowed();
    }
}
