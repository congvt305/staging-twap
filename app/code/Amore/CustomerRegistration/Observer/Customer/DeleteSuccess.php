<?php
/**
 * @author Eguana Team
 * @copyriht Copyright (c) 2020 Eguana {http://eguanacommerce.com}
 * Created by PhpStorm
 * User: abbas
 * Date: 20. 8. 4
 * Time: 오전 11:02
 */

namespace Amore\CustomerRegistration\Observer\Customer;

use Amore\CustomerRegistration\Model\POSLogger;
use Amore\CustomerRegistration\Model\POSSystem;
use Magento\Framework\Event\ObserverInterface;
use Magento\Newsletter\Model\SubscriberFactory;
use Magento\Newsletter\Model\Subscriber;
use Magento\Directory\Model\RegionFactory;
use Magento\Directory\Model\ResourceModel\Region as RegionResourceModel;
use \Magento\Customer\Model\Data\Customer;

/**
 * PLEASE ENTER ONE LINE SHORT DESCRIPTION OF CLASS
 * Class DeleteSuccess
 * @package Amore\CustomerRegistration\Observer\Customer
 */
class DeleteSuccess implements ObserverInterface
{
    /**
     * @var POSSystem
     */
    private $POSSystem;

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
        SubscriberFactory $subscriberFactory,
        POSLogger $logger,
        POSSystem $POSSystem
    ) {
        $this->POSSystem = $POSSystem;
        $this->subscriberFactory = $subscriberFactory;
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
            $customer = $observer->getEvent()->getCustomer();
            $customAttributes = $customer->getCustomAttributes();
            $APIParameters = $this->getAPIParameters($customer);
            $this->POSSystem->syncMember($APIParameters);
        } catch (\Exception $e) {
            $this->logger->addExceptionMessage($e->getMessage());
        }
    }

    /**
     * SHORT DESCRIPTION
     * LONG DESCRIPTION LINE BY LINE
     * @param $customer Customer
     * @return array
     */
    private function getAPIParameters($customer)
    {
        $parameters = [];
        $parameters['cstmIntgSeq'] = $customer->getData('integration_number');
        $parameters['if_flag'] = 'U';
        $parameters['firstName'] = trim($customer->getFirstname());
        $parameters['lastName'] = trim($customer->getLastname());
        $parameters['birthDay'] = $customer->getDob()?preg_replace('/\D/', '', $customer->getDob()):'';
        $parameters['mobileNo'] = $customer->getData('mobile_number');
        $parameters['email'] = trim($customer->getEmail());
        $parameters['sex'] = $customer->getGender() == '1' ? 'M' : 'F';
        $parameters['emailYN'] = $this->isCustomerSubscribToNewsLetters($customer->getId()) ? 'Y' : 'N';
        if ($customer->getCustomAttribute('sms_subscription_status')) {
            $parameters['smsYN'] = $customer->getData('sms_subscription_status') == 1 ? 'Y' : 'N';
        } else {
            $parameters['smsYN'] = '';
        }
        $parameters['callYN'] = 'N';
        if ($customer->getData('dm_subscription_status')) {
            $parameters['dmYN'] = $customer->getData('dm_subscription_status') == 1 ? 'Y' : 'N';
        } else {
            $parameters['dmYN'] = '';
        }
        $regionName = $customer->getData('dm_state') ?
            $customer->getData('dm_state') : '';
        $regionObject = null;
        if ($regionName) {
            $regionObject = $this->getRegionObject($regionName);
            $parameters['homeCity'] = $regionObject->getCode()?$regionObject->getCode():'';
        } else {
            $parameters['homeCity'] = '';
        }

        $cityName = $customer->getData('dm_city') ?
            $customer->getData('dm_city') : '';
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

        $parameters['homeAddr1'] = $customer->getData('dm_detailed_address') ?
            trim($customer->getData('dm_detailed_address')) : '';
        $parameters['homeZip'] = $customer->getData('dm_zipcode') ?
            $customer->getData('dm_zipcode') : '';

        $parameters['salOrgCd'] =  $customer->getData('sales_organization_code')?
            $customer->getData('sales_organization_code'):'';
        $parameters['salOffCd'] = $customer->getData('sales_office_code')?
            $customer->getData('sales_office_code'):'';
        $parameters['prtnrid'] = $customer->getData('partner_id')?
            $customer->getData('partner_id'):'';
        $parameters['statusCD'] = '02';

        return $parameters;
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
            $this->logger->addExceptionMessage($e->getMessage());
        }
        return $region;
    }
}