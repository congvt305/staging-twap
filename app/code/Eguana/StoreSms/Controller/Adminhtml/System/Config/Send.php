<?php

namespace Eguana\StoreSms\Controller\Adminhtml\System\Config;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Eguana\StoreSms\Model\SmsSender;
use Eguana\StoreSms\Helper\Data;
use Psr\Log\LoggerInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\Controller\Result\RawFactory;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\Result\Raw;
use Magento\Framework\Controller\ResultInterface;

/**
 * This class is responsible for sending test message
 *
 * Class Send
 */
class Send extends Action
{
    /**
     * @var Data
     */
    private $data;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var SmsSender
     */
    private $sendSms;

    /**
     * @var Context
     */
    private $context;

    /**
     * @var ManagerInterface
     */
    protected $messageManager;

    /**
     * @var RawFactory
     */
    protected $rawFactory;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * Send constructor.
     * @param Context $context
     * @param SmsSender $sendSms
     * @param StoreManagerInterface $storeManager
     * @param Data $data
     * @param ManagerInterface $messageManager
     * @param RawFactory $rawFactory
     * @param LoggerInterface $logger
     */
    public function __construct(
        Context $context,
        SmsSender $sendSms,
        StoreManagerInterface $storeManager,
        Data $data,
        ManagerInterface $messageManager,
        RawFactory $rawFactory,
        LoggerInterface $logger
    ) {
        $this->sendSms = $sendSms;
        $this->storeManager = $storeManager;
        $this->data = $data;
        $this->context = $context;
        $this->messageManager = $messageManager;
        $this->rawFactory = $rawFactory;
        $this->logger = $logger;
        parent::__construct($context);
    }

    /**
     * setting params for test sms
     * @return ResponseInterface|Raw|ResultInterface
     */
    public function execute()
    {
        $storeId = 0; //default store
        if ($this->_request->getParam('store')) {
            $storeId = $this->_request->getParam('store');
        }
        try {
            if ($this->_request->getParam('website')) {
                $websiteId = $this->_request->getParam('website');
                $storeIds = $this->storeManager->getWebsite($websiteId)->getStoreIds();
                $first_key = array_key_first($storeIds);
                $storeId = $storeIds[$first_key];
            }
        } catch (\Exception $e) {
            $this->logger->error($e->getMessage());
        }
        try {
            $isSmsActive = $this->data->getActivation($storeId);
        } catch (\Exception $e) {
            $this->logger->error($e->getMessage());
        }
        $telephoneNumber = $this->context->getRequest()->getParam('number');
        $message = $this->context->getRequest()->getParam('message');
        $result = $this->rawFactory->create();
        $redirectUrl =$result->setContents($this->context->getRedirect()->getRefererUrl());

        if ($telephoneNumber === '') {
            $this->messageManager->addErrorMessage(__('Please Test Enter phone Number'));

            return $redirectUrl;
        }
        if ($message === '') {
            $this->messageManager->addErrorMessage(__('Please Enter Test text message'));

            return $redirectUrl;
        }
        if ($isSmsActive) {
            $this->sendTestMessage($message, $telephoneNumber, $storeId);
            return $redirectUrl;
        } else {
            $this->messageManager->addErrorMessage(__('Please enable extension'));
            return $redirectUrl;
        }
    }

    /**
     * Send Test message
     *
     * @param $message
     * @param $telephoneNumber
     */
    public function sendTestMessage($message, $telephoneNumber, $storeId)
    {
        $isTestSmsSent = $this->sendSms->sendMessageByApi($message, $telephoneNumber, $storeId);

        if ($isTestSmsSent) {
            $this->messageManager->addSuccessMessage(__('Test Sms has been sent on Your number'));
        } else {
            $this->messageManager->addErrorMessage(__('Please enter valid Api credentials'));
        }
    }
}
