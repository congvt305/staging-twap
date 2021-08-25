<?php
/**
 * @author Eguana Team
 * @copyriht Copyright (c) 2020 Eguana {http://eguanacommerce.com}
 * Created by PhpStorm
 * User: arslan
 * Date: 10/11/20
 * Time: 10:08 PM
 */
namespace Eguana\Redemption\Controller\Details;

use Eguana\Redemption\Api\CounterRepositoryInterface;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Eguana\Redemption\Model\Service\EmailSender;
use Magento\Framework\Controller\ResultFactory;
use Eguana\Redemption\Model\Service\SmsSender;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Class Resend
 * This class is used to resend the email and sms
 */
class Resend extends Action
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
     * @var CounterRepositoryInterface
     */
    private $counterRepository;

    /**
     * Index constructor.
     *
     * @param Context $context
     * @param EmailSender $emailSender
     * @param SmsSender $smsSender
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        Context $context,
        EmailSender $emailSender,
        SmsSender $smsSender,
        StoreManagerInterface $storeManager,
        CounterRepositoryInterface $counterRepository
    ) {
        $this->emailSender = $emailSender;
        $this->smsSender = $smsSender;
        $this->storeManager = $storeManager;
        $this->counterRepository = $counterRepository;
        parent::__construct($context);
    }

    /**
     * Dispatch request
     * This method is used to resend the sms and email to the customer
     */
    public function execute()
    {
        $resultJson = $this->resultFactory->create(ResultFactory::TYPE_JSON);
        $counterId = $this->getRequest()->getParam('counter_id');

        if (!$counterId) {
            $this->messageManager->addErrorMessage(
                __('Counter %1 is not available', $counterId)
            );
            $resultJson->setData(
                [
                    "resendmessage" => '',
                    "success" => false,
                    'redirectUrl' => '/'
                ]
            );
            return $resultJson;
        }
        $defaultStoreId = $this->storeManager->getStore()->getId();
        $counter = $this->getCounter($counterId);
        if (!$counter) {
            $this->messageManager->addErrorMessage(
                __('Counter %1 is not available', $counterId)
            );
            $resultJson->setData(
                [
                    "resendmessage" => '',
                    "success" => false,
                    'redirectUrl' => '/'
                ]
            );
            return $resultJson;
        }
        if ($this->emailSender->getRegistrationEmailEnableValue($defaultStoreId) == 1) {
            try {
                $this->emailSender->sendEmail($counterId);
                $this->smsSender->sendSms($counterId);
            } catch (\Exception $e) {
                $this->messageManager->addErrorMessage(
                    __('Email or SMS sending failed.')
                );
                $resultJson->setData(
                    [
                        "resendmessage" => '',
                        "success" => false,
                        'redirectUrl' => $this->_redirect->getRefererUrl()
                    ]
                );
                return $resultJson;
            }
        }
        $resultJson->setData(
            [
                "resendmessage" => __('You have successfully applied for redemption, please check your email and newsletter.'),
                "success" => true
            ]
        );
        return $resultJson;
    }

    /**
     * @param $counterId
     * @return \Eguana\Redemption\Api\Data\CounterInterface
     */
    public function getCounter($counterId)
    {
        return $this->counterRepository->getById($counterId);
    }
}
