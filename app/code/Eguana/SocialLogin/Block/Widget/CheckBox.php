<?php
/**
 * @author Eguana Team
 * @copyright Copyright (c) 2020 Eguana {http://eguanacommerce.com}
 * Created by PhpStorm
 * User: umer
 * Date: 18/11/20
 * Time: 5:46 PM
 */
namespace Eguana\SocialLogin\Block\Widget;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\View\Element\Template\Context;
use Eguana\SocialLogin\Helper\Data;
use Magento\Customer\Helper\Address;
use Magento\Customer\Api\CustomerMetadataInterface;

/**
 * Block to render customer's Checkbox attribute
 *
 */
class CheckBox extends \Magento\Customer\Block\Widget\AbstractWidget
{

    /**
     * @var Data
     */
    private $helper;

    /**
     * Create an instance of the Checkbox widget.
     * @param Context $context
     * @param Address $addressHelper
     * @param CustomerMetadataInterface $customerMetadata
     * @param Data $helper
     * @param array $data
     */
    public function __construct(
        Context $context,
        Address $addressHelper,
        CustomerMetadataInterface $customerMetadata,
        Data $helper,
        array $data = []
    ) {
        parent::__construct($context, $addressHelper, $customerMetadata, $data);
        $this->_isScopePrivate = true;
        $this->helper = $helper;
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
        $this->setTemplate('Eguana_SocialLogin::widget/checkbox.phtml');
    }

    /**
     * Get LINE Agreement Text
     * @return mixed
     */
    public function getAgreementText()
    {
        return $this->helper->getAgreementText();
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
}
