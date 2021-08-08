<?php
/**
 * @author Eguana Team
 * @copyriht Copyright (c) 2021 Eguana {http://eguanacommerce.com}
 * Created by PhpStorm
 * User: adeel
 * Date: 7/15/21
 * Time: 3:10 AM
 */
namespace Amore\GcrmDataExport\Observer\Customer;

use Amore\GcrmDataExport\Model\Config\Config;
use Magento\Customer\Api\Data\AddressInterfaceFactory;
use Magento\Framework\App\Request\DataPersistorInterface;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Amore\CustomerRegistration\Model\POSSystem;
use Magento\Customer\Model\Data\Customer;
use Amore\CustomerRegistration\Model\Sequence;
use Amore\CustomerRegistration\Helper\Data;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Amore\CustomerRegistration\Model\POSLogger;
use Magento\Newsletter\Model\Subscriber;
use Magento\Newsletter\Model\SubscriberFactory;
use Magento\Framework\App\RequestInterface;
use Magento\Directory\Model\RegionFactory;
use Magento\Directory\Model\ResourceModel\Region as RegionResourceModel;
use Magento\Customer\Api\AddressRepositoryInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Customer\Api\GroupRepositoryInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Eguana\Directory\Helper\Data as DataAlias;

/**
 * Call POS API on customer information change
 *
 * Class SaveSuccess
 */
class SaveSuccess implements ObserverInterface
{
    /**
     * @var Config
     */
    private $configData;

    /**
     * @var Sequence
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
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @var Json
     */
    private $json;

    /**
     * @var GroupRepositoryInterface
     */
    private $groupRepository;

    /**
     * @var SearchCriteriaBuilder
     */
    private $searchCriteria;

    /**
     * @var POSLogger
     */
    private $logger;

    /**
     * @var SubscriberFactory
     */
    private $subscriberFactory;

    /**
     * @var DataAlias
     */
    private $cityHelper;

    /**
     * @var RequestInterface
     */
    private $request;

    /**
     * @var RegionFactory
     */
    private $regionFactory;

    /**
     * @var RegionResourceModel
     */
    private $regionResourceModel;

    /**
     * @var AddressRepositoryInterface
     */
    private $addressRepository;

    /**
     * @var AddressInterfaceFactory
     */
    private $addressDataFactory;

    /**
     * SaveSuccess constructor.
     * @param Config $configData
     * @param RequestInterface $request
     * @param RegionFactory $regionFactory
     * @param RegionResourceModel $regionResourceModel
     * @param DataAlias $cityHelper
     * @param Sequence $sequence
     * @param Data $config
     * @param CustomerRepositoryInterface $customerRepository
     * @param SubscriberFactory $subscriberFactory
     * @param POSLogger $logger
     * @param POSSystem $POSSystem
     * @param AddressRepositoryInterface $addressRepository
     * @param AddressInterfaceFactory $addressDataFactory
     * @param StoreManagerInterface $storeManager
     * @param ScopeConfigInterface $scopeConfig
     * @param Json $json
     * @param GroupRepositoryInterface $groupRepository
     * @param SearchCriteriaBuilder $searchCriteria
     */
    public function __construct(
        Config $configData,
        RequestInterface $request,
        RegionFactory $regionFactory,
        RegionResourceModel $regionResourceModel,
        DataAlias $cityHelper,
        Sequence $sequence,
        Data $config,
        CustomerRepositoryInterface $customerRepository,
        SubscriberFactory $subscriberFactory,
        POSLogger $logger,
        POSSystem $POSSystem,
        AddressRepositoryInterface $addressRepository,
        AddressInterfaceFactory $addressDataFactory,
        StoreManagerInterface $storeManager,
        ScopeConfigInterface $scopeConfig,
        Json $json,
        GroupRepositoryInterface $groupRepository,
        SearchCriteriaBuilder $searchCriteria,
        DataPersistorInterface $dataPersistor
    ) {
        $this->configData = $configData;
        $this->sequence = $sequence;
        $this->POSSystem = $POSSystem;
        $this->subscriberFactory = $subscriberFactory;
        $this->config = $config;
        $this->customerRepository = $customerRepository;
        $this->logger = $logger;
        $this->cityHelper = $cityHelper;
        $this->request = $request;
        $this->regionFactory = $regionFactory;
        $this->regionResourceModel = $regionResourceModel;
        $this->addressRepository = $addressRepository;
        $this->addressDataFactory = $addressDataFactory;
        $this->storeManager = $storeManager;
        $this->scopeConfig = $scopeConfig;
        $this->json = $json;
        $this->groupRepository = $groupRepository;
        $this->searchCriteria = $searchCriteria;
        $this->dataPersistor = $dataPersistor;
    }

    /**
     * Observer called on successfull customer registration
     *
     * @param Observer $observer
     */
    public function execute(
        Observer $observer
    ) {
        /**
         * @var Customer $newCustomerData
         */
        $newCustomerData = $observer->getEvent()->getData('customer');

        $homeCity = '';
        $homeState = '';
        $homeAddr1 = '';
        $homeZip = '';

        try {
            $isNew = $observer->getEvent()->getData('isNew');
            $customerId = $newCustomerData->getId();
            $customerData = $this->customerRepository->getById($customerId);
            $regDate = $customerData->getCreatedAt();
            $updDate = $customerData->getUpdatedAt();
            $cstmIntgSeq = $customerData->getCustomAttribute('integration_number')->getValue();
            $firstName = $customerData->getFirstname();
            $lastName = $customerData->getLastname();
            $dob = $customerData->getDob();
            $birthDay = str_replace("-", "", $dob);
            $mobileNo = $customerData->getCustomAttribute('mobile_number')->getValue();
            $email = $customerData->getEmail();
            $sex = $customerData->getGender();
            $emailYN = $this->isCustomerSubscribToNewsLetters($customerData->getId()) ? 'Y' : 'N';
            $callYN = $customerData->getCustomAttribute('call_subscription_status')->getValue();
            $smsYN = $callYN;
            $dmYN = $customerData->getCustomAttribute('dm_subscription_status')->getValue();
            $defaultBillingAddressId = $customerData->getDefaultBilling();
            $addresses = $customerData->getAddresses();
            $defaultBillingAddress = null;
            foreach ($addresses as $address) {
                if ($address->getId() == $defaultBillingAddressId) {
                    $defaultBillingAddress = $address;
                    break;
                }
            }
            $statusCD = $customerData->getCustomAttribute('status_code')->getValue();
            $salOrgCd = $customerData->getCustomAttribute('sales_organization_code')->getValue();
            $salOffCd = $customerData->getCustomAttribute('sales_office_code')->getValue();
            if ($defaultBillingAddress) {
                $homeCity = $defaultBillingAddress->getRegion()->getRegionCode();
                $homeAddr1 = implode(' ', $defaultBillingAddress->getStreet());
                $homeZip = $defaultBillingAddress->getPostcode();
                $cityName = $defaultBillingAddress->getCity();
                if ($cityName) {
                    $cities = $this->cityHelper->getCityData();
                    $regionCities = $cities[$defaultBillingAddress->getRegionId()];
                    foreach ($regionCities as $regionCity) {
                        if ($regionCity['name'] == $cityName) {
                            $homeState = $regionCity['pos_code'];
                            break;
                        }
                    }
                }
            }
        } catch (\Exception $e) {
            $this->logger->addExceptionMessage($e->getMessage());
        }

        try {
            $host = $this->configData->getHerokuHost();
            $dbname = $this->configData->getHerokuDBName();
            $user = $this->configData->getHerokuUser();
            $password = $this->configData->getHerokuPassword();
            $db_connection = pg_connect(
                "host=$host
                     dbname=$dbname
                     user=$user
                     password=$password"
            );
            if ($db_connection) {
                $sql = '';
                $query = "SELECT * FROM apgcrm.GECPCustomer__c WHERE cstmintgseq__c = '$cstmIntgSeq'";
                $result = pg_query($db_connection, $query);
                if ($result && pg_num_rows($result) > 0) {
                    $sql = "UPDATE apgcrm.GECPCustomer__c SET (cstmintgseq__c,ifflag__c,firstName__c,lastName__c,birthDay__c,
                            mobileno__c,email__c,sex__c,emailyn__c,smsyn__c,callyn__c,dmyn__c,homecity__c,homestate__c,
                            homeaddr1__c,homezip__c,statuscd__c,salorgcd__c,saloffcd__c,regdate__c,upddate__c) = (
                            '$cstmIntgSeq','$isNew','$firstName','$lastName','$birthDay','$mobileNo',
                            '$email','$sex','$emailYN','$smsYN','$callYN','$dmYN','$homeCity','$homeState',
                            '$homeAddr1','$homeZip','$statusCD','$salOrgCd','$salOffCd','$regDate','$updDate')
                             WHERE cstmIntgSeq='$cstmIntgSeq'";
                } else {
                    $sql = "INSERT INTO apgcrm.GECPCustomer__c (cstmintgseq__c,ifflag__c,firstName__c,lastName__c,birthDay__c,
                            mobileno__c,email__c,sex__c,emailyn__c,smsyn__c,callyn__c,dmyn__c,homecity__c,homestate__c,
                            homeaddr1__c,homezip__c,statuscd__c,salorgcd__c,saloffcd__c,regdate__c,upddate__c) VALUES (
                            '$cstmIntgSeq','$isNew','$firstName','$lastName','$birthDay','$mobileNo',
                            '$email','$sex','$emailYN','$smsYN','$callYN','$dmYN','$homeCity','$homeState',
                            '$homeAddr1','$homeZip','$statusCD','$salOrgCd','$salOffCd','$regDate','$updDate')";
                }
                $result = pg_exec($db_connection, $sql);
                pg_close($db_connection);
            }
        } catch(\Exception $e) {
            $this->logger->addExceptionMessage($e->getMessage());
        }
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
