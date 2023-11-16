<?php

namespace Amore\PointsIntegration\Model;

use Amore\CustomerRegistration\Helper\Data;
use Amore\CustomerRegistration\Model\POSLogger;
use Amore\CustomerRegistration\Model\POSSystem;
use Magento\Customer\Model\CustomerFactory;
use Magento\Customer\Model\ResourceModel\Customer\CollectionFactory;
use Magento\Directory\Model\Region;
use Magento\Directory\Model\RegionFactory;
use Magento\Directory\Model\ResourceModel\Region as RegionResourceModel;
use Eguana\Directory\Helper\Data as CityHelper;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Store\Model\WebsiteFactory;

/**
 * Class PosCustomerData
 * @package Amore\PointsIntegration\Model
 */
class PosCustomerData
{
    /**
     * @param RequestInterface $request
     * @param RegionFactory $regionFactory
     * @param RegionResourceModel $regionResourceModel
     * @param CityHelper $cityHelper
     * @param Data $config
     * @param POSLogger $logger
     * @param POSSystem $posSystem
     * @param ManagerInterface $eventManager
     * @param CustomerFactory $customerFactory
     * @param CollectionFactory $customerCollectionFactory
     * @param WebsiteFactory $websiteFactory
     */
    public function __construct(
        protected RequestInterface $request,
        protected RegionFactory $regionFactory,
        protected RegionResourceModel $regionResourceModel,
        protected CityHelper $cityHelper,
        protected Data $config,
        protected POSLogger $logger,
        protected POSSystem $posSystem,
        protected ManagerInterface $eventManager,
        protected CustomerFactory $customerFactory,
        protected CollectionFactory $customerCollectionFactory,
        protected WebsiteFactory $websiteFactory
    ) { }

    /**
     * @param string $email
     * @return array
     * @throws LocalizedException
     */
    public function sendJoinMemberByIntegrationNumber(string $integrationNumber) {
        $result = [];
        $customerCollection = $this->customerCollectionFactory->create()
            ->addAttributeToFilter('integration_number', $integrationNumber)
            ->load();
        foreach ($customerCollection as $data) {
            $customer = $this->customerFactory->create()->load($data->getId());
            $result[] = $this->sendJoinMember($customer);
        }

        return $result;
    }



    /**
     * @param string $email
     * @return array
     * @throws LocalizedException
     */
    public function sendJoinMemberByEmail(string $email) {
        $websiteIds = $this->getWebsiteIds();
        $result = [];
        foreach ($websiteIds as $websiteId) {
            $customer = $this->customerFactory->create()
                ->setWebsiteId($websiteId)->loadByEmail($email);
            if ($customer->getId()) {
                $result[] = $this->sendJoinMember($customer);
            }
        }

        return $result;
    }

    /**
     * @param $customer
     * @return string|void
     */
    public function sendJoinMember($customer) {
        $apiParameters = $this->getApiParameters($customer, 'register');

        return $this->posSystem->syncMember($apiParameters, $customer->getStoreId());
    }

    /**
     * @param $customer
     * @param $action
     * @return array|void
     */
    public function getApiParameters($customer, string $action)
    {
        try {
            $parameters = [];
            /**
             * @var \Magento\Customer\Model\Customer $customer
             */
            $customerDataModel = $customer->getDataModel();
            $parameters['cstmIntgSeq'] = $customerDataModel->getCustomAttribute('integration_number')->getValue();
            $parameters['if_flag'] = $action == 'register' ? 'I' : 'U';
            $parameters['firstName'] = trim($customer->getFirstname());
            $parameters['lastName'] = trim($customer->getLastname());
            $parameters['birthDay'] = $customer->getDob() ? preg_replace('/\D/', '', $customer->getDob()) : '';
            $parameters['mobileNo'] = $customerDataModel->getCustomAttribute('mobile_number')->getValue();
            $parameters['email'] = trim($customer->getEmail());
            $parameters['sex'] = $customer->getGender() == '1' ? 'M' : 'F';
            if ($customerDataModel->getCustomAttribute('ba_code')) {
                $baCode = $this->posSystem->checkBACodePrefix(
                    $customerDataModel->getCustomAttribute('ba_code')->getValue()
                );
                $parameters['empID'] = $baCode;
            } else {
                $parameters['empID'] = '';
            }
            $subscribeStatus = 'N';
            $dmStatus = '';
            if ($customerDataModel->getCustomAttribute('call_subscription_status')) {
                $subscribeData = $customerDataModel->getCustomAttribute('call_subscription_status')->getValue();
                if ($subscribeData == 1) {
                    $subscribeStatus = 'Y';
                    $dmStatus = 'Y';
                } else {
                    $dmStatus = 'N';
                }
            }
            $parameters['callYN'] = $subscribeStatus;
            $parameters['emailYN'] = $subscribeStatus;
            $parameters['smsYN'] = $subscribeStatus;
            $parameters['dmYN'] = $dmStatus;
            $defaultShippingAddressId = $customer->getDefaultShipping();
            $parameters['homeZip'] = '';
            $addresses = $customer->getAddresses();
            $defaultShippingAddress = null;
            foreach ($addresses as $address) {
                if ($address->getId() == $defaultShippingAddressId) {
                    $defaultShippingAddress = $address;
                    break;
                }
            }
            $regionName = $defaultShippingAddress->getRegion() ?? '';
            if ($regionName) {
                $regionObject = $this->getRegionObject($regionName, $defaultShippingAddress->getRegionId());
                $parameters['homeCity'] = $regionObject->getCode() ? $regionObject->getCode() : '';
            } else {
                $parameters['dmYN'] = '';
            }
            $addressParameters = $this->getAddressParameters($defaultShippingAddress);
            $parameters = array_merge($parameters, $addressParameters);
            $countryPosCode = $customerDataModel->getCustomAttribute('country_pos_code') ? $customerDataModel->getCustomAttribute('country_pos_code')->getValue() : '';
            $countryId = $customerDataModel->getCustomAttribute('country_pos_code')->getValue() ?? ($countryPosCode ?: 'HK');
            $parameters['countryCode'] = $this->config->getCountryPosCodeById($countryId);
            $parameters['statusCD'] = '01';
            $parameters['dmYN'] = $parameters['smsYN'];
            $checkCustomerOffOn = $this->posSystem->checkCusOffOn(trim($parameters['firstName']), trim($parameters['lastName']), trim($parameters['mobileNo']), trim($parameters['countryCode']), $customer->getStoreId());
            if ($checkCustomerOffOn == 1) {
                $parameters['if_flag'] = 'U';
            }

            return $parameters;

        } catch (\Exception $exception) {
            $this->logger->addExceptionMessage($exception->getMessage());
            $this->logger->addExceptionMessage('Fail to get API Parameter: ' . json_encode($parameters));
        }
    }

    /**
     * @param $address
     * @return array
     */
    private function getAddressParameters($address)
    {
        $parameters = [];
        $parameters['homeCity'] = $address->getRegionCode();
        foreach ($address->getStreet() as $key => $street) {
            $parameters['homeAddr' . $key + 1] = $street;
        }
        $parameters['homeZip'] = $address->getPostcode();
        $cityId = $address->getCustomAttribute('city_id') ? $address->getCustomAttribute('city_id')->getValue() : '';
        $cityName = $address->getCity();
        $parameters['homeState'] = '';

        if ($cityId || $cityName) {
            $cities = $this->cityHelper->getCityData();
            if (isset($cities[$address->getRegionId()][$cityId]['pos_code'])) {
                $parameters['homeState'] = $cities[$address->getRegionId()][$cityId]['pos_code'];
                return $parameters;
            }
            $regionCities = $cities[$address->getRegionId()];
            foreach ($regionCities as $regionCity) {
                if ($regionCity['name'] == $cityName) {
                    $parameters['homeState'] = $regionCity['pos_code'];
                    break;
                }
            }
        }

        return $parameters;
    }

    /**
     * @param $regionName
     * @param $regionId
     * @return Region
     */
    private function getRegionObject($regionName, $regionId)
    {
        $region = $this->regionFactory->create();
        try {
            if (!empty($regionId)) {
                $this->regionResourceModel->load($region, $regionId, 'region_id');
            } else {
                $this->regionResourceModel->load($region, $regionName, 'default_name');
            }
        } catch (\Exception $e) {
            $this->logger->addExceptionMessage($e->getMessage());
        }

        return $region;
    }

    /**
     * @return array
     */
    public function getWebsiteIds()
    {
        $websiteIds = [];
        $collection = $this->websiteFactory->create()->getCollection();
        foreach ($collection as $website) {
            $websiteIds[] = $website->getId();
        }

        return $websiteIds;
    }
}
