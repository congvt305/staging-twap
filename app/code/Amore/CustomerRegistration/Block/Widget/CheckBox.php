<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Amore\CustomerRegistration\Block\Widget;

use Magento\Customer\Api\CustomerMetadataInterface;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Customer\Api\Data\OptionInterface;

/**
 * Block to render customer's Checkbox attribute
 *
 * @SuppressWarnings(PHPMD.DepthOfInheritance)
 */
class CheckBox extends \Magento\Customer\Block\Widget\AbstractWidget
{
    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $_customerSession;

    /**
     * @var CustomerRepositoryInterface
     */
    protected $customerRepository;

    /**
     * Create an instance of the Checkbox widget
     *
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Customer\Helper\Address $addressHelper
     * @param CustomerMetadataInterface $customerMetadata
     * @param CustomerRepositoryInterface $customerRepository
     * @param \Magento\Customer\Model\Session $customerSession
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Customer\Helper\Address $addressHelper,
        CustomerMetadataInterface $customerMetadata,
        CustomerRepositoryInterface $customerRepository,
        \Magento\Customer\Model\Session $customerSession,
        array $data = []
    ) {
        $this->_customerSession = $customerSession;
        $this->customerRepository = $customerRepository;
        parent::__construct($context, $addressHelper, $customerMetadata, $data);
        $this->_isScopePrivate = true;
    }

    /**
     * Initialize block
     *
     * @return void
     */
    public function _construct()
    {
        parent::_construct();
        $this->setTemplate('Amore_CustomerRegistration::widget/checkbox.phtml');
    }

    /**
     * Check if Checkbox attribute enabled in system
     *
     * @return bool
     */
    public function isEnabled()
    {
        return $this->_getAttribute($this->getAttributeCode()) ? (bool)$this->_getAttribute($this->getAttributeCode())->isVisible() : false;
    }

    /**
     * Check if Checkbox attribute marked as required
     *
     * @return bool
     */
    public function isRequired()
    {
        return $this->_getAttribute($this->getAttributeCode()) ? (bool)$this->_getAttribute($this->getAttributeCode())->isRequired() : false;
    }

    /**
     * Retrieve store attribute label
     *
     * @param string $attributeCode
     *
     * @return string
     */
    public function getStoreLabel($attributeCode)
    {
        $attribute = $this->_getAttribute($attributeCode);
        return $attribute ? __($attribute->getStoreLabel()) : '';
    }

    /**
     * Get current customer from session
     *
     * @return CustomerInterface
     */
    public function getCustomer()
    {
        return $this->customerRepository->getById($this->_customerSession->getCustomerId());
    }

    /**
     * Returns options from Checkbox attribute
     *
     * @return OptionInterface[]
     */
    public function getOptions()
    {
        return $this->_getAttribute($this->getAttributeCode())->getOptions();
    }
}
