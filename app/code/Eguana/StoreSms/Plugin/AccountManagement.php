<?php
namespace Eguana\StoreSms\Plugin;

use Magento\Framework\Session\SessionManagerInterface;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Framework\Exception\InputException;
use Magento\Framework\App\RequestInterface;
use Eguana\StoreSms\Helper\Data;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Customer\Model\AccountManagement as AccountManagementPlugin;
use Psr\Log\LoggerInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Eguana\StoreSms\Model\CountryCode;

/**
 * This class is responsible for validate verification code
 *
 * Class AccountManagement
 */
class AccountManagement
{
    /**
     * @var SessionManagerInterface
     *
     */
    private $sessionManager;

    /**
     * @var RequestInterface
     */
    private $requestInterface;

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
    private $loggerInterface;

    /**
     * @var CountryCode
     */
    private $countryCode;

    /**
     * AccountManagement constructor.
     * @param SessionManagerInterface $sessionManager
     * @param RequestInterface $requestInterface
     * @param Data $data
     * @param StoreManagerInterface $storeManager
     * @param LoggerInterface $loggerInterface
     * @param CountryCode $countryCode
     */
    public function __construct(
        SessionManagerInterface $sessionManager,
        RequestInterface $requestInterface,
        Data $data,
        StoreManagerInterface $storeManager,
        LoggerInterface $loggerInterface,
        CountryCode $countryCode
    ) {
        $this->sessionManager   = $sessionManager;
        $this->requestInterface = $requestInterface;
        $this->data             = $data;
        $this->storeManager     = $storeManager;
        $this->loggerInterface  = $loggerInterface;
        $this->countryCode  = $countryCode;
    }

    /**
     * This Function is responsible for verification code on registration
     *
     * @param AccountManagementPlugin $subject
     * @param CustomerInterface $customer
     * @param null $password
     * @param string $redirectUrl
     * @return array
     * @throws InputException
     */
    public function beforeCreateAccount(
        AccountManagementPlugin $subject,
        CustomerInterface $customer,
        $password = null,
        $redirectUrl = ''
    ) {
        $storeCode = '';
        try {
            $storeId = $this->storeManager->getStore()->getId();
        } catch (NoSuchEntityException $exception) {
            $this->loggerInterface->debug($exception->getMessage());
        }
        if ($this->data->getActivation($storeId) && $this->data->getVerificationActivation($storeId)) {
            $this->checkValidationCode();
        }

        return [$customer, $password, $redirectUrl];
    }

    /**
     * Check verification validation code
     *
     * @throws InputException
     */
    private function checkValidationCode()
    {
        $sessionCode = $this->sessionManager->getVerificationCode();
        $sessionNumber = $this->sessionManager->getPhoneNumber();
        $formCode = $this->requestInterface->getParam('smscode');
        $phoneNumber = $this->requestInterface->getParam('mobile_number');
        if ($phoneNumber[0] == 0) {
            $phoneNumber = ltrim($phoneNumber, '0');
        }
        if ($sessionNumber != $phoneNumber || $sessionCode != (int)$formCode) {
            throw new InputException(__('Please Enter correct verification code'));
        }
    }
}
