<?php

namespace CJ\AmastyShopby\Block\Navigation;

use Amasty\Shopby\Block\Navigation\FilterRenderer as AmastyFilterRenderer;
use Magento\Catalog\Model\Layer\Filter\FilterInterface;
use Magento\Store\Model\ScopeInterface;

class FilterRenderer extends AmastyFilterRenderer
{
    /**
     * @param FilterInterface $filter
     * @return string
     */
    public function render(FilterInterface $filter)
    {
        if ($this->isEnableFilterNavigation()) {
            return parent::render($filter);
        } else {
            $this->assign('filterItems', $filter->getItems());
            $html = $this->_toHtml();
            $this->assign('filterItems', []);
            return $html;
        }
    }

    /**
     * @return bool
     */
    protected function isEnableFilterNavigation()
    {
        return $this->_scopeConfig->isSetFlag(
            'amshopby/general/enable_filter_navigation',
            ScopeInterface::SCOPE_STORE
        );
    }
}
