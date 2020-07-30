<?php
/**
 * @author Eguana Team
 * @copyriht Copyright (c) 2020 Eguana {http://eguanacommerce.com}
 * Created by PhpStorm
 * User: abbas
 * Date: 20. 7. 10
 * Time: 오후 5:11
 */

namespace Amore\CustomerRegistration\Observer\Customer;

use Magento\Framework\Event\ObserverInterface;
use Amore\CustomerRegistration\Model\POSSystem;
use \Magento\Customer\Model\Data\Customer;
use Amore\CustomerRegistration\Model\Sequence;
use Amore\CustomerRegistration\Helper\Data;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Amore\CustomerRegistration\Model\POSLogger;
use Magento\Newsletter\Model\SubscriberFactory;
use Magento\Newsletter\Model\Subscriber;
use Magento\Directory\Model\RegionFactory;
use Magento\Directory\Model\ResourceModel\Region as RegionResourceModel;

/**
 * PLEASE ENTER ONE LINE SHORT DESCRIPTION OF CLASS
 * Class SaveSuccess
 * @package Amore\CustomerRegistration\Observer\Customer
 */
class SaveSuccess implements ObserverInterface
{
    /**
     * @var \Amore\CustomerRegistration\Model\Sequence
     */
    private $sequence;

    /**
     * @var POSSystem
     */
    private $POSSystem;

    /**
     * @var Data
     */
    private $config;

    /**
     * @var CustomerRepositoryInterface
     */
    private $customerRepository;

    /**
     * @var POSLogger
     */
    private $logger;

    /**
     * @var SubscriberFactory
     */
    private $subscriberFactory;
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

    public function __construct(
        RegionFactory $regionFactory,
        \Eguana\Directory\Helper\Data $cityHelper,
        RegionResourceModel $regionResourceModel,
        Sequence $sequence,
        Data $config,
        CustomerRepositoryInterface $customerRepository,
        SubscriberFactory $subscriberFactory,
        POSLogger $logger,
        POSSystem $POSSystem
    ) {
        $this->sequence = $sequence;
        $this->POSSystem = $POSSystem;
        $this->subscriberFactory = $subscriberFactory;
        $this->config = $config;
        $this->customerRepository = $customerRepository;
        $this->logger = $logger;
        $this->regionFactory = $regionFactory;
        $this->regionResourceModel = $regionResourceModel;
        $this->cityHelper = $cityHelper;
    }

    /**
     * Observer called on successfull customer registration
     *
     * @param \Magento\Framework\Event\Observer $observer
     */
    public function execute(
        \Magento\Framework\Event\Observer $observer
    ) {
        try {
            /**
             * @var Customer $newCustomerData
             */
            $newCustomerData = $observer->getEvent()->getData('customer_data_object');

            /**
             * @var Customer $oldCustomerData
             */
            $oldCustomerData = $observer->getEvent()->getData('orig_customer_data_object');

            /**
             * When customer register for the first time then old data will be null
             */
            if ($oldCustomerData == null) {
                $this->assignIntegrationNumber($newCustomerData);
                return;
            }

            $newDataHaveSequenceNumber = false;

            if ($newCustomerData->getCustomAttribute('integration_number') &&
                $newCustomerData->getCustomAttribute('integration_number')->getValue()
            ) {
                $newDataHaveSequenceNumber = true;
            }

            $oldDataHaveSequenceNumber = false;

            if ($oldCustomerData->getCustomAttribute('integration_number') &&
                $oldCustomerData->getCustomAttribute('integration_number')->getValue()
            ) {
                $oldDataHaveSequenceNumber = true;
            }

            if (!$oldDataHaveSequenceNumber && $newDataHaveSequenceNumber) {
                $APIParameters = $this->getAPIParameters($newCustomerData, 'register');
                $this->POSSystem->syncMember($APIParameters);
            } elseif ($oldDataHaveSequenceNumber && $newDataHaveSequenceNumber) {
                $oldDataAPIParameters = $this->getAPIParameters($oldCustomerData, 'update');
                $newDataAPIParameters = $this->getAPIParameters($newCustomerData, 'update');
                if ($this->APIValuesChanged($oldDataAPIParameters, $newDataAPIParameters)) {
                    $this->POSSystem->syncMember($newDataAPIParameters);
                }
            }
        } catch (\Exception $e) {
            $this->logger->addExceptionMessage($e->getMessage());
        }
    }

    /**
     * SHORT DESCRIPTION
     * LONG DESCRIPTION LINE BY LINE
     * @param $customer Customer
     * @return mixed
     */
    private function assignIntegrationNumber($customer)
    {
        try {
            $posOrOnline = 'online';
            if ($customer->getCustomAttribute('referrer_code')) {
                $posOrOnline = 'pos';
            }
            if ($posOrOnline == 'online') {
                $posOrOnline = $customer->getCustomAttribute('imported_from_pos')->getValue() == 1 ? 'pos' : 'online';
            }

            $this->sequence->setCustomerType($posOrOnline);
            if ($posOrOnline == 'online') {
                $secquenceNumber = $this->sequence->getNextValue();
                $customer->setCustomAttribute('integration_number', $secquenceNumber);
            }
            $customer->setCustomAttribute(
                'sales_organization_code',
                $this->config->getOrganizationSalesCode($customer->getWebsiteId())
            );
            $customer->setCustomAttribute(
                'sales_office_code',
                $this->config->getOfficeSalesCode($customer->getWebsiteId())
            );
            $customer->setCustomAttribute('partner_id', $this->config->getPartnerId($customer->getWebsiteId()));
            return $this->customerRepository->save($customer);
        } catch (\Exception $e) {
            $e->getMessage();
            $this->logger->addExceptionMessage($e->getMessage());
            return $customer;
        }
    }

    private function getAPIParameters($customer, $action)
    {
        $parameters = [];
        $parameters['cstmIntgSeq'] = $customer->getCustomAttribute('integration_number')->getValue();
        $parameters['if_flag'] = $action == 'register' ? 'I' : 'U';
        $parameters['firstName'] = trim($customer->getFirstname());
        $parameters['lastName'] = trim($customer->getLastname());
        $parameters['birthDay'] = $customer->getDob()?preg_replace('/\D/', '', $customer->getDob()):'';
        $parameters['mobileNo'] = $customer->getCustomAttribute('mobile_number')->getValue();
        $parameters['email'] = trim($customer->getEmail());
        $parameters['sex'] = $customer->getGender() == '1' ? 'M' : 'F';
        $parameters['emailYN'] = $this->isCustomerSubscribToNewsLetters($customer->getId()) ? 'Y' : 'N';
        if ($customer->getCustomAttribute('sms_subscription_status')) {
            $parameters['smsYN'] = $customer->getCustomAttribute('sms_subscription_status')->getValue() == 1 ? 'Y' : 'N';
        } else {
            $parameters['smsYN'] = '';
        }
        $parameters['callYN'] = 'N';
        if ($customer->getCustomAttribute('dm_subscription_status')) {
            $parameters['dmYN'] = $customer->getCustomAttribute('dm_subscription_status')->getValue() == 1 ? 'Y' : 'N';
        } else {
            $parameters['dmYN'] = '';
        }
        $regionName = $customer->getCustomAttribute('dm_state') ?
            $customer->getCustomAttribute('dm_state')->getValue() : '';
        $regionObject = null;
        if ($regionName) {
            $regionObject = $this->getRegionObject($regionName);
            $parameters['homeCity'] = $regionObject->getCode()?$regionObject->getCode():'';
        } else {
            $parameters['homeCity'] = '';
        }

        $cityName = $customer->getCustomAttribute('dm_city') ?
            $customer->getCustomAttribute('dm_city')->getValue() : '';
        $parameters['homeState'] = '';
        if ($cityName && $regionObject) {
            $cities = $this->cityHelper->getCityData();
            $regionCities = $cities[$regionObject->getRegionId()];
            foreach ($regionCities as $regionCity) {
                if ($regionCity['name'] == $cityName) {
                    $parameters['homeState'] = $regionCity['code'];
                    break;
                }
            }
        }

        $parameters['homeAddr1'] = $customer->getCustomAttribute('dm_detailed_address') ?
            trim($customer->getCustomAttribute('dm_detailed_address')->getValue()) : '';
        $parameters['homeZip'] = $customer->getCustomAttribute('dm_zipcode') ?
            $customer->getCustomAttribute('dm_zipcode')->getValue() : '';

        $parameters['salOrgCd'] =  $customer->getCustomAttribute('sales_organization_code')?
            $customer->getCustomAttribute('sales_organization_code')->getValue():'';
        $parameters['salOffCd'] = $customer->getCustomAttribute('sales_office_code')?
            $customer->getCustomAttribute('sales_office_code')->getValue():'';
        $parameters['prtnrid'] = $customer->getCustomAttribute('partner_id')?
            $customer->getCustomAttribute('partner_id')->getValue():'';
        $parameters['statusCD'] = '01';

        return $parameters;
    }

    private function APIValuesChanged($oldValues, $newValues)
    {
        $result = false;
        foreach ($oldValues as $key => $value) {
            if ($value != $newValues[$key]) {
                $result = true;
                break;
            }
        }
        return $result;
    }

    /**
     * To check whether customer is subscribed to the news letters or not
     *
     * @param int $customerId
     *
     * @return bool
     */
    private function isCustomerSubscribToNewsLetters($customerId)
    {
        /**
         * @var Subscriber $subscriber
         */
        $subscriber = $this->subscriberFactory->create();
        $status = $subscriber->loadByCustomerId((int)$customerId)->isSubscribed();

        return (bool)$status;
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
}