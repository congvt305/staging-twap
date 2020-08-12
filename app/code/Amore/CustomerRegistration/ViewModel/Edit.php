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
use Magento\Directory\Model\RegionFactory;
use Magento\Directory\Model\ResourceModel\Region as RegionResourceModel;
use Amore\CustomerRegistration\Helper\Data;

/**
 * PLEASE ENTER ONE LINE SHORT DESCRIPTION OF CLASS
 * Class Edit
 * @package Amore\CustomerRegistration\ViewModel
 */
class Edit extends \Magento\Directory\Block\Data implements ArgumentInterface
{
    /**
     * @var RegionFactory
     */
    private $regionFactory;
    /**
     * @var RegionResourceModel
     */
    private $regionResourceModel;
    /**
     * @var \Eguana\Directory\Helper\Data
     */
    private $cityHelper;
    /**
     * @var Data
     */
    private $configHelper;

    public function __construct(
        Data $configHelper,
        RegionFactory $regionFactory,
        RegionResourceModel $regionResourceModel,
        \Eguana\Directory\Helper\Data $cityHelper,
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
        $this->regionFactory = $regionFactory;
        $this->regionResourceModel = $regionResourceModel;
        $this->cityHelper = $cityHelper;
        $this->configHelper = $configHelper;
    }

    /**
     * SHORT DESCRIPTION
     * LONG DESCRIPTION LINE BY LINE
     * @param \Magento\Customer\Api\Data\CustomerInterface $customer
     * @return int
     */
    public function getCustomerDMStateRegionId($customer)
    {
        $regionId = 1;
        if ($dmState = $customer->getCustomAttribute('dm_state')) {
            /** @var \Magento\Directory\Model\Region $regionObject */
            $regionObject = $this->getRegionObject($dmState->getValue());
            if ($regionObject) {
                $regionId = $regionObject->getRegionId();
            }
        }
        return $regionId;
    }

    /**
     * SHORT DESCRIPTION
     * LONG DESCRIPTION LINE BY LINE
     * @param \Magento\Customer\Api\Data\CustomerInterface $customer
     * @return int
     */
    public function getCustomerDMCityId($customer)
    {
        $cityId = 1;
        if ($dmState = $customer->getCustomAttribute('dm_state')) {
            /** @var \Magento\Directory\Model\Region $regionObject */
            $regionObject = $this->getRegionObject($dmState->getValue());
            if ($regionObject) {
                $regionId = $regionObject->getRegionId();
                $dmCity = $customer->getCustomAttribute('dm_city');
                if ($dmCity && $regionId) {
                    $cities = $this->cityHelper->getCityData();
                    $regionCities = $cities[$regionId];
                    $dmCityName = $dmCity->getValue();
                    foreach ($regionCities as $regionCityId => $regionCity) {
                        if ($regionCity['name'] == $dmCityName) {
                            $cityId = $regionCity['code'];
                            break;
                        }
                    }
                }
            }
        }
        return $cityId;
    }

    public function getCustomAttributeValue($customer, $attributeCode)
    {
        $attributeValue = '';
        if ($attribute = $customer->getCustomAttribute($attributeCode)) {
            $attributeValue = $attribute->getValue();
        }
        return $attributeValue;
    }

    public function getCustomerDMStateValue($customer)
    {
        $dmState = '';
        if ($dmState = $customer->getCustomAttribute('dm_state')) {
            $dmState = $dmState->getValue();
        }
        return $dmState;
    }

    private function getRegionObject($regionName)
    {
        /** @var \Magento\Directory\Model\Region $region */
        $region = $this->regionFactory->create();
        try {
            $this->regionResourceModel->load($region, $regionName, 'default_name');
        } catch (\Exception $e) {
            $message = $e->getMessage();
        }
        return $region;
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