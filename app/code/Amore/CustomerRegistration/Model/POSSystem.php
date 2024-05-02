<?php
/**
 * @author Eguana Team
 * @copyriht Copyright (c) 2020 Eguana {http://eguanacommerce.com}
 * Created by PhpStorm
 * User: abbas
 * Date: 20. 6. 8
 * Time: 오후 1:17
 */

namespace Amore\CustomerRegistration\Model;

use Amore\PointsIntegration\Model\Source\Config;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\HTTP\Client\Curl;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Amore\CustomerRegistration\Helper\Data as AmoreHelper;
use Magento\Framework\Event\ManagerInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Directory\Model\RegionFactory;
use Magento\Directory\Model\ResourceModel\Region as RegionResourceModel;
use CJ\Middleware\Helper\Data as MiddlewareHelper;
use CJ\Middleware\Model\PosRequest;
use Psr\Log\LoggerInterface as Logger;

/**
 * In this class we will call the POS API
 * Class POSSystem
 * @package Amore\CustomerRegistration\Model
 */
class POSSystem extends PosRequest
{
    /**#@+
     * BA Code PREFIX
     */
    const DATE_FORMAT = 'd/m/Y';
    /**#@-*/

    const EGUANA_BIZCONNECT_OPERATION_PROCESSED = 'eguana_bizconnect_operation_processed';

    /**
     * @var TimezoneInterface
     */
    private $timezone;

    /**
     * @var Config
     */
    protected $config;

    /**
     * @var Logger
     */
    protected $logger;

    /**
     * @var \Magento\Framework\Event\ManagerInterface
     */
    private $eventManager;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $storeManager;

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
     * @var MiddlewareHelper
     */
    protected $middlewareHelper;

    /**
     * @var POSLogger
     */
    protected $posLogger;

    /**
     * @var AmoreHelper
     */
    protected $amoreHelper;

    /**
     * @param Curl $curl
     * @param MiddlewareHelper $middlewareHelper
     * @param Logger $logger
     * @param Config $config
     * @param RegionFactory $regionFactory
     * @param RegionResourceModel $regionResourceModel
     * @param \Eguana\Directory\Helper\Data $cityHelper
     * @param TimezoneInterface $timezone
     * @param ManagerInterface $eventManager
     * @param StoreManagerInterface $storeManager
     * @param POSLogger $posLogger
     * @param AmoreHelper $amoreHelper
     */
    public function __construct(
        Curl $curl,
        MiddlewareHelper $middlewareHelper,
        Logger $logger,
        Config $config,
        RegionFactory $regionFactory,
        RegionResourceModel $regionResourceModel,
        \Eguana\Directory\Helper\Data $cityHelper,
        TimezoneInterface $timezone,
        ManagerInterface $eventManager,
        StoreManagerInterface $storeManager,
        POSLogger $posLogger,
        AmoreHelper $amoreHelper
    ) {
        $this->timezone = $timezone;
        $this->eventManager = $eventManager;
        $this->storeManager = $storeManager;
        $this->regionFactory = $regionFactory;
        $this->regionResourceModel = $regionResourceModel;
        $this->cityHelper = $cityHelper;
        $this->posLogger = $posLogger;
        $this->amoreHelper = $amoreHelper;
        parent::__construct($curl, $middlewareHelper, $logger, $config);
    }

    public function getMemberInfo($firstName, $lastName, $mobileNumber, $storeId = null)
    {
        $posData = $this->callPOSInfoAPI(trim($firstName), trim($lastName), trim($mobileNumber), $storeId);
        if (isset($posData['birthDay'])) {
            $posData['birthDay'] = substr_replace($posData['birthDay'], '/', 4, 0);
            $posData['birthDay'] = substr_replace($posData['birthDay'], '/', 7, 0);
            if ($this->storeManager->getStore()->getCode() == "vn_laneige") {
                $posData['birthDay'] = $this->changeDateFormat(strtotime($posData['birthDay']));
            }
        }
        return $posData;
    }

    public function getStoreId()
    {
        try {
            $storeId = $this->storeManager->getStore()->getId();
        } catch (NoSuchEntityException $e) {
            $storeId = $this->storeManager->getDefaultStoreView()->getId();
        }
        return $storeId;
    }

    private function callPOSInfoAPI($firstName, $lastName, $mobileNumber, $storeId = null)
    {
        $result = [];
        $response = [];
        if (!$storeId) {
            $storeId = $this->getStoreId();
        }
        $callSuccess = 1;
        $parameters = [
            'firstName' => $firstName,
            'lastName' => $lastName,
            'mobileNumber' => $mobileNumber,
            'salOrgCd' => $this->middlewareHelper->getSalesOrganizationCode('store', $storeId),
            'salOffCd' => $this->middlewareHelper->getSalesOfficeCode('store', $storeId)
        ];
        try {
            $this->posLogger->addAPILog(
                'POS get info API Call',
                $parameters
            );

            $response = $this->sendRequest($parameters, $storeId, 'memberInfo');

            $result = $this->handleResponse($response, 'memberInfo');
            $this->posLogger->addAPILog(
                'POS get info API Response',
                $response
            );
        } catch (\Exception $e) {
            if ($e->getMessage() == '<url> malformed') {
                $result['message'] = __('Please first configure POS APIs properly. Then try again.');
            } else {
                $result['message'] = $e->getMessage();
            }
            $this->posLogger->addAPILog($e->getMessage());
            $callSuccess = 0;
        }

        $log['request'] = $parameters;
        $log['response'] = $response;

        $websiteName = $this->storeManager->getWebsite()->getName();
        $resultMessage = $this->validateResponseBACodeInfoApi($response)['message'];

        $this->eventManager->dispatch(
            self::EGUANA_BIZCONNECT_OPERATION_PROCESSED,
            [
                'topic_name' => 'eguana.pos.get.info',
                'direction' => 'outgoing',
                'to' => $websiteName, //from or to
                'serialized_data' => $this->middlewareHelper->serializeData($log),
                'status' => $callSuccess,
                'result_message' => $resultMessage
            ]
        );

        return $result;
    }

    /**
     * Function to process data of response
     * @param $response
     * @return array|mixed
     * @throws NoSuchEntityException
     */
    public function processResponseData($response)
    {
        if ($response['data']['mobilecheckCnt'] > 1) {
            $result['code'] = 4;
            $cmsPage = $this->amoreHelper->getDuplicateMembershipCmsPage();
            if ($cmsPage) {
                $result['url'] = $this->storeManager->getStore()->getBaseUrl().$cmsPage;
            } else {
                $link = $this->getCustomerServiceLink();
                $result['message'] = __(
                    'There is a problem with the requested subscription information. Please contact our CS Center for registration. %1',
                    $link
                );
            }
        } elseif (isset($response['data']['customerInfo']['cstmIntgSeq']) == false ||
            $response['data']['customerInfo']['cstmIntgSeq'] == ''
        ) {
            $result['code'] = 5;
            $cmsPage = $this->amoreHelper->getMembershipErrorCmsPage();
            if ($cmsPage) {
                $result['url'] = $this->storeManager->getStore()->getBaseUrl().$cmsPage;
            } else {
                $link = $this->getLogInLink();
                $result['message'] = __(
                    'This member account has been registered, please %1 to the member directly, thank you',
                    $link
                );
            }

        } else {
            $result = $response['data']['customerInfo'];
            $result['region'] = [];
            if ($result['homeCity']) {
                /** @var \Magento\Directory\Model\Region $region */
                $region = $this->regionFactory->create();
                $this->regionResourceModel->load($region, $result['homeCity'], 'code');
                $result['region'] = $region->getData();
            }

            if (isset($result['region']['region_id']) && $result['homeState']) {
                if ($result['homeState'] && $result['region']['region_id']) {
                    $cities = $this->cityHelper->getCityData();
                    $regionCities = isset($cities[$result['region']['region_id']]) ? $cities[$result['region']['region_id']] : [];
                    foreach ($regionCities as $regionCity) {
                        if ($regionCity['pos_code'] == $result['homeState']) {
                            $result['city'] = $regionCity;
                            break;
                        }
                    }
                }
            }

            if (!isset($result['city'])) {
                $temp = [];
                $temp['code'] = '';
                $temp['name'] = '';
                $result['city'] = $temp;
            }
        }
        return $result;
    }

    /**
     * It will call the POS API join
     * Whenever customer will update or register this function will call and it will sync with the POS system
     *
     * @param Array $parameters
     * @param $storeId
     */
    public function syncMember($parameters, $storeId = null)
    {
        try {
            $response = $this->callJoinAPI($parameters, $storeId);
            //$this->savePOSSyncReport($parameters, $response);
        } catch (\Exception $e) {
            $this->posLogger->addAPILog($e->getMessage());
            return $e->getMessage();
        }
    }

    private function callJoinAPI($parameters, $storeId = null)
    {
        $result = [];
        $callSuccess = 1;
        $response = [];
        if (!$storeId) {
            $storeId = $this->getStoreId();
        }
        try {


            $this->posLogger->addAPILog(
                'POS set info API Call',
                $parameters
            );
            $response = $this->sendRequest($parameters, $storeId, 'memberJoin');

            $result = $this->handleResponse($response, 'memberJoin');

            $this->posLogger->addAPILog(
                'POS set info API Response',
                $response
            );

        } catch (\Exception $e) {
            $result['message'] = $e->getMessage();
            $result['status'] = 0;
            $this->posLogger->addAPILog($e->getMessage());
            $callSuccess = 0;
        }

        $log['request'] = $parameters;
        $log['response'] = $response;

        $websiteName = $this->storeManager->getWebsite()->getName();

        $this->eventManager->dispatch(
            self::EGUANA_BIZCONNECT_OPERATION_PROCESSED,
            [
                'topic_name' => 'eguana.pos.sync.info',
                'direction' => 'outgoing',
                'to' => $websiteName, //from or to
                'serialized_data' => $this->middlewareHelper->serializeData($log),
                'status' => $callSuccess,
                'result_message' => isset($result['message'])?$result['message']:'Fail'
            ]
        );

        return $result;
    }

    /**
     * To get current site base usrl
     *
     * @return string
     */
    private function getLogInLink()
    {
        try {
            $link = $this->storeManager->getStore()->getBaseUrl() . 'customer/account/login/';
            return '<a href="' . $link . '">' . __('Log in') . '</a>';
        } catch (\Exception $e) {
            return __('Log in');
        }
    }

    /**
     * To get Customer Service Link
     *
     * @return string
     */
    private function getCustomerServiceLink()
    {
        try {
            $link = $this->storeManager->getStore()->getBaseUrl() . 'contact';
            return '<a href="' . $link . '">' . __('Customer Care Service') . '</a>';
        } catch (\Exception $e) {
            return __('Customer Care Service');
        }
    }

    /**
     * To call POS Api for BA Code info
     *
     * @param $baCode
     * @param null $websiteId
     * @param null $salOrgCd
     * @param null $salOffCd
     * @return array
     */
    public function callBACodeInfoApi($baCode, $websiteId = null, $salOrgCd = null, $salOffCd = null)
    {
        $result = [
            'verify' => false,
            'message' => ''
        ];
        $response = [];
        $storeId = $this->getStoreId();
        if (!$salOrgCd) {
            $salOrgCd = $this->middlewareHelper->getSalesOrganizationCode('store', $storeId);
        }
        if (!$salOffCd) {
            $salOffCd = $this->middlewareHelper->getSalesOfficeCode('store', $storeId);
        }
        $callSuccess = 1;
        $baCode = $this->checkBACodePrefix($baCode);
        try {
            $parameters = [
                'empID' => $baCode,
                'salOrgCd' => $salOrgCd,
                'salOffCd' => $salOffCd
            ];

            $this->posLogger->addAPILog(
                'POS get BA Code info API Call',
                $parameters
            );
            $response = $this->sendRequest( $parameters, $storeId, 'baInfo');

            $this->posLogger->addAPILog(
                'POS get BA Code info API Response',
                $response
            );

        } catch (\Exception $e) {
            if ($e->getMessage() == '<url> malformed') {
                $result['message'] = __('Please first configure POS APIs properly. Then try again.');
            } else {
                $result['message'] = $e->getMessage();
            }
            $this->posLogger->addAPILog($e->getMessage());
            $callSuccess = 0;
        }

        $log['request'] = $parameters;
        $log['response'] = $response;

        $websiteName = $this->storeManager->getWebsite()->getName();

        $result = $this->validateResponseBACodeInfoApi($response);

        $this->eventManager->dispatch(
            self::EGUANA_BIZCONNECT_OPERATION_PROCESSED,
            [
                'topic_name' => 'eguana.pos.get.bacode.info',
                'direction' => 'outgoing',
                'to' => $websiteName,
                'serialized_data' => $this->middlewareHelper->serializeData($log),
                'status' => $callSuccess,
                'result_message' => $result['message']
            ]
        );

        return $result;
    }

    /**
     * To check BA Code Prefix exist or not then ammend it
     *
     * @param $baCode
     * @return string
     */
    public function checkBACodePrefix($baCode)
    {
        $baCode = $baCode ? trim($baCode) : '';
        if ($baCode) {
            $websiteId = $this->storeManager->getWebsite()->getId();
            $checkTW = substr($baCode, 0, 2);
            $prefix = $this->amoreHelper->getBaCodePrefix($websiteId);
            if (in_array(strtolower($checkTW), ['tw', 'vn'])) {
                $baCode = strtoupper($checkTW) . substr($baCode, 2);
            } else {
                if ($prefix) {
                    $baCode = strtoupper($prefix) . $baCode;
                }

            }
        }
        return $baCode;
    }

    /**
     * This method is used to change the date format
     * @param $date
     * @return string
     */
    public function changeDateFormat($date)
    {
        try {
            return $this->timezone->date($date)->format(self::DATE_FORMAT);
        } catch (\Exception $exception) {
            $this->posLogger->addAPILog($exception->getMessage());
        }
    }

    /**
     * @param $response
     * @return array
     */
    public function validateResponseBACodeInfoApi($response)
    {
        $result['message'] = __('Unable to fetch information at this time');
        $result['verify'] = false;
        if (isset($response['success']) && $response['success']) {
            if (isset($response['data'], $response['data']['exitYN']) && $response['data']['exitYN'] == 'N') {
                $result['message'] = __('No information exist in POS');
            } elseif (isset($response['data'], $response['data']['exitYN']) && $response['data']['exitYN'] == 'Y') {
                $result['verify'] = true;
                $result['message'] = __('Information loaded successfully');
            }
        }
        return $result;
    }
}
