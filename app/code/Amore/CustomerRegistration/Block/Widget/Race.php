<?php

declare(strict_types=1);

namespace Amore\CustomerRegistration\Block\Widget;

use Magento\Customer\Api\CustomerMetadataInterface;
use Magento\Customer\Api\Data\OptionInterface;

/**
 * Block to render customer's race attribute
 */
class Race extends \Magento\Customer\Block\Widget\AbstractWidget
{
    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $_customerSession;

    /**
     * Create an instance of the Race widget
     *
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Customer\Helper\Address $addressHelper
     * @param CustomerMetadataInterface $customerMetadata
     * @param \Magento\Customer\Model\Session $customerSession
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Customer\Helper\Address $addressHelper,
        CustomerMetadataInterface $customerMetadata,
        \Magento\Customer\Model\Session $customerSession,
        array $data = []
    ) {
        $this->_customerSession = $customerSession;
        parent::__construct($context, $addressHelper, $customerMetadata, $data);
    }

    /**
     * Initialize block
     *
     * @return void
     */
    public function _construct()
    {
        parent::_construct();
        $this->setTemplate('Amore_CustomerRegistration::widget/race.phtml');
    }

    /**
     * Check if race attribute enabled in system
     *
     * @return bool
     */
    public function isEnabled(): bool
    {
        return $this->_getAttribute('race') && (bool)$this->_getAttribute('race')->isVisible();
    }

    /**
     * Check if race attribute marked as required
     *
     * @return bool
     */
    public function isRequired(): bool
    {
        return $this->_getAttribute('race') && (bool)$this->_getAttribute('race')->isRequired();
    }

    /**
     * Retrieve store attribute label
     *
     * @return string
     */
    public function getStoreLabel()
    {
        $attribute = $this->_getAttribute('race');
        return $attribute ? __($attribute->getStoreLabel()) : '';
    }

    /**
     * Returns options from race attribute
     *
     * @return OptionInterface[]
     */
    public function getRaceOptions(): array
    {
        return $this->_getAttribute('race')->getOptions();
    }
}
