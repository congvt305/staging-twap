<?php
/**
 * @author Eguana Team
 * @copyriht Copyright (c) 2020 Eguana {http://eguanacommerce.com}
 * Created by PhpStorm
 * User: abbas
 * Date: 20. 8. 11
 * Time: 오후 8:14
 */

namespace Amore\CustomerRegistration\Model;

use Amore\CustomerRegistration\Model\POSLogger;
use Magento\Newsletter\Model\SubscriberFactory;
use Magento\Newsletter\Model\Subscriber;
use \Magento\Customer\Model\Data\Customer;

/**
 * It will prepare the customer API request parameters
 * Class POSSyncAPI
 * @package Amore\CustomerRegistration\Model
 */
class POSSyncAPI
{
    /**
     * @var POSLogger
     */
    private $logger;

    /**
     * @var SubscriberFactory
     */
    private $subscriberFactory;

    /**
     * @var \Eguana\Directory\Helper\Data
     */
    private $cityHelper;

    public function __construct(
        \Eguana\Directory\Helper\Data $cityHelper,
        SubscriberFactory $subscriberFactory,
        POSLogger $logger
    ) {
        $this->subscriberFactory = $subscriberFactory;
        $this->logger = $logger;
        $this->cityHelper = $cityHelper;
    }

    /**
     * SHORT DESCRIPTION
     * LONG DESCRIPTION LINE BY LINE
     * @param $customer Customer
     * @return array
     */
    public function getAPIParameters($customer, $address, $action)
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
        if ($customer->getData('sms_subscription_status')) {
            $parameters['smsYN'] = $customer->getData('sms_subscription_status') == 1 ? 'Y' : 'N';
        } else {
            $parameters['smsYN'] = 'N';
        }
        if ($customer->getData('call_subscription_status')) {
            $parameters['callYN'] = $customer->getData('call_subscription_status') == 1 ? 'Y' : 'N';
        } else {
            $parameters['callYN'] = 'N';
        }
        if ($customer->getData('dm_subscription_status')) {
            $parameters['dmYN'] = $customer->getData('dm_subscription_status') == 1 ? 'Y' : 'N';
        } else {
            $parameters['dmYN'] = 'N';
        }
        if ($parameters['dmYN'] == 'Y' && $address) {
            $addressParameters = $this->getAddressParameters($address);
            $parameters = array_merge($parameters, $addressParameters);
        }

        $parameters['salOrgCd'] =  $customer->getData('sales_organization_code')?
            $customer->getData('sales_organization_code'):'';
        $parameters['salOffCd'] = $customer->getData('sales_office_code')?
            $customer->getData('sales_office_code'):'';
        $parameters['prtnrid'] = $customer->getData('partner_id')?
            $customer->getData('partner_id'):'';
        $parameters['statusCD'] = $action == 'delete'?'02':'01';

        return $parameters;
    }

    /**
     * To get address values
     * From default billing address get the parameters for the POS sync API
     * @param \Magento\Customer\Model\Address $address
     * @return array
     */
    private function getAddressParameters($address)
    {
        $parameters = [];
        $parameters['homeCity'] = $address->getRegionCode();
        $parameters['homeAddr1'] = $address->getStreetFull();
        $parameters['homeZip'] = $address->getPostcode();
        $cityName = $address->getCity();
        $parameters['homeState'] = '';
        if ($cityName) {
            $cities = $this->cityHelper->getCityData();
            $regionCities = $cities[$address->getRegionId()];
            foreach ($regionCities as $regionCity) {
                if ($regionCity['name'] == $cityName) {
                    $parameters['homeState'] = $regionCity['code'];
                    break;
                }
            }
        }
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
}
