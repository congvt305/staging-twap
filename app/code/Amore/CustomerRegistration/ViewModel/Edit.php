<?php
/**
 * @author Eguana Team
 * @copyriht Copyright (c) 2020 Eguana {http://eguanacommerce.com}
 * Created by PhpStorm
 * User: abbas
 * Date: 20. 7. 27
 * Time: 오후 1:55
 */

namespace Amore\CustomerRegistration\ViewModel;

use Magento\Framework\View\Element\Block\ArgumentInterface;
use Amore\CustomerRegistration\Helper\Data;

/**
 * To provide the differnet functions during account update
 * Class Edit
 * @package Amore\CustomerRegistration\ViewModel
 */
class Edit extends \Magento\Directory\Block\Data implements ArgumentInterface
{
    /**
     * @var Data
     */
    private $configHelper;

    public function __construct(
        Data $configHelper,
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Directory\Helper\Data $directoryHelper,
        \Magento\Framework\Json\EncoderInterface $jsonEncoder,
        \Magento\Framework\App\Cache\Type\Config $configCacheType,
        \Magento\Directory\Model\ResourceModel\Region\CollectionFactory $regionCollectionFactory,
        \Magento\Directory\Model\ResourceModel\Country\CollectionFactory $countryCollectionFactory,
        array $data = []
    ) {
        parent::__construct(
            $context,
            $directoryHelper,
            $jsonEncoder,
            $configCacheType,
            $regionCollectionFactory,
            $countryCollectionFactory,
            $data
        );
        $this->configHelper = $configHelper;
    }

    public function getCustomAttributeValue($customer, $attributeCode)
    {
        $attributeValue = '';
        if ($attribute = $customer->getCustomAttribute($attributeCode)) {
            $attributeValue = $attribute->getValue();
        }
        return $attributeValue;
    }

    /**
     * Get minimum mobile number digits
     * Get minimum mobile number digits set in setting from admin setting
     *
     * @return null|int
     */
    public function getMinimumMobileNumberDigits()
    {
        return $this->configHelper->getMinimumMobileNumberDigits();
    }

    /**
     * Get maximum mobile number digits
     * Get maximum mobile number digits set in setting from admin setting
     *
     * @return null|int
     */
    public function getMaximumMobileNumberDigits()
    {
        return $this->configHelper->getMaximumMobileNumberDigits();
    }

    /**
     * Get CODE EXPIRATION TIME IN MINUTES
     * Get code expiration time in minutes set in setting from admin setting
     *
     * @return null|int
     */
    public function getCodeExpirationTimeInMinutes()
    {
        return $this->configHelper->getCodeExpirationTimeInMinutes();
    }

    /**
     * Retrieve SMS code action url and set "secure" param to avoid confirm
     * message when we submit form from secure page to unsecure
     *
     * @return string
     */
    public function getSendCodeUrl()
    {
        return $this->getUrl(
            'customerregistration/verification/code',
            ['_secure' => true]
        );
    }

    /**
     * Retrieve SMS code action url and set "secure" param to avoid confirm
     * message when we submit form from secure page to unsecure
     *
     * @return string
     */
    public function getVerifyCodeUrl()
    {
        return $this->getUrl(
            'customerregistration/verification/verify',
            ['_secure' => true]
        );
    }

    /**
     * Retrieve POS Verification URL
     *
     * @return string
     */
    public function getPOSVerificationUrl()
    {
        return $this->getUrl(
            'customerregistration/verification/pos',
            ['_secure' => true]
        );
    }

    /**
     * Retrieve SMS code action url and set "secure" param to avoid confirm
     * message when we submit form from secure page to unsecure
     *
     * @return string
     */
    public function getAddAddressUrl()
    {
        return $this->getUrl(
            'customer/address',
            ['_secure' => true]
        );
    }

}