<?php
/**
 * @author Eguana Team
 * @copyriht Copyright (c) 2021 Eguana {http://eguanacommerce.com}
 * Created by PhpStorm
 * User: arslan
 * Date: 22/4/21
 * Time: 2:45 PM
 */
namespace Eguana\CustomAmastyPromo\Override\Helper;

use Amasty\Promo\Helper\Cart as CartAlias;
use Magento\Catalog\Model\Product\Type;
use Amasty\Promo\Model\ItemRegistry\PromoItemData;
use Amasty\Promo\Model\Product as ProductStock;
use Magento\Catalog\Model\Product;
use Magento\Framework\Exception\LocalizedException;
use Magento\Quote\Model\Quote;
use Amasty\Promo\Helper\Messages;

/**
 * In this class a preference has been added for the method addProduct
 * Class Cart
 */
class Cart extends CartAlias
{
    /**
     * @var Messages
     */
    private $promoMessagesHelper;

    /**
     * @var ProductStock
     */
    private $product;

    /**
     * Cart constructor.
     * @param Messages $promoMessagesHelper
     * @param ProductStock $product
     */
    public function __construct(
        Messages $promoMessagesHelper,
        ProductStock $product
    ) {
        parent::__construct(
            $promoMessagesHelper,
            $product
        );
        $this->promoMessagesHelper = $promoMessagesHelper;
        $this->product = $product;
    }

    /**
     * @param Product $product
     * @param int $qty
     * @param PromoItemData $promoItemData
     * @param array $requestParams
     * @param Quote|null $quote
     *
     * @return bool
     */
    public function addProduct(
        Product $product,
        $qty,
        $promoItemData,
        array $requestParams,
        Quote $quote
    ) {
        if ($product->getTypeId() == Type::TYPE_SIMPLE) {
            $qty = $this->resolveQty($product, $qty, $quote);
        }
        if ($qty == 0) {
            return false;
        }

        $ruleId = $promoItemData->getRuleId();
        //TODO ST-1949 process not free items with custom_price
        $requestParams['qty'] = $qty;
        $requestParams['options']['ampromo_rule_id'] = $ruleId;
        $requestParams['options']['discount'] = $promoItemData->getDiscountArray();

        try {
            $item = $quote->addProduct($product, new \Magento\Framework\DataObject($requestParams));

            if ($item instanceof \Magento\Quote\Model\Quote\Item) {
                $item->setData('ampromo_rule_id', $ruleId);
            } else {
                throw new LocalizedException(__($item));
            }

            //qty for promoItemData will be reserved later
            $promoItemData->isDeleted(false);
            return true;
        } catch (\Exception $e) {
            $this->promoMessagesHelper->showMessage(
                $e->getMessage(),
                true,
                true
            );
        }
        return false;
    }

    /**
     * @param Product $product
     * @param int $qty
     * @param Quote $quote
     * @return float|int
     */
    private function resolveQty($product, $qty, $quote)
    {
        $availableQty = $this->product->checkAvailableQty($product->getSku(), $qty, $quote);

        if ($availableQty <= 0) {
            $this->promoMessagesHelper->addAvailabilityError($product);

            $availableQty = 0;
        } elseif ($availableQty < $qty) {
            $this->promoMessagesHelper->showMessage(
                __(
                    "We apologize, but requested quantity of free gift <strong>%1</strong>"
                    . " is not available at the moment",
                    $product->getName()
                ),
                false,
                true
            );
        }
        return $availableQty;
    }
}
