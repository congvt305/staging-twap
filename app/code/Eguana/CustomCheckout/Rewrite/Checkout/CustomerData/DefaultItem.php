<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Eguana\CustomCheckout\Rewrite\Checkout\CustomerData;


use Magento\Catalog\Model\Product\Configuration\Item\ItemResolverInterface;

class DefaultItem extends \Magento\Checkout\CustomerData\DefaultItem
{

    /**
     * @var \Amasty\Promo\Helper\Item
     */
    private $promoItemHelper;

    public function __construct(
        \Amasty\Promo\Helper\Item $promoItemHelper,
        \Magento\Catalog\Helper\Image $imageHelper,
        \Magento\Msrp\Helper\Data $msrpHelper,
        \Magento\Framework\UrlInterface $urlBuilder,
        \Magento\Catalog\Helper\Product\ConfigurationPool $configurationPool,
        \Magento\Checkout\Helper\Data $checkoutHelper,
        \Magento\Framework\Escaper $escaper = null,
        ItemResolverInterface $itemResolver = null
    ) {
        parent::__construct(
            $imageHelper,
            $msrpHelper,
            $urlBuilder,
            $configurationPool,
            $checkoutHelper,
            $escaper,
            $itemResolver
        );
        $this->promoItemHelper = $promoItemHelper;
    }

    /**
     * {@inheritdoc}
     */
    protected function doGetItemData()
    {
        $itemData = parent::doGetItemData();
        $itemData['gift'] = $this->promoItemHelper->isPromoItem($this->item)?true:false;
        return $itemData;
    }

}
