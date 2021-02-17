<?php
/**
 * @author Eguana Team
 * @copyriht Copyright (c) 2020 Eguana {http://eguanacommerce.com}
 * Created by PhpStorm
 * User: raheel
 * Date: 22/10/20
 * Time: 8:23 PM
 */
namespace Eguana\EventReservation\Controller\Reservation;

use Eguana\EventReservation\Api\CounterRepositoryInterface;
use Eguana\EventReservation\Api\EventRepositoryInterface;
use Eguana\EventReservation\Api\UserReservationRepositoryInterface;
use Eguana\EventReservation\Helper\ConfigData;
use Eguana\EventReservation\Model\Email\EmailSender;
use Eguana\EventReservation\Model\UserReservation\ReservationValidation;
use Eguana\EventReservation\Model\UserReservationFactory;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Data\Form\FormKey\Validator;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\View\Result\Page;
use Magento\Framework\View\Result\PageFactory;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\Exception\NoSuchEntityException;

/**
 * Class to reserve an event
 *
 * Class Index
 */
class Index extends Action implements HttpGetActionInterface, HttpPostActionInterface
{
    /**#@+
     * Constant for date format.
     */
    const DATE_FORMAT = 'Y-m-d';
    /**#@-*/

    /**
     * @var PageFactory
     */
    private $pageFactory;

    /**
     * @var ConfigData
     */
    private $configHelper;

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
     * @var Validator
     */
    private $formKeyValidator;

    /**
     * @var UserReservationFactory
     */
    private $userReservationFactory;

    /**
     * @var EmailSender
     */
    private $emailSender;

    /**
     * @var ReservationValidation
     */
    private $reservationValidation;

    /**
     * @var DateTime
     */
    private $dateTime;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @param Context $context
     * @param DateTime $dateTime
     * @param Validator $formKeyValidator
     * @param ConfigData $configHelper
     * @param EmailSender $emailSender
     * @param PageFactory $pageFactory
     * @param StoreManagerInterface $storeManager
     * @param ReservationValidation $reservationValidation
     * @param UserReservationFactory $userReservationFactory
     * @param EventRepositoryInterface $eventRepository
     * @param CounterRepositoryInterface $counterRepository
     * @param UserReservationRepositoryInterface $userReservationRepository
     */
    public function __construct(
        Context $context,
        DateTime $dateTime,
        Validator $formKeyValidator,
        ConfigData $configHelper,
        EmailSender $emailSender,
        PageFactory $pageFactory,
        StoreManagerInterface $storeManager,
        ReservationValidation $reservationValidation,
        UserReservationFactory $userReservationFactory,
        EventRepositoryInterface $eventRepository,
        CounterRepositoryInterface $counterRepository,
        UserReservationRepositoryInterface $userReservationRepository
    ) {
        $this->dateTime = $dateTime;
        $this->pageFactory = $pageFactory;
        $this->emailSender = $emailSender;
        $this->configHelper = $configHelper;
        $this->storeManager = $storeManager;
        $this->eventRepository = $eventRepository;
        $this->formKeyValidator = $formKeyValidator;
        $this->counterRepository = $counterRepository;
        $this->reservationValidation = $reservationValidation;
        $this->userReservationFactory = $userReservationFactory;
        $this->userReservationRepository = $userReservationRepository;
        parent::__construct($context);
    }

    /**
     * Method to create page or redirect
     *
     * @return ResponseInterface|Redirect|ResultInterface|Page
     */
    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();
        $post = (array) $this->getRequest()->getPostValue();
        if (!empty($post) && $this->formKeyValidator->validate($this->getRequest())) {
            $result = $this->saveReservation($post, $resultRedirect);
            if ($result) {
                return $resultRedirect->setPath('*/*/success');
            } else {
                return $resultRedirect->setUrl($this->_redirect->getRefererUrl());
            }
        }

        $eventId = $this->getRequest()->getParam('id');
        try {
            $event = $this->eventRepository->getById($eventId);
            $storeId = $event->getStoreId();
            $storeId = ($storeId && is_array($storeId)) ? $storeId[0] : $storeId;
            $storeId = $storeId ? $storeId : 0;
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage(
                __('No event exsit against this request')
            );
            return $resultRedirect->setPath('/');
        }

        if ($this->configHelper->getEventEnabled($storeId) == 0 || $event->isActive() == 0) {
            if ($event->isActive() == 0) {
                $message = __('This Event is currently disabled');
            } else {
                $message = __('No event exsit against this request');
            }
            $this->messageManager->addErrorMessage($message);
            return $resultRedirect->setPath('/');
        }

        if (isset($event)) {
            return $this->pageFactory->create();
        } else {
            $this->messageManager->addErrorMessage(
                __('No event exsit against this request')
            );
            return $resultRedirect->setUrl('/');
        }
    }

    /**
     * Save reservation
     *
     * @param $post
     * @param $resultRedirect
     * @return mixed
     */
    private function saveReservation($post, $resultRedirect)
    {
        $success = false;
        try {
            $counter = $this->counterRepository->getById($post['counter_id']);
        } catch (\Exception $exception) {
            $this->messageManager->addErrorMessage(__('This Counter doesn\'t exist.'));
            return false;
        }

        $data = $post;
        $data['offline_store_id'] = $counter->getOfflineStoreId();
        $data['agreement'] = ($post['agreement'] == 'on') ? 1 : 0;
        $token = substr(str_shuffle('0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ'), 0, 15);
        $data['auth_token'] = $token;
        $data['date'] = $this->dateTime->gmtDate(self::DATE_FORMAT, $post['date']);
        $data['store_id'] = $this->storeManager->getStore()->getId();

        $canReserve = $this->reservationValidation->canReserveEvent($data);
        $seatsAvailable = $this->reservationValidation->seatsAvailable($data);

        if (!$canReserve || !$seatsAvailable) {
            if (!$canReserve) {
                $message = 'Your have already reserved this event';
            } else {
                $message = 'Sorry all seats against this counter are reserved';
            }

            $this->messageManager->addErrorMessage(__($message));
            return false;
        }

        $model = $this->userReservationFactory->create();
        $model->setData($data);

        try {
            $this->userReservationRepository->save($model);
            if ($this->configHelper->getCustomerEmailEnabled($data['store_id']) == 1) {
                $this->emailSender->sendEmailToCustomer(
                    $model->getData('user_reserve_id'),
                    'pending'
                );
            }
            if ($this->configHelper->getStaffEmailEnabled($data['store_id']) == 1) {
                $this->emailSender->sendEmailToStaff(
                    $model->getData('user_reserve_id'),
                    'pending'
                );
            }
            $this->messageManager->addSuccessMessage(
                __('Successfully booked an event')
            );
            $success = true;
        } catch (LocalizedException $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
        } catch (\Exception $e) {
            $this->messageManager->addExceptionMessage(
                $e,
                __('Something went wrong while reserving the event')
            );
        }
        return $success;
    }
}
