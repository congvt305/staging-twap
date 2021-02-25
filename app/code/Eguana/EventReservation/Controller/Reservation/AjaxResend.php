<?php
/**
 * @author Eguana Team
 * @copyriht Copyright (c) 2020 Eguana {http://eguanacommerce.com}
 * Created by PhpStorm
 * User: raheel
 * Date: 24/2/21
 * Time: 9:08 PM
 */
namespace Eguana\EventReservation\Controller\Reservation;

use Eguana\EventReservation\Model\Service\SmsSender;
use Eguana\EventReservation\Model\Email\EmailSender;
use Eguana\EventReservation\Helper\ConfigData;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Controller\ResultInterface;
use Magento\Store\Model\StoreManagerInterface;
use Psr\Log\LoggerInterface;

/**
 * This class is used to resend the email and sms
 *
 * Class AjaxResend
 */
class AjaxResend extends Action
{
    /**
     * @var EmailSender
     */
    private $emailSender;

    /**
     * @var SmsSender
     */
    private $smsSender;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var ConfigData
     */
    private $configHelper;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @param Context $context
     * @param SmsSender $smsSender
     * @param ConfigData $configHelper
     * @param EmailSender $emailSender
     * @param LoggerInterface $logger
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        Context $context,
        SmsSender $smsSender,
        ConfigData $configHelper,
        EmailSender $emailSender,
        LoggerInterface $logger,
        StoreManagerInterface $storeManager
    ) {
        $this->logger = $logger;
        $this->smsSender = $smsSender;
        $this->emailSender = $emailSender;
        $this->storeManager = $storeManager;
        $this->configHelper = $configHelper;
        parent::__construct($context);
    }

    /**
     * This method is used to resend the sms and email to the customer
     *
     * @return ResponseInterface|ResultInterface
     */
    public function execute()
    {
        $response = [
            'success' => false,
            'resendMessage' => __('Email or SMS sending failed.')
        ];
        try {
            $reservationId = $this->getRequest()->getParam('reserved_id');
            $storeId = $this->storeManager->getStore()->getId();
            $this->smsSender->sendSms($reservationId);
            if ($this->configHelper->getCustomerEmailEnabled($storeId) == 1) {
                $this->emailSender->sendEmailToCustomer(
                    $reservationId,
                    'pending'
                );
            }
            if ($this->configHelper->getStaffEmailEnabled($storeId) == 1) {
                $this->emailSender->sendEmailToStaff(
                    $reservationId,
                    'pending'
                );
            }
            $response = [
                'success' => true,
                'resendMessage' => __('Successfully booked an event')
            ];
        } catch (\Exception $exception) {
            $this->logger->debug($exception->getMessage());
        }
        $resultJson = $this->resultFactory->create(ResultFactory::TYPE_JSON);
        return $resultJson->setData($response);
    }
}
