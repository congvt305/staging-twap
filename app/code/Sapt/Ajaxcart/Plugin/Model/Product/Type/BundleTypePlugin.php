<?php
declare(strict_types=1);

namespace Sapt\Ajaxcart\Plugin\Model\Product\Type;

use Magento\Bundle\Model\Product\SingleChoiceProvider;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Type;
use Magento\Catalog\Model\Product\Type\AbstractType as Subject;

/**
 * Class BundleTypePlugin
 *
 * Plugin to add possibility to add bundle product with single option from list (clone from Magento, applied for SAPT Theme)
 */
class BundleTypePlugin
{
    const APPLIED_STORES = ['tw_laneige', 'default'];

    /**
     * @var SingleChoiceProvider
     */
    private SingleChoiceProvider $singleChoiceProvider;

    /**
     * @param SingleChoiceProvider $singleChoiceProvider
     */
    public function __construct(
        SingleChoiceProvider $singleChoiceProvider
    ) {
        $this->singleChoiceProvider = $singleChoiceProvider;
    }

    /**
     * Add possibility to add to cart from the list in case of one required option
     *
     * @param Subject $subject
     * @param bool $result
     * @param Product $product
     * @return bool
     */
    public function afterIsPossibleBuyFromList(Subject $subject, $result, $product)
    {
        if ($product->getTypeId() === Type::TYPE_BUNDLE
            && in_array($product->getStore()->getCode(), self::APPLIED_STORES)
        ) {
            $isSingleChoice = $this->singleChoiceProvider->isSingleChoiceAvailable($product);
            if ($isSingleChoice === true) {
                $result = true;
            }
        }

        return $result;
    }
}
