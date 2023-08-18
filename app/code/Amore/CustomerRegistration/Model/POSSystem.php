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

use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Amore\CustomerRegistration\Helper\Data;
use GuzzleHttp\ClientFactory;
use GuzzleHttp\Psr7\ResponseFactory;
use Magento\Directory\Model\RegionFactory;
use Magento\Directory\Model\ResourceModel\Region as RegionResourceModel;
use CJ\Middleware\Helper\Data as MiddlewareHelper;
use CJ\Middleware\Model\Pos\Connection\Request as MiddlewareRequest;
/**
 * In this class we will call the POS API
 * Class POSSystem
 * @package Amore\CustomerRegistration\Model
 */
class POSSystem
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
     * @var Data
     */
    private $config;

    /**
     * @var \Magento\Framework\HTTP\ZendClientFactory
     */
    private $httpClientFactory;
    /**
     * @var \Zend\Http\Client
     */
    private $zendClient;
    /**
     * @var POSLogger
     */
    private $logger;
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
    private $middlewareHelper;

    /**
     * @var MiddlewareRequest
     */
    private $middleRequest;

    /**
     * @param RegionFactory $regionFactory
     * @param RegionResourceModel $regionResourceModel
     * @param \Eguana\Directory\Helper\Data $cityHelper
     * @param Data $config
     * @param TimezoneInterface $timezone
     * @param \Magento\Framework\HTTP\ZendClientFactory $httpClientFactory
     * @param \Zend\Http\Client $zendClient
     * @param \Amore\CustomerRegistration\Model\POSLogger $logger
     * @param \Magento\Framework\Event\ManagerInterface $eventManager
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param MiddlewareHelper $middlewareHelper
     * @param MiddlewareRequest $middleRequest
     */
    public function __construct(
        RegionFactory $regionFactory,
        RegionResourceModel $regionResourceModel,
        \Eguana\Directory\Helper\Data $cityHelper,
        Data $config,
        TimezoneInterface $timezone,
        \Magento\Framework\HTTP\ZendClientFactory $httpClientFactory,
        \Zend\Http\Client $zendClient,
        POSLogger $logger,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        MiddlewareHelper $middlewareHelper,
        MiddlewareRequest $middleRequest
    ) {
        $this->middlewareHelper = $middlewareHelper;
        $this->timezone = $timezone;
        $this->config = $config;
        $this->httpClientFactory = $httpClientFactory;
        $this->zendClient = $zendClient;
        $this->logger = $logger;
        $this->eventManager = $eventManager;
        $this->storeManager = $storeManager;
        $this->regionFactory = $regionFactory;
        $this->regionResourceModel = $regionResourceModel;
        $this->cityHelper = $cityHelper;
        $this->middleRequest = $middleRequest;
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
        $isMiddlewareEnable = $this->middlewareHelper->isMiddlewareEnabled('store', $storeId);
        $parameters = [
            'firstName' => $firstName,
            'lastName' => $lastName,
            'mobileNumber' => $mobileNumber,
            'salOrgCd' => $this->middlewareHelper->getSalesOrganizationCode('store', $storeId),
            'salOffCd' => $this->middlewareHelper->getSalesOfficeCode('store', $storeId)
        ];
        try {
            $this->logger->addAPILog(
                'POS get info API Call',
                $parameters
            );

            $apiResponse = $this->middleRequest->sendRequest($parameters, $storeId, 'memberInfo');
            $response = $this->middlewareHelper->unserializeData($apiResponse);
            $result = $this->handleResponse($response, $isMiddlewareEnable);
            $this->logger->addAPILog(
                'POS get info API Response',
                $response
            );
        } catch (\Exception $e) {
            if ($e->getMessage() == '<url> malformed') {
                $result['message'] = __('Please first configure POS APIs properly. Then try again.');
            } else {
                $result['message'] = $e->getMessage();
            }
            $this->logger->addAPILog($e->getMessage());
            $callSuccess = 0;
        }

        $log['request'] = $parameters;
        $log['response'] = $response;

        $websiteName = $this->storeManager->getWebsite()->getName();
        $resultMessage = isset($result['message']) ? $result['message'] : 'Fail';
        if ($isMiddlewareEnable) {
            if ((isset($result['success']) && $result['success']) &&
                (isset($result['data']) && isset($result['data']['checkYN']) && $result['data']['checkYN'] == 'N')
            ) {
                $resultMessage = __('No information exist in POS');
            } elseif ((isset($result['success']) && $result['success']) &&
                (isset($result['data']) && isset($result['data']['checkYN']) && $result['data']['checkYN'] == 'Y') &&
                $resultMessage == 'Fail'
            ) {
                $resultMessage = __('Information loaded successfully');
            }
        } else {
            if ($response['message'] == 'SUCCESS' && $response['data']['checkYN'] == 'N') {
                $resultMessage = __('No information exist in POS');
            }elseif ($response['message'] == 'SUCCESS' && $response['data']['checkYN'] == 'Y' && $resultMessage == 'Fail') {
                $resultMessage = __('Information loaded successfully');
            }
        }

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
     * @param $response
     * @param $isMiddlewareEnable
     * @return array|mixed
     * @throws NoSuchEntityException
     */
    public function handleResponse($response, $isMiddlewareEnable = false)
    {
        if ($isMiddlewareEnable) {
            if ((isset($response['success']) && $response['success']) &&
                (isset($response['data']) && isset($response['data']['checkYN']) && $response['data']['checkYN'] == 'Y')
            ) {
                $result = $this->processResponseData($response);
            } elseif ((isset($response['success']) && $response['success']) &&
                (isset($response['data']) && isset($response['data']['checkYN']) && $response['data']['checkYN'] == 'N')
            ) {
                $result = [];
            } else {
                if (isset($response['data']) &&
                    isset($response['data']['statusMessage']) &&
                    $response['data']['statusMessage']
                ) {
                    $result['message'] = $response['data']['statusMessage'];
                } else {
                    $result = [];
                }
            }
        } else {
            if ($response['message'] == 'SUCCESS' && $response['data']['checkYN'] == 'Y') {
                $result = $this->processResponseData($response);
            } elseif ($response['message'] == 'SUCCESS' && $response['data']['checkYN'] == 'N') {
                $result = [];

            } else {
                $result['message'] = $response['message'];
            }
        }
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
            $cmsPage = $this->config->getDuplicateMembershipCmsPage();
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
            $cmsPage = $this->config->getMembershipErrorCmsPage();
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
            $this->logger->addAPILog($e->getMessage());
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
        $isMiddlewareEnable = $this->middlewareHelper->isMiddlewareEnabled('store', $storeId);
        try {


            $this->logger->addAPILog(
                'POS set info API Call',
                $parameters
            );
            $apiResponse = $this->middleRequest->sendRequest($parameters, $storeId, 'memberJoin');


            $response = $this->middlewareHelper->unserializeData($apiResponse);
            if ($isMiddlewareEnable) {
                if (isset($response['success']) && $response['success']) {
                    $result['message'] = $response['data']['statusMessage'];
                    $result['status'] = 1;
                } else {
                    $result['message'] = $response['data']['statusMessage'];
                    $result['status'] = 0;
                }
            } else {
                if ($response['message'] == 'SUCCESS') {
                    $result['message'] = $response['message'];
                    $result['status'] = 1;
                } else {
                    $result['message'] = $response['message'];
                    $result['status'] = 0;
                }
            }
            $this->logger->addAPILog(
                'POS set info API Response',
                $response
            );

        } catch (\Exception $e) {
            $result['message'] = $e->getMessage();
            $result['status'] = 0;
            $this->logger->addAPILog($e->getMessage());
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
        $result['verify'] = false;
        $response = [];
        $storeId = $this->getStoreId();
        $isMiddlewareEnable = $this->middlewareHelper->isMiddlewareEnabled('store', $storeId);
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

            $this->logger->addAPILog(
                'POS get BA Code info API Call',
                $parameters
            );
            $apiResponse = $this->middleRequest->sendRequest( $parameters, $storeId, 'baInfo');
            $response = $this->middlewareHelper->unserializeData($apiResponse);
            if ($isMiddlewareEnable) {
                if ((isset($response['success']) && $response['success']) &&
                    (isset($response['data']) && isset($response['data']['exitYN']) && $response['data']['exitYN'] == 'Y')
                ) {
                    $result['verify']   = true;
                    $result['message']  = __('The code is confirmed as valid information');
                } elseif ((isset($response['success']) && $response['success']) &&
                    (isset($response['data']) && isset($response['data']['exitYN']) && $response['data']['exitYN'] == 'N')
                ) {
                    $result['message'] = __('No such information, please re-enter');
                } else {
                    $result['message'] = __('Unable to fetch information at this time');
                }
            } else {
                if (isset($response['message']) == 'SUCCESS' && isset($response['data']['exitYN'])
                    && $response['data']['exitYN'] == 'Y') {
                    $result['verify']   = true;
                    $result['message']  = __('The code is confirmed as valid information');
                } elseif (isset($response['message']) == 'SUCCESS' && isset($response['data']['exitYN'])
                    && $response['data']['exitYN'] == 'N') {
                    $result['message'] = __('No such information, please re-enter');
                } else {
                    $result['message'] = __('Unable to fetch information at this time');
                }
            }

            $this->logger->addAPILog(
                'POS get BA Code info API Response',
                $response
            );

        } catch (\Exception $e) {
            if ($e->getMessage() == '<url> malformed') {
                $result['message'] = __('Please first configure POS APIs properly. Then try again.');
            } else {
                $result['message'] = $e->getMessage();
            }
            $this->logger->addAPILog($e->getMessage());
            $callSuccess = 0;
        }

        $log['request'] = $parameters;
        $log['response'] = $response;

        $websiteName = $this->storeManager->getWebsite()->getName();

        $resultMessage = isset($result['message'])?$result['message']:'Fail';
        if ($isMiddlewareEnable) {
            if ((isset($response['success']) && $response['success']) &&
                (isset($response['data']) && isset($response['data']['exitYN']) && $response['data']['exitYN'] == 'Y') &&
                $resultMessage == 'Fail'
            ) {
                $resultMessage = __('Information loaded successfully');
            } elseif ((isset($response['success']) && $response['success']) &&
                (isset($response['data']) && isset($response['data']['exitYN']) && $response['data']['exitYN'] == 'N')
            ) {
                $resultMessage = __('No information exist in POS');
            }
        } else {
            if ($response['message'] == 'SUCCESS' && $response['data']['exitYN'] == 'N') {
                $resultMessage = __('No information exist in POS');
            } elseif ($response['message'] == 'SUCCESS' && $response['data']['exitYN'] == 'Y' &&
                $resultMessage == 'Fail') {
                $resultMessage = __('Information loaded successfully');
            }
        }

        $this->eventManager->dispatch(
            self::EGUANA_BIZCONNECT_OPERATION_PROCESSED,
            [
                'topic_name' => 'eguana.pos.get.bacode.info',
                'direction' => 'outgoing',
                'to' => $websiteName,
                'serialized_data' => $this->middlewareHelper->serializeData($log),
                'status' => $callSuccess,
                'result_message' => $resultMessage
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
            $prefix = $this->config->getBaCodePrefix($websiteId);
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
            $this->logger->debug($exception->getMessage());
        }
    }
}
