<?php
declare(strict_types=1);

namespace Amasty\CheckoutGraphQl\Plugin\QuoteGraphQl\Model\Cart\SetShippingAddressesOnCart;

use Amasty\CheckoutGraphQl\Model\Utils\Address\CustomAttributesSetter;
use Amasty\CheckoutGraphQl\Model\Utils\Address\FillEmptyData;
use Amasty\CheckoutGraphQl\Model\Utils\Address\Validator;
use Magento\GraphQl\Model\Query\ContextInterface;
use Magento\Quote\Api\Data\CartInterface;
use Magento\QuoteGraphQl\Model\Cart\SetShippingAddressesOnCart;

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
        SetShippingAddressesOnCart $subject,
        ContextInterface $context,
        CartInterface $cart,
        array $shippingAddressesInput
    ): array {
        $shippingAddress = &$shippingAddressesInput[0]['address'];
        if (empty($shippingAddress)) {
            return [$context, $cart, $shippingAddressesInput];
        }

        $shippingAddress = $this->customAttrSetter->execute($shippingAddress);
        $this->addressValidator->validate($shippingAddress);
        $shippingAddress = $this->fillEmptyData->execute($shippingAddress);

        return [$context, $cart, $shippingAddressesInput];
    }
}
