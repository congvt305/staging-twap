<?php

namespace CJ\PointRedemption\Plugin;

use CJ\PointRedemption\Helper\Data;
use Magento\Framework\Pricing\Render\RendererPool;
use Magento\Framework\Pricing\SaleableInterface;
use CJ\PointRedemption\Setup\Patch\Data\AddRedemptionAttributes;

class PriceRender
{
    protected $template = 'CJ_PointRedemption::product/price/redemption_point_amount.phtml';

    /**
     * @var Data
     */
    protected $pointRedemptionHelper;

    /**
     * @param Data $pointRedemptionHelper
     */
    public function __construct(Data $pointRedemptionHelper)
    {
        $this->pointRedemptionHelper = $pointRedemptionHelper;
    }

    /**
     * Display point amount instead of price
     * @param RendererPool $subject
     * @param $renderBlock
     * @param $priceCode
     * @param SaleableInterface $saleableItem
     * @return mixed
     */
    public function afterCreatePriceRender(
        RendererPool $subject,
        $renderBlock,
        $priceCode,
        SaleableInterface $saleableItem
    ) {
        $isPointRedeemableProduct = $saleableItem->getData(AddRedemptionAttributes::IS_POINT_REDEEMABLE_ATTRIBUTE_CODE);
        $isPointDisplay = $this->pointRedemptionHelper->isPointDisplay();
        if ($isPointRedeemableProduct & $isPointDisplay) {
            return $renderBlock->setTemplate($this->template);
        }
        return $renderBlock;
    }
}
