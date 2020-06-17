<?php
/**
 * Created by PhpStorm.
 * User: abbas
 * Date: 2020-05-21
 * Time: 오후 5:09
 *
 */

namespace Amore\CustomerRegistration\Block\Widget;

use Magento\Customer\Api\CustomerMetadataInterface;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Customer\Api\Data\OptionInterface;
use Magento\Customer\Helper\Address;
use Magento\Customer\Model\Session;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\View\Element\Template\Context;

/**
 * Block to render customer's Checkbox attribute
 *
 */
class CheckBox extends \Magento\Customer\Block\Widget\AbstractWidget
{
    /**
     * To save customer session
     *
     * @var Session $customerSession customer session
     */
    protected $customerSession;

    /**
     * To get customer session values
     *
     * @var CustomerRepositoryInterface
     */
    protected $customerRepository;

    /**
     * Create an instance of the Checkbox widget
     *
     * @param Context                     $context            Context
     * @param Address                     $addressHelper      address helper
     * @param CustomerMetadataInterface   $customerMetadata   Customer Meta data
     * @param CustomerRepositoryInterface $customerRepository Customer repository
     * @param Session                     $customerSession    Customer Session
     * @param array                       $data               Data
     */
    public function __construct(
        Context $context,
        Address $addressHelper,
        CustomerMetadataInterface $customerMetadata,
        CustomerRepositoryInterface $customerRepository,
        Session $customerSession,
        array $data = []
    ) {
        $this->customerSession = $customerSession;
        $this->customerRepository = $customerRepository;
        parent::__construct($context, $addressHelper, $customerMetadata, $data);
        $this->_isScopePrivate = true;
    }

    /**
     * Initialize block
     * Initialize the block
     *
     * @return void
     *
     *  phpcs:disable Magento2.CodeAnalysis.EmptyBlock
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
        return $this->_getAttribute($this->getAttributeCode())
            ? (bool)$this->_getAttribute($this->getAttributeCode())->isVisible()
            : false;
    }

    /**
     * Check if Checkbox attribute marked as required
     *
     * @return bool
     */
    public function isRequired()
    {
        return $this->_getAttribute($this->getAttributeCode())
            ? (bool)$this->_getAttribute($this->getAttributeCode())->isRequired()
            : false;
    }

    /**
     * Retrieve store attribute label
     *
     * @param string $attributeCode attribute code
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
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function getCustomer()
    {
        return $this->customerRepository->getById(
            $this->customerSession->getCustomerId()
        );
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

    /**
     * Get the attribute value
     *
     * @return mixed
     */
    public function getFormValue()
    {
        $formValue = $this->getData('form_value');
        return $formValue == 1 ? $formValue:0;
    }
}
