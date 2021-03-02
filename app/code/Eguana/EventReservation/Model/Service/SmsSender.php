<?php
/**
 * @author Eguana Team
 * @copyriht Copyright (c) 2020 Eguana {http://eguanacommerce.com}
 * Created by PhpStorm
 * User: raheel
 * Date: 24/2/21
 * Time: 11:05 PM
 */
namespace Eguana\EventReservation\Model\Service;

use Eguana\EventReservation\Api\Data\EventInterface;
use Eguana\EventReservation\Api\EventRepositoryInterface;
use Eguana\EventReservation\Api\UserReservationRepositoryInterface;
use Eguana\EventReservation\Helper\ConfigData;
use Eguana\EventReservation\Model\Email\EmailSender;
use Eguana\StoreSms\Api\SmsManagementInterface;
use Magento\Email\Model\Template;
use Magento\Email\Model\TemplateFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Store\Model\StoreManagerInterface;
use Psr\Log\LoggerInterface;

/**
 * Class is used to send the sms to customers
 *
 * Class SmsSender
 */
class SmsSender
{
    /**
     * @var SmsManagementInterface
     */
    private $smsManagement;

    /**
     * @var ConfigData
     */
    private $configHelper;

    /**
     * @var TemplateFactory
     */
    private $templateFactory;

    /**
     * @var UserReservationRepositoryInterface
     */
    private $userReservationRepository;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var EventRepositoryInterface
     */
    private $eventRepository;

    /**
     * @var EmailSender
     */
    private $emailSender;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @param ConfigData $configHelper
     * @param EmailSender $emailSender
     * @param LoggerInterface $logger
     * @param TemplateFactory $templateFactory
     * @param StoreManagerInterface $storeManager
     * @param SmsManagementInterface $smsManagement
     * @param EventRepositoryInterface $eventRepository
     * @param UserReservationRepositoryInterface $userReservationRepository
     */
    public function __construct(
        ConfigData $configHelper,
        EmailSender $emailSender,
        LoggerInterface $logger,
        TemplateFactory $templateFactory,
        StoreManagerInterface $storeManager,
        SmsManagementInterface $smsManagement,
        EventRepositoryInterface $eventRepository,
        UserReservationRepositoryInterface $userReservationRepository
    ) {
        $this->logger = $logger;
        $this->emailSender = $emailSender;
        $this->configHelper = $configHelper;
        $this->storeManager = $storeManager;
        $this->smsManagement = $smsManagement;
        $this->templateFactory = $templateFactory;
        $this->eventRepository = $eventRepository;
        $this->userReservationRepository = $userReservationRepository;
    }

    /**
     * To send the sms when event reserved by customer
     *
     * @param $userReserveId
     */
    public function sendSms($userReserveId)
    {
        try {
            $storeId = $this->storeManager->getStore()->getId();
            if ($this->configHelper->getSendSmsEnable($storeId)) {
                $number = $this->userReservationRepository->getById($userReserveId)->getPhone();
                $message = $this->getMessage($userReserveId, $storeId);
                if ($number && $message) {
                    $this->smsManagement->sendMessage($number, $message, $storeId);
                }
            }
        } catch (NoSuchEntityException $exception) {
            $this->logger->critical($exception->getMessage());
        } catch (LocalizedException $exception) {
            $this->logger->critical($exception->getMessage());
        } catch (\Exception $exception) {
            $this->logger->critical($exception->getMessage());
            return;
        }
    }

    /**
     * Get message template
     *
     * @param $userReserveId
     * @param $storeId
     * @return string
     */
    private function getMessage($userReserveId, $storeId)
    {
        try {
            $reservation = $this->userReservationRepository->getById($userReserveId);
            $params['customer'] = $reservation->getName();
            $websiteId = $this->storeManager->getStore()->getWebsiteId();
            $websiteCode = $this->storeManager->getWebsite($websiteId)->getCode();
            $event = $this->getEvent($reservation->getEventId());
            if ($websiteCode == 'tw_lageige_website') {
                $storeName = __('Laneige');
            } else {
                $storeName = __('Sulwhasoo');
            }
            $params['storeEvent'] = $storeName . ' ＜' . $event->getTitle() . '＞';
            $token = $reservation->getAuthToken() . '_C';
            $params['confirmLink'] = $this->emailSender->getConfirmLink($userReserveId, $token);
            $params['cancelLink'] = $this->emailSender->getCancelLink($userReserveId, $token);

            /** @var Template $templateModel */
            $templateModel = $this->templateFactory->create();
            $templateModel->setDesignConfig(
                ['area' => 'frontend', 'store' => $storeId]
            );
            $templateModel->loadByConfigPath(ConfigData::SMS_TEMPLATE_PATH);

            return $templateModel->getProcessedTemplate($params);
        } catch (\Exception $exception) {
            $this->logger->error($exception->getMessage());
            return '';
        }
    }

    /**
     * Get event details
     *
     * @param $eventId
     * @return EventInterface
     * @throws NoSuchEntityException
     */
    private function getEvent($eventId)
    {
        try {
            $event = $this->eventRepository->getById($eventId);
        } catch (\Exception $exception) {
            throw new NoSuchEntityException(__('The Event with the "%1" ID doesn\'t exist.', $eventId));
        }
        return $event;
    }
}
