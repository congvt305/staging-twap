<?php
declare(strict_types=1);

namespace Amasty\CheckoutGraphQl\Plugin\CustomerGraphQl\Model\Customer\Address\PopulateCustomerAddressFromInput;

use Amasty\CheckoutGraphQl\Model\Utils\Address\CustomAttributesSetter;
use Amasty\CheckoutGraphQl\Model\Utils\Address\FillEmptyData;
use Amasty\CheckoutGraphQl\Model\Utils\Address\Validator;
use Magento\Customer\Api\Data\AddressInterface;
use Magento\CustomerGraphQl\Model\Customer\Address\PopulateCustomerAddressFromInput;

class SetCustomAttributesToAddress
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
        PopulateCustomerAddressFromInput $subject,
        AddressInterface $address,
        array $addressData
    ) {
        if (empty($addressData)) {
            return [$address, $addressData];
        }

        $addressData = $this->customAttrSetter->execute($addressData);
        $this->addressValidator->validate($addressData);
        $addressData = $this->fillEmptyData->execute($addressData);

        return [$address, $addressData];
    }
}
