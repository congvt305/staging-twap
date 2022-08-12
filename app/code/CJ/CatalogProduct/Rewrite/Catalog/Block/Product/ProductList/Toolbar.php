<?php

namespace CJ\CatalogProduct\Rewrite\Catalog\Block\Product\ProductList;

use Magento\Catalog\Helper\Product\ProductList;
use Magento\Catalog\Model\Config;
use Magento\Catalog\Model\Product\ProductList\Toolbar as ToolbarModel;
use Magento\Catalog\Model\Product\ProductList\ToolbarMemorizer;
use Magento\Catalog\Model\Session;
use Magento\Framework\App\Http\Context;
use Magento\Framework\Data\Collection;
use Magento\Framework\Data\Form\FormKey;
use Magento\Framework\Data\Helper\PostHelper;
use Magento\Framework\Url\EncoderInterface;
use CJ\CatalogProduct\Helper\Data as HelperData;

class Toolbar extends \Magento\Catalog\Block\Product\ProductList\Toolbar
{
    protected $helperData;

    public function __construct(
        HelperData $helperData,
        \Magento\Framework\View\Element\Template\Context $context,
        Session $catalogSession,
        Config $catalogConfig,
        ToolbarModel $toolbarModel,
        EncoderInterface $urlEncoder,
        ProductList $productListHelper,
        PostHelper $postDataHelper,
        array $data = [],
        ToolbarMemorizer $toolbarMemorizer = null,
        Context $httpContext = null,
        FormKey $formKey = null
    )
    {
        parent::__construct($context, $catalogSession, $catalogConfig, $toolbarModel, $urlEncoder, $productListHelper, $postDataHelper, $data, $toolbarMemorizer, $httpContext, $formKey);
        $this->helperData = $helperData;
    }

    /**
     * Set collection to pager
     *
     * @param Collection $collection
     * @return \Magento\Catalog\Block\Product\ProductList\Toolbar
     */
    public function setCollection($collection)
    {
        $store = $this->_storeManager->getStore();
        $this->_collection = $collection;

        $this->_collection->setCurPage($this->getCurrentPage());

        // we need to set pagination only if passed value integer and more that 0
        $limit = (int)$this->getLimit();
        if ($limit) {
            $this->_collection->setPageSize($limit);
        }
        if ($currentOrder = $this->getCurrentOrder()) {
            if ($this->helperData->getEnableFilterOnSale($store->getId()) && $this->_request->getParam('on_sale')) {

                $this->_collection->getSelect()->where('price_index.final_price < price_index.price')
                    ->columns('(1 - (price_index.final_price / price_index.price)) AS discount')
                    ->order('discount DESC');
            }
            if (($this->getCurrentOrder()) == 'position') {
                $this->_collection->addAttributeToSort(
                    $this->getCurrentOrder(),
                    $this->getCurrentDirection()
                );
            } elseif ($store->getCode() == 'my_sulwhasoo') {
                if ($currentOrder == 'high_to_low') {
                    $this->_collection->setOrder('price', 'desc');
                } elseif ($currentOrder == 'low_to_high') {
                    $this->_collection->setOrder('price', 'asc');
                }
            } else {
                $this->_collection->setOrder($this->getCurrentOrder(), $this->getCurrentDirection());
            }
        }
        return $this;
    }
}
