<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Amore\CustomerRegistration\Block\Widget;

use Magento\Customer\Api\AddressMetadataInterface;
use Magento\Customer\Api\CustomerMetadataInterface;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Customer\Helper\Address as AddressHelper;
use Magento\Customer\Model\Options;
use Magento\Framework\View\Element\Template\Context;

/**
 * Widget for showing custom attributes.
 *
 * @method CustomerInterface getObject()
 * @method Name setObject(CustomerInterface $customer)
 *
 * @SuppressWarnings(PHPMD.DepthOfInheritance)
 */
class TextField extends \Magento\Customer\Block\Widget\AbstractWidget
{

    /**
     * @var AddressMetadataInterface
     */
    protected $addressMetadata;

    /**
     * @var Options
     */
    protected $options;

    /**
     * @param Context $context
     * @param AddressHelper $addressHelper
     * @param CustomerMetadataInterface $customerMetadata
     * @param Options $options
     * @param AddressMetadataInterface $addressMetadata
     * @param array $data
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
     * @return void
     */
    public function _construct()
    {
        parent::_construct();

        // default template location
        $this->setTemplate('Amore_CustomerRegistration::widget/textfield.phtml');
    }

    /**
     * Can show config value
     *
     * @param string $key
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
    public function showAttribute()
    {
        return $this->_isAttributeVisible($this->getAttributeCode());
    }


    /**
     * Retrieve store attribute label
     *
     *
     * @return string
     */
    public function getStoreLabel()
    {
        $attribute = $this->_getAttribute($this->getAttributeCode());
        return $attribute ? __($attribute->getStoreLabel()) : '';
    }

    /**
     * @param string $attributeCode
     *
     * @return bool
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
     */
    public function isEnabled()
    {
        return $this->_getAttribute($this->getAttributeCode()) ? (bool)$this->_getAttribute($this->getAttributeCode())->isVisible(
        ) : false;
    }

    /**
     * Check if company attribute marked as required
     *
     * @return bool
     */
    public function isRequired()
    {
        return $this->_getAttribute($this->getAttributeCode()) ? (bool)$this->_getAttribute($this->getAttributeCode())
            ->isRequired() : false;
    }

    public function getFrontendClasses()
    {
        return $this->_getAttribute($this->getAttributeCode()) ? (string)$this->_getAttribute($this->getAttributeCode())
            ->getFrontendClass() : '';
    }

    public function getAttributeValue()
    {
        return $this->getData($this->getAttributeCode());
    }
}
