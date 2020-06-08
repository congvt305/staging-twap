<?php
/**
 * Created by PhpStorm.
 * User: abbas
 * Date: 2020-05-21
 * Time: 오후 5:09
 */

namespace Amore\CustomerRegistration\Block\Widget;

use Magento\Customer\Api\AddressMetadataInterface;
use Magento\Customer\Api\CustomerMetadataInterface;
use Magento\Customer\Block\Widget\AbstractWidget;
use Magento\Customer\Helper\Address as AddressHelper;
use Magento\Customer\Model\Options;
use Magento\Framework\View\Element\Template\Context;

/**
 * Block to render custom attribute
 *
 * @SuppressWarnings(PHPMD.DepthOfInheritance)
 */

class TextField extends AbstractWidget
{

    /**
     * Address meta data
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
     * Constructor
     *
     * @param Context                   $context          Context
     * @param AddressHelper             $addressHelper    Address Helper
     * @param CustomerMetadataInterface $customerMetadata Customer Meta Data
     * @param Options                   $options          Options
     * @param AddressMetadataInterface  $addressMetadata  Address Meta Data
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
     * Constructor
     *
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
    public function showAttribute()
    {
        return $this->_isAttributeVisible($this->getAttributeCode());
    }

    /**
     * Retrieve store attribute label
     *
     * @return string
     */
    public function getStoreLabel()
    {
        $attribute = $this->_getAttribute($this->getAttributeCode());
        return $attribute ? __($attribute->getStoreLabel()) : '';
    }

    /**
     * Attribute is visible or not
     *
     * @param string $attributeCode Attribute Code
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
        return $this->_getAttribute($this->getAttributeCode())
            ? (bool)$this->_getAttribute($this->getAttributeCode())->isVisible()
            : false;
    }

    /**
     * Check if company attribute marked as required
     *
     * @return bool
     */
    public function isRequired()
    {
        return $this->_getAttribute($this->getAttributeCode())
            ? (bool)$this->_getAttribute($this->getAttributeCode())
            ->isRequired() : false;
    }

    /**
     * Get frontend classes of the attribute
     *
     * @return string
     */
    public function getFrontendClasses()
    {
        return $this->_getAttribute($this->getAttributeCode())
            ? (string)$this->_getAttribute($this->getAttributeCode())
            ->getFrontendClass() : '';
    }

    /**
     * Get the attribute value
     *
     * @return mixed
     */
    public function getAttributeValue()
    {
        return $this->getData($this->getAttributeCode());
    }
}
