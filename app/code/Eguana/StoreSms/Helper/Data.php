<?php
namespace Eguana\StoreSms\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Store\Model\ScopeInterface;
use Magento\Framework\Encryption\EncryptorInterface;
use Magento\Framework\App\Helper\Context;
use Psr\Log\LoggerInterface;

/**
 * This class is used for StoreSms configuration data
 *
 * Class Data
 *
 */
class Data extends AbstractHelper
{
    /**
     * store constants
     */
    const XML_PATH_MODULE_ACTIVATION = 'eguanasms/general/active';
    const XML_PATH_API_CREDENTIAL = 'eguanasms/general/';
    const XML_PATH_API_SENDER = 'eguanasms/general/sender';
    const XML_PATH_ORDER_STATUS = 'eguanasms/templates/order_status_';
    const XML_PATH_ORDER_TEMPLATE = 'eguanasms/templates/order_status_';
    const XML_PATH_FOR_REGISTRATION = 'eguanasms/templates/registration_verification_active';
    const XML_PATH_FOR_REGISTRATION_TEMPLATE = 'eguanasms/templates/customer_register_sms';
    const XML_PATH_FOR_WELCOME_SMS = 'eguanasms/templates/registration_welcome_active';
    const XML_PATH_FOR_NUMBER_ACTIVATION = 'eguanasms/general/mobile_number_validation_active';
    const XML_PATH_FOR_REVERSE_NAME_FORMAT = 'eguanasms/general/name_format';
    const XML_PATH_FOR_DEFAULT_COUNTRY = 'general/country/default';
    const XML_PATH_FOR_COUNTRY_CODE = 'eguanasms/general/country_code';
    const XML_PATH_STORE_PHONE = 'general/store_information/phone';
    const XML_PATH_STORE_NAME = 'general/store_information/name';

    /**
     * @var EncryptorInterface
     */
    private $encryptorInterface;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * Data constructor.
     * @param Context $context
     * @param EncryptorInterface $encryptorInterface
     * @param LoggerInterface $logger
     */
    public function __construct(
        EncryptorInterface $encryptorInterface,
        Context $context,
        LoggerInterface $logger
    ) {
        parent::__construct($context);
        $this->encryptorInterface = $encryptorInterface;
        $this->logger = $logger;
    }

    /**
     * Get StoreSms activation status
     *
     * @param null $scopeCode
     * @return mixed
     */
    public function getActivation($scopeCode = null)
    {
        $result = '';
        try {
            $result = $this->scopeConfig->getValue(
                self::XML_PATH_MODULE_ACTIVATION,
                ScopeInterface::SCOPE_STORE,
                $scopeCode
            );
        } catch (\Exception $e) {

            $this->logger->error($e->getMessage());
        }

        return $result;
    }

    /**
     * Get StoreSms Credentials
     *
     * @param $field
     * @param null $scopeCode
     * @return mixed
     */
    public function getApiCredentials($field, $scopeCode = null)
    {
        $result = $this->scopeConfig->getValue(
            self::XML_PATH_API_CREDENTIAL . $field,
            ScopeInterface::SCOPE_STORE,
            $scopeCode
        );
        return $field !== 'api_password' ? $result : $this->encryptorInterface->decrypt($result);
    }

    /**
     * get status of sending notification for order status Pending
     *
     * @param $status
     * @param null $scopeCode
     * @return mixed
     */
    public function getOrderStatus($status, $scopeCode = null)
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_ORDER_STATUS . $status . '_active',
            ScopeInterface::SCOPE_STORE,
            $scopeCode
        );
    }

    /**
     * This function will get  SMS template selected template config value
     *
     * @param $status
     * @return string
     */
    public function getTemplateIdentifer($status, $scopeCode)
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_ORDER_TEMPLATE . $status,
            ScopeInterface::SCOPE_STORE,
            $scopeCode
        );
    }
    /**
     * This function will get  SMS template selected template config value
     *
     * @param $status
     * @return string
     */
    public function getTemplateIdentiferRegistration($scopeCode)
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_FOR_REGISTRATION_TEMPLATE,
            ScopeInterface::SCOPE_STORE,
            $scopeCode
        );
    }

    /**
     * This function will get value if sms verification activation
     *
     * @param null $scopeCode
     * @return mixed
     */
    public function getVerificationActivation($scopeCode = null)
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_FOR_REGISTRATION,
            ScopeInterface::SCOPE_STORE,
            $scopeCode
        );
    }

    /**
     * This function get test message data to send test sms
     *
     * @param $field
     * @param null $scopeCode
     * @return mixed
     */
    public function getTestSmsData($field, $scopeCode = null)
    {
        return $this->scopeConfig->getValue(
            self:: XML_PATH_FOR_TEST_SMS . $field,
            ScopeInterface::SCOPE_STORE,
            $scopeCode
        );
    }

    /**
     * get default country
     * @param null $scopeCode
     * @return mixed
     */
    public function getCurrentCountry($scopeCode = null)
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_FOR_DEFAULT_COUNTRY,
            ScopeInterface::SCOPE_STORE,
            $scopeCode
        );
    }

    /**
     * get country code
     * @param null $scopeCode
     * @return integer
     */
    public function getCountryCode($scopeCode = null)
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_FOR_COUNTRY_CODE,
            ScopeInterface::SCOPE_STORE,
            $scopeCode
        );
    }

    /**
     * This function will get unique mobile number validation status on or off by admin
     * @param null $scopeCode
     * @return mixed
     */
    public function getNumberValidationStatus($scopeCode = null)
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_FOR_NUMBER_ACTIVATION,
            ScopeInterface::SCOPE_STORE,
            $scopeCode
        );
    }

    /**
     * This function will get the activation status for the send welcome message
     * @param null $scopeCode
     * @return mixed
     */
    public function getWelcomeSmsActive($scopeCode = null)
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_FOR_WELCOME_SMS,
            ScopeInterface::SCOPE_STORE,
            $scopeCode
        );
    }

    /**
     * This function is use to get the sender for the api
     * @param null $scopeCode
     * @return mixed
     */
    public function getSender($scopeCode = null)
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_API_SENDER,
            ScopeInterface::SCOPE_STORE,
            $scopeCode
        );
    }

    /**
     * This function is use to get store phone number
     * @param null $scopeCode
     * @return string | void
     */
    public function getStorePhoneNumber($scopeCode = null)
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_STORE_PHONE,
            ScopeInterface::SCOPE_STORE,
            $scopeCode
        );
    }

    /**
     * This function is use to get store name
     * @param null $scopeCode
     * @return mixed
     */
    public function getStoreName($scopeCode = null)
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_STORE_NAME,
            ScopeInterface::SCOPE_STORE,
            $scopeCode
        );
    }


    /**
     * This function will check that either Reverse Name format is active or not
     * @param null $scopeCode
     * @return mixed
     */
    public function getReverseNameFormat($scopeCode = null)
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_FOR_REVERSE_NAME_FORMAT,
            ScopeInterface::SCOPE_STORE,
            $scopeCode
        );
    }
}
