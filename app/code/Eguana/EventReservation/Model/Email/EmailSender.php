<?php
/**
 * @author Eguana Team
 * @copyriht Copyright (c) 2020 Eguana {http://eguanacommerce.com}
 * Created by PhpStorm
 * User: raheel
 * Date: 6/11/20
 * Time: 12:53 PM
 */
declare(strict_types=1);

namespace Eguana\EventReservation\Model\Email;

use Eguana\EventReservation\Api\Data\EventInterface;
use Eguana\EventReservation\Api\CounterRepositoryInterface;
use Eguana\EventReservation\Api\Data\UserReservationInterface;
use Eguana\EventReservation\Api\EventRepositoryInterface;
use Eguana\EventReservation\Api\UserReservationRepositoryInterface;
use Eguana\EventReservation\Helper\ConfigData;
use Eguana\StoreLocator\Api\StoreInfoRepositoryInterface;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Area;
use Magento\Framework\App\Request\DataPersistorInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Mail\Template\TransportBuilder;
use Magento\Framework\Translate\Inline\StateInterface;
use Magento\Framework\Url;
use Magento\Store\Model\StoreManagerInterface;
use Psr\Log\LoggerInterface;

/**
 * Send Email to customer & staff admin
 *
 * Class EmailSender
 */
class EmailSender
{
    /**#@+
     * Constant for link.
     */
    const EMAIL_ACTION_LINK = 'event/reservation/emailaction/';
    /**#@-*/

    /**
     * @var ConfigData
     */
    private $helper;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var StateInterface
     */
    private $inlineTranslation;

    /**
     * @var TransportBuilder
     */
    private $transportBuilder;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var EventRepositoryInterface
     */
    private $eventRepository;

    /**
     * @var CounterRepositoryInterface
     */
    private $counterRepository;

    /**
     * @var UserReservationRepositoryInterface
     */
    private $userReservationRepository;

    /**
     * @var StoreInfoRepositoryInterface
     */
    private $storeInfoRepository;

    /**
     * @var DataPersistorInterface
     */
    private $dataPersistor;

    /**
     * @var Url
     */
    private $url;

    /**
     * @param Url $url
     * @param ConfigData $helper
     * @param LoggerInterface $logger
     * @param StateInterface $inlineTranslation
     * @param TransportBuilder $transportBuilder
     * @param StoreManagerInterface $storeManager
     * @param DataPersistorInterface $dataPersistor
     * @param EventRepositoryInterface $eventRepository
     * @param CounterRepositoryInterface $counterRepository
     * @param UserReservationRepositoryInterface $userReservationRepository
     * @param StoreInfoRepositoryInterface $storeInfoRepository
     */
    public function __construct(
        Url $url,
        ConfigData $helper,
        LoggerInterface $logger,
        StateInterface $inlineTranslation,
        TransportBuilder $transportBuilder,
        StoreManagerInterface $storeManager,
        DataPersistorInterface $dataPersistor,
        EventRepositoryInterface $eventRepository,
        CounterRepositoryInterface $counterRepository,
        UserReservationRepositoryInterface $userReservationRepository,
        StoreInfoRepositoryInterface $storeInfoRepository
    ) {
        $this->url = $url;
        $this->helper = $helper;
        $this->logger = $logger;
        $this->inlineTranslation = $inlineTranslation;
        $this->transportBuilder = $transportBuilder;
        $this->storeManager = $storeManager;
        $this->dataPersistor = $dataPersistor;
        $this->eventRepository = $eventRepository;
        $this->counterRepository = $counterRepository;
        $this->userReservationRepository = $userReservationRepository;
        $this->storeInfoRepository = $storeInfoRepository;
    }

    /**
     * Send Email to the customer on reservation
     *
     * @param $userReserveId
     * @param $callFor
     */
    public function sendEmailToCustomer($userReserveId, $callFor)
    {
        try {
            $reservationDetail = $this->getReservationDetail($userReserveId);
            $eventId = $reservationDetail->getEventId();
            $event = $this->getEvent($eventId);
            $storeId = $event->getStoreId();
            $storeId = ($storeId && is_array($storeId)) ? $storeId[0] : $storeId;
            $storeId = $storeId ? $storeId : 0;
            $templateOptions = [
                'area' => Area::AREA_FRONTEND,
                'store' => $storeId
            ];
            $storeName = $this->storeManager->getStore($storeId)->getName();

            $websiteId = $this->storeManager->getStore($storeId)->getWebsiteId();
            $websiteCode = $this->storeManager->getWebsite($websiteId)->getCode();
            if ($websiteCode == 'tw_lageige_website') {
                $siteName = __('Laneige');
            } else {
                $siteName = __('Sulwhasoo');
            }
            $storeEventName = $siteName . ' ＜' . $event->getTitle() . '＞';

            $eventTime = $reservationDetail->getDate() . ' ' . $reservationDetail->getTimeSlot();
            $templateVars = [
                'customer_name' => $reservationDetail->getName(),
                'event_title' => $event->getTitle(),
                'event_time' => $eventTime,
                'physical_store' => $this->getStoreInfoTitle($reservationDetail->getOfflineStoreId()),
                'store_name' => $storeName,
                'store_event_name' => $storeEventName
            ];
            if ($callFor == 'pending') {
                $token = $reservationDetail->getAuthToken() . '_C';
                $templateVars['confirm_link'] = $this->getConfirmLink($userReserveId, $token);
                $templateVars['cancel_link'] = $this->getCancelLink($userReserveId, $token);
                $templateId = $this->helper->getEmailGroupConfigValue(
                    'customer_email_pending',
                    $storeId
                );
            } elseif ($callFor == 'confirm') {
                $templateId = $this->helper->getEmailGroupConfigValue(
                    'customer_email_confirmed',
                    $storeId
                );
            } else {
                $templateId = $this->helper->getEmailGroupConfigValue(
                    'customer_email_canceled',
                    $storeId
                );
            }
            $from = ['email' => $this->getSenderEmail($storeId), 'name' => $this->getEmailSenderName($storeId)];
            $this->inlineTranslation->suspend();
            $to = $reservationDetail->getEmail();
            $transport = $this->transportBuilder
                ->setTemplateIdentifier($templateId)
                ->setTemplateOptions($templateOptions)
                ->setTemplateVars($templateVars)
                ->setFrom($from)
                ->addTo($to)
                ->getTransport();

            $transport->sendMessage();
            $this->inlineTranslation->resume();
        } catch (LocalizedException $e) {
            $this->logger->error($e->getMessage());
        }
    }

    /**
     * Send Email to the staff on reservation
     *
     * @param $userReserveId
     * @param $callFor
     */
    public function sendEmailToStaff($userReserveId, $callFor)
    {
        try {
            $reservationDetail = $this->getReservationDetail($userReserveId);
            $eventId    = $reservationDetail->getEventId();
            $counterId  = $reservationDetail->getCounterId();
            $event = $this->getEvent($eventId);
            $storeId = $event->getStoreId();
            $storeId = ($storeId && is_array($storeId)) ? $storeId[0] : $storeId;
            $storeId = $storeId ? $storeId : 0;
            $templateOptions = [
                'area' => Area::AREA_FRONTEND,
                'store' => $storeId
            ];

            $eventTime = $reservationDetail->getDate() . ' ' . $reservationDetail->getTimeSlot();
            $templateVars = [
                'customer_name' => $reservationDetail->getName(),
                'event_title' => $event->getTitle(),
                'event_time' => $eventTime,
                'physical_store' => $this->getStoreInfoTitle($reservationDetail->getOfflineStoreId())
            ];
            if ($callFor == 'pending') {
                $token = $reservationDetail->getAuthToken() . '_SA';
                $templateVars['confirm_link'] = $this->getConfirmLink($userReserveId, $token);
                $templateVars['cancel_link'] = $this->getCancelLink($userReserveId, $token);
                $templateId = $this->helper->getEmailGroupConfigValue(
                    'staff_email_pending',
                    $storeId
                );
            } elseif ($callFor == 'confirm') {
                $templateId = $this->helper->getEmailGroupConfigValue(
                    'staff_email_confirmed',
                    $storeId
                );
            } else {
                $templateId = $this->helper->getEmailGroupConfigValue(
                    'staff_email_canceled',
                    $storeId
                );
            }
            $from = ['email' => $this->getSenderEmail($storeId), 'name' => $this->getEmailSenderName($storeId)];
            $this->inlineTranslation->suspend();
            $to = $this->getStaffUserEmail($counterId, $storeId);
            $transport = $this->transportBuilder
                ->setTemplateIdentifier($templateId)
                ->setTemplateOptions($templateOptions)
                ->setTemplateVars($templateVars)
                ->setFrom($from)
                ->addTo($to)
                ->getTransport();

            $transport->sendMessage();
            $this->inlineTranslation->resume();
        } catch (LocalizedException $e) {
            $this->logger->error($e->getMessage());
        }
    }

    /**
     * Get reservation by id
     *
     * @param $userReserveId
     * @return UserReservationInterface
     * @throws NoSuchEntityException
     */
    private function getReservationDetail($userReserveId)
    {
        return $this->userReservationRepository->getById($userReserveId);
    }

    /**
     * Get id of Email Sender from configuration
     *
     * @param $storeId
     * @return mixed
     */
    private function getEmailSenderId($storeId)
    {
        return $this->helper->getEmailSenderId($storeId);
    }

    /**
     * Get Email of Sender from configuration
     *
     * @param $storeId
     * @return mixed
     */
    private function getSenderEmail($storeId)
    {
        return $this->helper->getConfigValue(
            'trans_email/ident_' . $this->getEmailSenderId($storeId) . '/email',
            $storeId
        );
    }

    /**
     * Get name of sender from configuration
     *
     * @param $storeId
     * @return mixed
     */
    private function getEmailSenderName($storeId)
    {
        return $this->helper->getConfigValue(
            'trans_email/ident_' . $this->getEmailSenderId($storeId) . '/name',
            $storeId
        );
    }

    /**
     * Get event data
     *
     * @param $eventId
     * @return int|mixed
     */
    private function getEventStoreId($eventId)
    {
        $storeId = 0;
        try {
            $event = $this->eventRepository->getById($eventId);
        } catch (\Exception $e) {
            $this->logger->info($e->getMessage());
        }

        if (isset($event)) {
            $storeId = $event->getStoreId();
            $storeId = is_array($storeId) ? $storeId[0] : $storeId;
        }

        return $storeId ? $storeId : 0;
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
        } catch (\Exception $e) {
            throw new NoSuchEntityException(__('The Event with the "%1" ID doesn\'t exist.', $eventId));
        }
        return $event;
    }

    /**
     * Get confirm reservation link
     *
     * @param $userReserveId
     * @param $token
     * @return string
     */
    public function getConfirmLink($userReserveId, $token)
    {
        $link = '';
        $eventId = $this->dataPersistor->get('current_event_id');
        if ($eventId) {
            $storeId = $this->getEventStoreId($eventId);
        }

        try {
            if (isset($storeId)) {
                $link = $this->url->getUrl(
                    self::EMAIL_ACTION_LINK . 'id/' . $userReserveId . '/call/confirm/token/' . $token,
                    [
                        '_scope' => $storeId,
                        '_nosid' => true
                    ]
                );
            } else {
                $link = $this->storeManager->getStore()->getUrl(
                    self::EMAIL_ACTION_LINK,
                    ['id' => $userReserveId, 'call' => 'confirm', 'token' => $token]
                );
            }
        } catch (\Exception $e) {
            $this->logger->info($e->getMessage());
        }
        return $link;
    }

    /**
     * Get confirm reservation link
     *
     * @param $userReserveId
     * @param $token
     * @return string
     */
    public function getCancelLink($userReserveId, $token)
    {
        $link = '';
        $eventId = $this->dataPersistor->get('current_event_id');
        if ($eventId) {
            $storeId = $this->getEventStoreId($eventId);
        }

        try {
            if (isset($storeId)) {
                $link = $this->url->getUrl(
                    self::EMAIL_ACTION_LINK . 'id/' . $userReserveId . '/call/cancel/token/' . $token,
                    [
                        '_scope' => $storeId,
                        '_nosid' => true
                    ]
                );
            } else {
                $link = $this->storeManager->getStore()->getUrl(
                    self::EMAIL_ACTION_LINK,
                    ['id' => $userReserveId, 'call' => 'cancel', 'token' => $token]
                );
            }
        } catch (\Exception $e) {
            $this->logger->info($e->getMessage());
        }
        return $link;
    }

    /**
     * Get store info title
     *
     * @param $id
     * @return string
     */
    private function getStoreInfoTitle($id)
    {
        $storeInfo = $this->storeInfoRepository->getById($id);
        return ($storeInfo) ? $storeInfo->getTitle() : '';
    }

    /**
     * Get staff user email
     *
     * @param $counterId
     * @param $storeId
     * @return mixed|string|null
     */
    private function getStaffUserEmail($counterId, $storeId)
    {
        $counter = $this->counterRepository->getById($counterId);
        $email = ($counter) ? $counter->getStaffEmail() : '';

        if (empty($email)) {
            $email = $this->helper->getStaffEmail($storeId);
        }

        return $email;
    }
}
