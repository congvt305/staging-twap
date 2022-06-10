<?php

namespace CJ\GAEnhanced\ViewModel;

use Magento\Framework\View\Element\Block\ArgumentInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Framework\App\Request\Http;
use Magento\Framework\Registry;

class GetGAEnhancedData implements ArgumentInterface
{
    /**
     * Constants for XML Paths.
     */
    const XML_PATH_PRODUCT_PER_PAGE = 'catalog/frontend/grid_per_page';

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @var Http
     */
    private $request;

    /**
     * @var \Magento\Framework\Registry
     */
    private $registry;

    /**
     * @param ScopeConfigInterface $scopeConfig
     * @param Http $request
     * @param \Magento\Framework\Registry $registry
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        Http $request,
        Registry $registry
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->request = $request;
        $this->registry = $registry;
    }

    /**
     * Get Product per page
     *
     * @return int|mixed
     */
    public function getProductPerPage()
    {
        return $this->scopeConfig->getValue(self::XML_PATH_PRODUCT_PER_PAGE, ScopeInterface::SCOPE_STORE);
    }

    /**
     * Get current page
     *
     * @return int|mixed
     */
    public function getCurrentPage()
    {
        return $this->request->getParam('p') ? $this->request->getParam('p') : 1;
    }

    /**
     * get Current Category
     *
     * @return mixed|null
     */
    public function getCurrentCategory()
    {
        return $this->registry->registry('current_category');
    }

    /**
     * get Root Category
     *
     * @return mixed|null
     */
    public function getRooCategory()
    {
        if ($this->getCurrentCategory()) {
            if ($this->getCurrentCategory()->getParentCategories()) {
                foreach ($this->getCurrentCategory()->getParentCategories() as $parent) {
                    if ($parent->getLevel() == 2) {
                        // return the level 2 category name;
                        return $parent->getName();
                    }
                }
            }
        }
        return null;
    }

    /**
     * Is enabled GA Enhanced
     *
     * @return bool
     */
    public function isEnabledGAEnhanced()
    {
        return (boolean)$this->getCurrentCategory()->getData('is_enabled_ga_enhanced');

    }
}
