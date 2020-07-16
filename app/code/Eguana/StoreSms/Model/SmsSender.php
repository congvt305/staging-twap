<?php
namespace Eguana\StoreSms\Model;

use Eguana\StoreSms\Api\SmsInterface;
use Eguana\StoreSms\Helper\Data;
use Magento\Customer\Model\ResourceModel\Customer\CollectionFactory;
use Magento\Email\Model\TemplateFactory;
use Magento\Framework\HTTP\Client\Curl;
use Magento\Framework\Math\Random;
use Magento\Framework\Session\SessionManagerInterface;
use Magento\Framework\View\Asset\NotationResolver\Variable;
use Magento\Store\Model\StoreManagerInterface;
use Psr\Log\LoggerInterface;
use Magento\Email\Model\ResourceModel\TemplateFactory as ResourceModelFactory;

/**
 * This class is responsible for sending verification code on telephone number
 *
 * Class SmsSender
 */
class SmsSender implements SmsInterface
{
    /**
     * constants
     */
    const XML_REGISTRATION_TEMPLATE_PATH = 'eguanasms/templates/customer_register_sms';

    /**
     * @var SessionManagerInterface
     */
    private $sessionManager;

    /**
     * @var Curl
     */
    private $curl;

    /**
     * @var Data
     */
    private $data;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var TemplateFactory
     */
    private $templateFactory;

    /**
     * @var CountryCode
     */
    private $countryCode;

    /**
     * @var CollectionFactory
     */
    private $collectionFactory;

    /**
     * @var ResourceModelFactory
     */
    private $resourceModelFactory;

    /**
     * SmsSender constructor.
     * @param SessionManagerInterface $sessionManager
     * @param Curl $curl
     * @param Data $data
     * @param StoreManagerInterface $storeManager
     * @param LoggerInterface $logger
     * @param TemplateFactory $templateFactory
     * @param \Eguana\StoreSms\Model\CountryCode $countryCode
     * @param CollectionFactory $collectionFactory
     */
    public function __construct(
        SessionManagerInterface $sessionManager,
        Curl $curl,
        Data $data,
        StoreManagerInterface $storeManager,
        LoggerInterface $logger,
        TemplateFactory $templateFactory,
        CountryCode $countryCode,
        CollectionFactory $collectionFactory,
        ResourceModelFactory $resourceModelFactory
    ) {
        $this->sessionManager = $sessionManager;
        $this->curl = $curl;
        $this->data = $data;
        $this->storeManager = $storeManager;
        $this->logger = $logger;
        $this->templateFactory = $templateFactory;
        $this->countryCode = $countryCode;
        $this->collectionFactory = $collectionFactory;
        $this->resourceModelFactory = $resourceModelFactory;
    }

    /**
     * Send verification code to user number
     *
     * @api
     * @param int|string $number Users name.
     * @return bool|int|string
     */
    public function sendMessage($number)
    {
        return $this->setCode($number);
    }

    /**
     * This function is set verification code in session
     *
     * @param $number
     * @return bool
     */
    public function setCode($number, $customer = 'customer')
    {
        $message = '';
        $verificationCode = '';
        $phoneNumber = '';
        try {
            $verificationCode = Random::getRandomNumber(1000, 9999);
            $this->sessionManager->start();
            $this->sessionManager->setVerificationCode($verificationCode);
            $countryCode = $this->countryCode->getCountryCallCode();
            $store = $this->storeManager->getStore()->getId();
            $numberPrefix = $countryCode[$this->data->getCurrentCountry($store)]['code'];
            $phoneNumber = $this->getPhoneNumberWithCode($number, $numberPrefix);
            $this->sessionManager->setPhoneNumber($number);
            $template = $this->templateFactory->create();
            $template->setDesignConfig(['area' => 'frontend', 'store' => $store]);
            $storePhoneNumber = $this->data->getStorePhoneNumber($store);
            $storeName = $this->storeManager->getStore()->getName();
            $params = [
                'code'         => $verificationCode,
                'store_name'   => $storeName,
                'store_phone'  => $storePhoneNumber,
                'customer'     => $customer
            ];
            $message = $template->getTemplateContent(self::XML_REGISTRATION_TEMPLATE_PATH, $params);
        } catch (\Exception $e) {
            $this->logger->error($e->getMessage());
        }

        $this->sendMessageByApi($message, $phoneNumber);
        return $verificationCode;
    }

    /**
     * This function will return order template notification content
     *
     * @param $templatePath
     * @param $customer
     * @param $order
     * @return mixed
     */
    public function getOrderNotification($storeId, $templatePath, $customer, $order, $storeName, $storePhoneNumber)
    {
        $message = '';
        $templateModel = $this->templateFactory->create();
        $template = $this->resourceModelFactory->create();
        $params = [ 'customer'=> $customer,'order' => $order, 'store_name' => $storeName, 'store_phone' => $storePhoneNumber];
        try {
            $templateModel->setDesignConfig(['area' => 'frontend', 'store' => $storeId]);
            $templateModel->loadDefault($templatePath);
            $message = $templateModel->getProcessedTemplate($params);
        } catch (\Exception $e) {
            $template = $template->load($templateModel, $templatePath);
            $message = $templateModel->getProcessedTemplate($params);
        }
        return $message;
    }

    /**
     * send message by api
     * @param $message
     * @param $number
     * @param null $storeId
     * @return bool
     */
    public function sendMessageByApi($message, $number, $storeId = null)
    {
        $result = true;
        try {
            if ($storeId == null) {
                $storeId = $this->storeManager->getStore()->getId();
            }

            $apiUserName = $this->data->getApiCredentials('api_login', $storeId);
            $apiPassword = $this->data->getApiCredentials('api_password', $storeId);
            $sender = $this->data->getSender($storeId);
            $apiUrl = $this->data->getApiCredentials('api_url', $storeId);
            $authorizationKey = "Basic " . base64_encode($apiUserName . ":" . $apiPassword);
            $header = [
                "accept: application/json",
                "authorization: " . $authorizationKey,
                "content-type: application/json"
            ];
            $param = [
                'from' => $sender,
                'to' => $number,
                'text' => $message
            ];
            $this->curl->setOption(CURLOPT_RETURNTRANSFER, true);
            $this->curl->setOption(CURLOPT_HTTPHEADER, $header);
            $this->curl->setOption(CURLOPT_HEADER, false);
            $this->curl->setOption(CURLOPT_POST, true);
            $this->curl->setOption(CURLOPT_POSTFIELDS, json_encode($param));
            $this->curl->post($apiUrl, $param);
        } catch (\Exception $e) {
            $result = false;

            return $result;
        }

        return $result;
    }

    /**
     * This function will check if already a account created by phone number
     * @param $number
     * @param $isResetPage
     * @return bool
     */
    private function validatePhoneNumber($number, $isResetPage)
    {
        $result = true;
        try {
            $storeId = $this->storeManager->getStore()->getId();
            if ($this->data->getNumberValidationStatus($storeId) === '1') {
                /** @var \Magento\Customer\Model\ResourceModel\Customer\Collection $customerCollection */
                $customerCollection = $this->collectionFactory->create();
                $customerCollection->addAttributeToFilter('mobile_number', $number)->load();
                $customerHaveNumber = $customerCollection->getSize();

                //in reset page : phone number should exist
                if ($isResetPage && $customerHaveNumber == 0) {
                    $result = false;
                }

                //if register page : phone should not exist
                if (!$isResetPage && $customerHaveNumber > 0) {
                    $result = false;
                }
            }
        } catch (\Exception $exception) {
            $this->logger->error($exception->getMessage());
        }

        return $result;
    }

    /**
     * SHORT DESCRIPTION
     * LONG DESCRIPTION LINE BY LINE
     * @param $number
     * @return int
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function validateOldCustomerNumber($number)
    {
        /** @var \Magento\Customer\Model\ResourceModel\Customer\Collection $customerCollection */
        $customerCollection = $this->collectionFactory->create();
        $customerCollection->addAttributeToFilter('mobile_number', $number);
        $customerCollection->addAttributeToFilter('username', ['notnull' => true]);

        return $customerCollection->load()->getSize();
    }

    /**
     * This function will check validation and return phone number with country code
     * @param $TelephoneNumber
     * @param $countryCode
     * @return string
     */
    public function getPhoneNumberWithCode($TelephoneNumber, $countryCode)
    {
        $phoneNumber = '';
        $n = strlen($countryCode);
        $result = substr($TelephoneNumber, 0, $n);
        if ($result == $countryCode) {
            $phoneNumber = $TelephoneNumber;
        } elseif ($TelephoneNumber[0] == 0) {
            $phoneWithLeadingZero = ltrim($TelephoneNumber, '0');
            $phoneNumber = $countryCode . $phoneWithLeadingZero;
        } else {
            $phoneNumber = $countryCode . $TelephoneNumber;
        }

        return $phoneNumber;
    }
}
