<?php

namespace CJ\PointRedemption\Plugin;

use Magento\Framework\Pricing\Render\RendererPool;
use Magento\Framework\Pricing\SaleableInterface;
use CJ\PointRedemption\Setup\Patch\Data\AddRedemptionAttributes;

class PriceRender
{
    protected $template = 'CJ_PointRedemption::product/price/redemption_point_amount.phtml';

    /**
     * Display point amount instead of price
     * @param RendererPool $subject
     * @param $renderBlock
     * @param $priceCode
     * @param SaleableInterface $saleableItem
     * @return mixed
     */
    public function afterCreatePriceRender(
        RendererPool      $subject,
        $renderBlock,
        $priceCode,
        SaleableInterface $saleableItem
    ) {
        $isPointRedeemableProduct = $saleableItem->getData(AddRedemptionAttributes::IS_POINT_REDEEMABLE_ATTRIBUTE_CODE);
        if ($isPointRedeemableProduct) {
            return $renderBlock->setTemplate($this->template);
        }
        return $renderBlock;
    }
}
