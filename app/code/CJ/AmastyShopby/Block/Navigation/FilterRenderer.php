<?php

namespace CJ\AmastyShopby\Block\Navigation;

use Amasty\Shopby\Block\Navigation\FilterRenderer as AmastyFilterRenderer;
use Amasty\Shopby\Helper\Category;
use Amasty\Shopby\Helper\Data as ShopbyHelper;
use Amasty\Shopby\Helper\FilterSetting;
use Amasty\Shopby\Helper\UrlBuilder;
use Amasty\Shopby\Model\ConfigProvider;
use Amasty\Shopby\Model\Source\DisplayMode;
use Amasty\ShopbyBase\Api\Data\FilterSettingInterface;
use Amasty\ShopbyBase\Model\FilterSetting\IsApplyFlyOut;
use Amasty\ShopbyBase\Model\FilterSetting\IsMultiselect;
use Amasty\ShopbyBase\Model\FilterSetting\IsShowProductQuantities;
use Magento\Catalog\Model\Layer\Filter\FilterInterface;
use Magento\Catalog\Model\Layer\Resolver;
use Magento\Framework\View\Element\Template\Context;
use Magento\Store\Model\ScopeInterface;
class FilterRenderer extends AmastyFilterRenderer
{
    public const TOP_NAV_RENDERER_NAME = 'amshopby.catalog.topnav.renderer';

    /**
     * @var IsApplyFlyOut
     */
    private $isApplyFlyOut;

    /**
     * @var IsShowProductQuantities
     */
    private $isShowProductQuantities;

    /**
     * @var ConfigProvider|null
     */
    private $configProvider;

    /**
     * @var IsMultiselect
     */
    private $isMultiselect;

    public function __construct(
        Context $context,
        FilterSetting $settingHelper,
        UrlBuilder $urlBuilder,
        ShopbyHelper $helper,
        Category $categoryHelper,
        Resolver $resolver,
        IsApplyFlyOut $isApplyFlyOut,
        IsShowProductQuantities $isShowProductQuantities,
        ConfigProvider $configProvider,
        IsMultiselect $isMultiselect,
        array $data = []
    ) {
        parent::__construct(
            $context,
            $settingHelper,
            $urlBuilder,
            $helper,
            $categoryHelper,
            $resolver,
            $isApplyFlyOut,
            $isShowProductQuantities,
            $configProvider,
            $isMultiselect,
            $data
        );
        $this->isApplyFlyOut = $isApplyFlyOut;
        $this->isShowProductQuantities = $isShowProductQuantities;
        $this->configProvider = $configProvider;
        $this->isMultiselect = $isMultiselect;
    }
    /**
     * @param FilterInterface $filter
     * @return string
     */
    public function render(FilterInterface $filter)
    {
        if ($this->isEnableFilterNavigation() || $filter instanceof \Amasty\Shopby\Model\Layer\Filter\Category) {
            return parent::render($filter);
        } else {
            $this->setTemplate('Magento_LayeredNavigation::layer/filter.phtml');
            $this->assign('filterItems', $filter->getItems());
            $html = $this->_toHtml();
            $this->assign('filterItems', []);
            return $html;
        }
    }

    protected function getTemplateByFilterSetting(FilterSettingInterface $filterSetting)
    {
        switch ($filterSetting->getDisplayMode()) {
            case DisplayMode::MODE_SLIDER:
                $template = "Amasty_Shopby::layer/filter/slider.phtml";
                break;
            case DisplayMode::MODE_FROM_TO_ONLY:
                $template = "Amasty_Shopby::layer/widget/fromto.phtml";
                break;
            default:
                $template = "Amasty_Shopby::layer/filter/default.phtml";
                break;
        }
        return $template;
    }

    protected function getCustomTemplateForCategoryFilter(FilterSettingInterface $filterSetting)
    {
        switch ($filterSetting->getDisplayMode()) {
            case DisplayMode::MODE_DROPDOWN:
                $template = "Amasty_Shopby::layer/filter/category/dropdown.phtml";
                break;
            default:
                if ($this->isApplyFlyOut->execute((int) $filterSetting->getSubcategoriesView())) {
                    $template = 'Amasty_Shopby::layer/filter/category/labels_fly_out.phtml';
                } else {
                    $template = 'Amasty_Shopby::layer/filter/category/labels_folding.phtml';
                }
                break;
        }
        return $template;
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
