<?php
declare(strict_types=1);

namespace CJ\AmastyPromo\Model\Rule\Condition\FilterTextGenerator\Product;

use Magento\AdvancedSalesRule\Model\Rule\Condition\ConcreteCondition\Product\Attribute as AttributeCondition;
use Amasty\Promo\Helper\Item as PromoItemHelper;
use Amasty\Promo\Plugin\SalesRule\Conditions\Product;

class Attribute extends \Magento\AdvancedSalesRule\Model\Rule\Condition\FilterTextGenerator\Product\Attribute
{
    /**
     * @var string
     */
    protected $attributeCode;

    protected PromoItemHelper $promoItemHelper;

    /**
     * @param PromoItemHelper $promoItemHelper
     * @param array $data
     */
    public function __construct(PromoItemHelper $promoItemHelper, array $data)
    {
        $this->promoItemHelper = $promoItemHelper;
        parent::__construct($data);
    }

    /**
     * @param \Magento\Framework\DataObject $quoteAddress
     * @return string[]
     */
    public function generateFilterText(\Magento\Framework\DataObject $quoteAddress)
    {
        $filterText = [];
        if ($quoteAddress instanceof \Magento\Quote\Model\Quote\Address) {
            $items = $quoteAddress->getAllItems();
            foreach ($items as $item) {
                $product = $item->getProduct();

                if (Product::CONDITION_ATTRIBUTE_NAME == $this->attributeCode) {
                    $product->setData(
                        Product::CONDITION_ATTRIBUTE_NAME,
                        (string)(int)$this->promoItemHelper->isPromoItem($item)
                    );
                }

                if (!$product->hasData($this->attributeCode)) {
                    $product->load($product->getId());
                }

                $value = $product->getData($this->attributeCode);
                if (is_scalar($value)) {
                    $text = AttributeCondition::FILTER_TEXT_PREFIX . $this->attributeCode . ':' . $value;
                    if (!in_array($text, $filterText)) {
                        $filterText[] = $text;
                    }
                }
            }
        }

        return $filterText;
    }
}