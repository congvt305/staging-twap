<?php


namespace Sapt\AjaxWishlist\Plugin;


use Magento\Store\Model\StoreManagerInterface;
use Sapt\AjaxWishlist\ViewModel\AjaxWishlistStatus;

class CatalogWidgetProductList
{
    const SAPT_THEME_STORE_CODE = ['tw_laneige', 'default'];

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;
    /**
     * @var AjaxWishlistStatus
     */
    private $viewModel;

    /**
     * @var \Magento\Catalog\ViewModel\Product\OptionsData
     */
    private $optionsDataViewModel;

    public function __construct(
        AjaxWishlistStatus $viewModel,
        StoreManagerInterface $storeManager,
        \Magento\Catalog\ViewModel\Product\OptionsData $optionsDataViewModel
    ) {
        $this->viewModel = $viewModel;
        $this->storeManager = $storeManager;
        $this->optionsDataViewModel = $optionsDataViewModel;
    }

    public function beforeToHtml(
        \Magento\CatalogWidget\Block\Product\ProductsList $block
    ) {
        if (in_array($this->storeManager->getStore()->getCode(), self::SAPT_THEME_STORE_CODE)) {
            $block->setData('wishlistStatusViewModel', $this->viewModel);
            $block->setData('optionDataViewModel', $this->optionsDataViewModel);
        }
        return [];
    }
}
