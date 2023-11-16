<?php

namespace Amasty\Shopby\Block\Navigation\Top;

use Magento\Framework\Registry;

class Navigation extends \Magento\LayeredNavigation\Block\Navigation
{
    public const PRODUCT_LISTING_SEARCH_BLOCK = 'search.result';
    public const PRODUCT_LISTING_TOOLBAR_BLOCK = 'product_list_toolbar';
    public const SEARCH_SORTING = 'amsorting_search';

    /**
     * @var Registry
     */
    private $registry;

    public function __construct(
        Registry $registry,
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Catalog\Model\Layer\Resolver $layerResolver,
        \Magento\Catalog\Model\Layer\FilterList $filterList,
        \Magento\Catalog\Model\Layer\AvailabilityFlagInterface $visibilityFlag,
        array $data = []
    ) {
        parent::__construct($context, $layerResolver, $filterList, $visibilityFlag, $data);
        $this->registry = $registry;
    }

    /**
     * @return \Magento\LayeredNavigation\Block\Navigation
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _beforeToHtml()
    {
        $productListingBlock = $this->getLayout()->getBlock(self::PRODUCT_LISTING_SEARCH_BLOCK);

        if ($productListingBlock) {
            $toolbarBlock = $this->getLayout()->getBlock(self::PRODUCT_LISTING_TOOLBAR_BLOCK);

            if ($toolbarBlock) {
                $toolbarBlock->setData('_current_grid_order', null);
                $toolbarBlock->setData('_current_grid_direction', null);
                $orders = $toolbarBlock->getAvailableOrders();
                unset($orders['position']);
                $orders['relevance'] = __('Relevance');
                $toolbarBlock->setAvailableOrders(
                    $orders
                )->setDefaultDirection(
                    'desc'
                );

                if (!$this->registry->registry(self::SEARCH_SORTING)) {
                    $toolbarBlock->setDefaultOrder('relevance');
                }
            }
        }

        return parent::_beforeToHtml();
    }
}
