<?php
/**
 * Created by PhpStorm
 * User: abbas
 * Date: 20. 5. 25
 * Time: 오전 11:33
 *
 */

namespace Amore\CustomerRegistration\Controller\Verification;

use Amore\CustomerRegistration\Model\Verification;
use CJ\Sms\Api\Data\SmsHistoryInterface;
use CJ\Sms\Api\SmsHistoryRepositoryInterface;
use CJ\Sms\Model\Config;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\Result\Json;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\ResultInterface;
use Magento\Store\Model\StoreManagerInterface;

/**
 * To send the verification code to the customer
 * Class Code
 */
class Code extends Action
{
    /**
     * Json Factory
     *
     * @var JsonFactory
     */
    private $resultJsonFactory;

    /**
     * Request
     *
     * @var RequestInterface
     */
    private $request;

    /**
     * Verfication
     *
     * @var Verification
     */
    private $verification;


    /**
     * @var Config
     */
    private $smsConfig;

    /**
     * @var SmsHistoryRepositoryInterface
     */
    private $smsHistoryRepository;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * Constructor
     *
     * @param Context $context
     * @param JsonFactory $resultJsonFactory
     * @param Verification $verification
     * @param Config $smsConfig
     * @param SmsHistoryRepositoryInterface $smsHistoryRepository
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        Context $context,
        JsonFactory $resultJsonFactory,
        Verification $verification,
        Config $smsConfig,
        SmsHistoryRepositoryInterface $smsHistoryRepository,
        StoreManagerInterface $storeManager
    ) {
        $this->request = $context->getRequest();
        $this->resultJsonFactory = $resultJsonFactory;
        $this->verification = $verification;
        $this->smsConfig = $smsConfig;
        $this->smsHistoryRepository = $smsHistoryRepository;
        $this->storeManager = $storeManager;
        parent::__construct($context);
    }

    /**
     * To send the code
     * To send the verification code to the customer
     *
     * @return ResponseInterface|Json|ResultInterface
     */
    public function execute()
    {
        $result['send'] = false;
        $mobileNumber = $this->request->getParam('mobileNumber');
        $firstName = trim($this->request->getParam('firstName'));
        $lastName = trim($this->request->getParam('lastName'));
        /**
         *  Json result
         *
         * @var Json $jsonResult
         */
        $jsonResult = $this->resultJsonFactory->create();
        try {
            $isEnabledSmsVerification = $this->smsConfig->isEnabledSmsVerification();
            if ($isEnabledSmsVerification) {
                $limitSendSMS = $this->smsConfig->getLimitSendSmsPerDay();
                $currentStoreId = $this->storeManager->getStore()->getId();
                $collection = $this->smsHistoryRepository->getByPhoneNumber($mobileNumber, $currentStoreId);
                $item = $collection->getFirstItem();
                $currentLimit = $item->getLimitNumber();
                if ($item->getEntityId()) {
                    if ($currentLimit && $currentLimit >= $limitSendSMS) {
                        $result['message'] = __('Reached limit send SMS per day. Please try again tomorrow');
                        $jsonResult->setData($result);
                        return $jsonResult;
                    }
                    $item->setData(SmsHistoryInterface::LIMIT_NUMBER, $currentLimit + 1);
                } else {
                    $item->setData(SmsHistoryInterface::STORE_ID, $currentStoreId);
                    $item->setData(SmsHistoryInterface::TELEPHONE, $mobileNumber);
                    $item->setData(SmsHistoryInterface::LIMIT_NUMBER, 1);
                }
            }
            $sendVerificationCodeResult = $this->verification
                ->sendVerificationCode($mobileNumber, $firstName, $lastName);

            if ($sendVerificationCodeResult === true) {
                $result['send'] = $sendVerificationCodeResult;
                $result['message'] = __('Verification code has been sent to your mobile');
            } else {
                $result['message'] = $sendVerificationCodeResult;
            }
            if ($isEnabledSmsVerification) {
                $this->smsHistoryRepository->save($item);
            }
        } catch (\Exception $e) {
            $result['message'] = $e->getMessage();
        }
        $jsonResult->setData($result);
        return $jsonResult;
    }
}
