<?php
/**
 * @author Eguana Team
 * @copyriht Copyright (c) 2020 Eguana {http://eguanacommerce.com}
 * Created by PhpStorm
 * User: raheel
 * Date: 5/11/20
 * Time: 12:10 PM
 */
namespace Eguana\EventReservation\Controller\Reservation;

use Eguana\EventReservation\Api\EventRepositoryInterface;
use Eguana\EventReservation\Api\UserReservationRepositoryInterface;
use Eguana\EventReservation\Helper\ConfigData;
use Eguana\EventReservation\Model\Email\EmailSender;
use Eguana\EventReservation\Model\UserReservation;
use Eguana\EventReservation\Model\UserReservation\ReservationValidation;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Controller\ResultInterface;
use Psr\Log\LoggerInterface;

/**
 * Used to confirm reservation
 *
 * Class EmailAction
 */
class EmailAction extends Action
{
    /**#@+
     * Constant for date format.
     */
    const CONFIRM_TEMPLATE  = 'Eguana_EventReservation::email/confirm.phtml';
    const CANCEL_TEMPLATE   = 'Eguana_EventReservation::email/cancel.phtml';
    /**#@-*/

    /**
     * @var UserReservationRepositoryInterface
     */
    private $userReservationRepository;

    /**
     * @var ConfigData
     */
    private $configHelper;

    /**
     * @var EmailSender
     */
    private $emailSender;

    /**
     * @var ReservationValidation
     */
    private $reservationValidation;

    /**
     * @var EventRepositoryInterface
     */
    private $eventRepository;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @param Context $context
     * @param ConfigData $configHelper
     * @param EmailSender $emailSender
     * @param LoggerInterface $logger
     * @param ReservationValidation $reservationValidation
     * @param EventRepositoryInterface $eventRepository
     * @param UserReservationRepositoryInterface $userReservationRepository
     */
    public function __construct(
        Context $context,
        ConfigData $configHelper,
        EmailSender $emailSender,
        LoggerInterface $logger,
        ReservationValidation $reservationValidation,
        EventRepositoryInterface $eventRepository,
        UserReservationRepositoryInterface $userReservationRepository
    ) {
        $this->logger = $logger;
        $this->emailSender = $emailSender;
        $this->configHelper = $configHelper;
        $this->eventRepository = $eventRepository;
        $this->reservationValidation = $reservationValidation;
        $this->userReservationRepository = $userReservationRepository;
        parent::__construct($context);
    }

    /**
     * Method used to load layout and render information
     *
     * @return ResponseInterface|ResultInterface
     */
    public function execute()
    {
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);

        $userReserveId = $this->getRequest()->getParam('id');
        $callFor = $this->getRequest()->getParam('call');
        $mailToken = $this->getRequest()->getParam('token');

        if ($userReserveId && $callFor && $mailToken) {
            $mailToken = explode('_', $mailToken);
            $token = $mailToken[0];
            $customerOrStaff = isset($mailToken[1]) ? $mailToken[1] : 'C';

            try {
                $model = $this->userReservationRepository->getById($userReserveId);
            } catch (\Exception $e) {
                $this->messageManager->addErrorMessage(__('No event reserved against this request'));
                return $resultRedirect->setUrl('/');
            }

            if ($customerOrStaff != 'C' && $customerOrStaff != 'SA') {
                $this->messageManager->addErrorMessage(__('No event reserved against this request.'));
                return $resultRedirect->setUrl('/');
            }

            if ($token == $model->getAuthToken()) {
                if ($callFor == 'confirm') {
                    $data = [
                        'date' => $model->getDate(),
                        'time_slot' => $model->getTimeSlot(),
                        'counter_id' => $model->getCounterId()
                    ];
                    $seatsAvailable = $this->reservationValidation->seatsAvailable($data, $callFor);

                    if (!$seatsAvailable) {
                        $this->messageManager->addErrorMessage(
                            __('Sorry all seats are reserved against this event.')
                        );
                        return $resultRedirect->setUrl('/');
                    } elseif ($customerOrStaff != 'SA' && $model->getEmailAction()) {
                        $this->messageManager->addErrorMessage(
                            __('You are not allowed to perform this action.')
                        );
                        return $resultRedirect->setUrl('/');
                    } else {
                        if ($customerOrStaff != 'SA') {
                            $model->setEmailAction(1);
                        }
                        $model->setStatus(UserReservation::STATUS_APPROVED);
                    }
                } elseif ($callFor == 'cancel') {
                    if ($customerOrStaff != 'SA' && $model->getEmailAction()) {
                        $this->messageManager->addErrorMessage(
                            __('You are not allowed to perform this action.')
                        );
                        return $resultRedirect->setUrl('/');
                    } else {
                        if ($customerOrStaff != 'SA') {
                            $model->setEmailAction(1);
                        }
                        $model->setStatus(UserReservation::STATUS_CANCELED);
                    }
                } else {
                    $this->messageManager->addErrorMessage(__('No event reserved against this id.'));
                    return $resultRedirect->setUrl('/');
                }

                $this->userReservationRepository->save($model);
                $this->sendEmail($customerOrStaff, $userReserveId, $callFor, $model->getEventId());

                if ($callFor == 'confirm') {
                    $message = 'The event reservation is confirmed successfully.';
                } else {
                    $message = 'The event reservation is canceled successfully.';
                }

                $this->messageManager->addSuccessMessage(__($message));
                return $resultRedirect->setUrl('/');
            }
        }
        $this->messageManager->addErrorMessage(__('No event reserved against this request.'));
        return $resultRedirect->setUrl('/');
    }

    /**
     * Send Email to customer & staff
     *
     * @param $emailTo
     * @param $userReserveId
     * @param $callFor
     * @param $eventId
     */
    private function sendEmail($emailTo, $userReserveId, $callFor, $eventId)
    {
        try {
            $event = $this->eventRepository->getById($eventId);
            $storeId = $event->getStoreId();
            $storeId = ($storeId && is_array($storeId)) ? $storeId[0] : $storeId;
            $storeId = $storeId ? $storeId : 0;
        } catch (\Exception $e) {
            $this->logger->error('Error while fetching event by id:' . $e->getMessage());
        }

        if (isset($storeId)) {
            if ($this->configHelper->getCustomerEmailEnabled($storeId) == 1) {
                $this->emailSender->sendEmailToCustomer(
                    $userReserveId,
                    $callFor
                );
            }
            if ($this->configHelper->getStaffEmailEnabled($storeId) == 1) {
                $this->emailSender->sendEmailToStaff(
                    $userReserveId,
                    $callFor
                );
            }
        }
    }
}
