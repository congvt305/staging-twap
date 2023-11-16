<?php
declare(strict_types=1);

namespace Amasty\CheckoutGraphQl\Plugin\QuoteGraphQl\Model\Cart\SetBillingAddressOnCart;

use Amasty\CheckoutGraphQl\Model\Utils\Address\CustomAttributesSetter;
use Amasty\CheckoutGraphQl\Model\Utils\Address\FillEmptyData;
use Amasty\CheckoutGraphQl\Model\Utils\Address\Validator;
use Magento\GraphQl\Model\Query\ContextInterface;
use Magento\Quote\Api\Data\CartInterface;
use Magento\QuoteGraphQl\Model\Cart\SetBillingAddressOnCart;

class SetCustomAttributes
{
    /**
     * @var FillEmptyData
     */
    private $fillEmptyData;

    /**
     * @var Validator
     */
    private $addressValidator;

    /**
     * @var CustomAttributesSetter
     */
    private $customAttrSetter;

    public function __construct(
        FillEmptyData $fillEmptyData,
        Validator $addressValidator,
        CustomAttributesSetter $customAttrSetter
    ) {
        $this->fillEmptyData = $fillEmptyData;
        $this->addressValidator = $addressValidator;
        $this->customAttrSetter = $customAttrSetter;
    }

    public function beforeExecute(
        SetBillingAddressOnCart $subject,
        ContextInterface $context,
        CartInterface $cart,
        array $billingAddressInput
    ): array {
        $billingAddress = &$billingAddressInput['address'];
        if (empty($billingAddress)) {
            return [$context, $cart, $billingAddressInput];
        }

        $billingAddress = $this->customAttrSetter->execute($billingAddress);
        $this->addressValidator->validate($billingAddress);
        $billingAddress = $this->fillEmptyData->execute($billingAddress);

        return [$context, $cart, $billingAddressInput];
    }
}
