<?php
/**
 * Created by PhpStorm.
 * User: abbas
 * Date: 2020-05-21
 * Time: 오후 5:09
 *
 */

namespace Amore\CustomerRegistration\Block\Widget;

use Magento\Customer\Api\AddressMetadataInterface;
use Magento\Customer\Api\CustomerMetadataInterface;
use Magento\Customer\Api\Data\AttributeMetadataInterface;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Customer\Block\Widget\AbstractWidget;
use Magento\Customer\Helper\Address as AddressHelper;
use Magento\Customer\Model\Options;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Phrase;
use Magento\Framework\View\Element\Template\Context;

/**
 * Widget for showing mobile number.
 *
 * @method CustomerInterface getObject()
 * @method Name setObject(CustomerInterface $customer)
 *
 * @SuppressWarnings(PHPMD.DepthOfInheritance)
 */
class MobileNumber extends AbstractWidget
{

    /**
     * The attribute code
     */
    const ATTRIBUTE_CODE = 'mobile_number';

    /**
     * Address Meta Data
     *
     * @var AddressMetadataInterface
     */
    protected $addressMetadata;

    /**
     * Options
     *
     * @var Options
     */
    protected $options;

    /**
     * Class constructor
     *
     * @param Context                   $context          Context
     * @param AddressHelper             $addressHelper    Address Helper
     * @param CustomerMetadataInterface $customerMetadata Customer Meta Data
     * @param Options                   $options          Options
     * @param AddressMetadataInterface  $addressMetadata  Address Meta data
     * @param array                     $data             Data
     */
    public function __construct(
        Context $context,
        AddressHelper $addressHelper,
        CustomerMetadataInterface $customerMetadata,
        Options $options,
        AddressMetadataInterface $addressMetadata,
        array $data = []
    ) {
        $this->options = $options;
        parent::__construct($context, $addressHelper, $customerMetadata, $data);
        $this->addressMetadata = $addressMetadata;
        $this->_isScopePrivate = true;
    }

    /**
     * Class Constructor
     *
     * @return void
     */
    public function _construct()
    {
        parent::_construct();

        // default template location
        $this->setTemplate('Amore_CustomerRegistration::widget/mobilenumber.phtml');
    }

    /**
     * Can show config value
     *
     * @param string $key Key
     *
     * @return bool
     */
    protected function _showConfig($key)
    {
        return (bool)$this->getConfig($key);
    }

    /**
     * Can show prefix
     *
     * @return bool
     */
    public function showMobileNumber()
    {
        return $this->_isAttributeVisible(self::ATTRIBUTE_CODE);
    }

    /**
     * Get attribute
     *
     * @param string $attributeCode Attribute Code
     *
     * @return AttributeMetadataInterface|null
     * @throws LocalizedException
     */
    protected function _getAttribute($attributeCode)
    {
        if ($this->getForceUseCustomerAttributes()
            || $this->getObject() instanceof CustomerInterface
        ) {

            return parent::_getAttribute($attributeCode);
        }

        try {
            $attribute = $this->addressMetadata
                ->getAttributeMetadata($attributeCode);
        } catch (NoSuchEntityException $e) {
            return null;
        }

        if ($this->getForceUseCustomerRequiredAttributes()
            && $attribute && !$attribute->isRequired()
        ) {
            $customerAttribute = parent::_getAttribute($attributeCode);
            if ($customerAttribute && $customerAttribute->isRequired()) {
                $attribute = $customerAttribute;
            }
        }

        return $attribute;
    }

    /**
     * Retrieve store attribute label
     *
     * @param string $attributeCode
     *
     * @return string
     */

    /**
     * Retrieve store attribute label
     *
     * @param string $attributeCode Attribute Code
     *
     * @return Phrase|string
     * @throws LocalizedException
     */
    public function getStoreLabel($attributeCode)
    {
        $attribute = $this->_getAttribute($attributeCode);
        return $attribute ? __($attribute->getStoreLabel()) : '';
    }

    /**
     * Get string with frontend validation classes for attribute
     *
     * @param string $attributeCode Attribute Code
     *
     * @return string
     * @throws LocalizedException
     */
    public function getAttributeValidationClass($attributeCode)
    {
        return $this->_addressHelper->getAttributeValidationClass($attributeCode);
    }

    /**
     * Attribute is visible or not

     * @param string $attributeCode Attribute Code
     *
     * @return bool
     * @throws LocalizedException
     */
    private function _isAttributeVisible($attributeCode)
    {
        $attributeMetadata = $this->_getAttribute($attributeCode);
        return $attributeMetadata ? (bool)$attributeMetadata->isVisible() : false;
    }

    /**
     * Check if company attribute enabled in system
     *
     * @return bool
     * @throws LocalizedException
     */
    public function isEnabled()
    {
        return $this->_getAttribute(self::ATTRIBUTE_CODE)
            ? (bool)$this->_getAttribute(self::ATTRIBUTE_CODE)->isVisible()
            : false;
    }

    /**
     * Check if attribute marked as required
     *
     * @return bool
     * @throws LocalizedException
     */
    public function isRequired()
    {
        return $this->_getAttribute(self::ATTRIBUTE_CODE)
            ? (bool)$this->_getAttribute(self::ATTRIBUTE_CODE)
            ->isRequired() : false;
    }
}
