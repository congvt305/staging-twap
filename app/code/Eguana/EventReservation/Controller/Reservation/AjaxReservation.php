<?php
/**
 * @author Eguana Team
 * @copyriht Copyright (c) 2020 Eguana {http://eguanacommerce.com}
 * Created by PhpStorm
 * User: raheel
 * Date: 24/2/21
 * Time: 11:50 PM
 */
namespace Eguana\EventReservation\Controller\Reservation;

use Eguana\EventReservation\Api\CounterRepositoryInterface;
use Eguana\EventReservation\Api\UserReservationRepositoryInterface;
use Eguana\EventReservation\Helper\ConfigData;
use Eguana\EventReservation\Model\Email\EmailSender;
use Eguana\EventReservation\Model\Service\SmsSender;
use Eguana\EventReservation\Model\UserReservation\ReservationValidation;
use Eguana\EventReservation\Model\UserReservationFactory;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Store\Model\StoreManagerInterface;
use Psr\Log\LoggerInterface;

/**
 * To reserve event with ajax request
 *
 * Class AjaxReservation
 */
class AjaxReservation extends Action
{
    /**#@+
     * Constant for date format.
     */
    const DATE_FORMAT = 'Y-m-d';
    /**#@-*/

    /**
     * @var ConfigData
     */
    private $configHelper;

    /**
     * @var CounterRepositoryInterface
     */
    private $counterRepository;

    /**
     * @var UserReservationRepositoryInterface
     */
    private $userReservationRepository;

    /**
     * @var UserReservationFactory
     */
    private $userReservationFactory;

    /**
     * @var SmsSender
     */
    private $smsSender;

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
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @param Context $context
     * @param DateTime $dateTime
     * @param SmsSender $smsSender
     * @param ConfigData $configHelper
     * @param EmailSender $emailSender
     * @param LoggerInterface $logger
     * @param StoreManagerInterface $storeManager
     * @param ReservationValidation $reservationValidation
     * @param UserReservationFactory $userReservationFactory
     * @param CounterRepositoryInterface $counterRepository
     * @param UserReservationRepositoryInterface $userReservationRepository
     */
    public function __construct(
        Context $context,
        DateTime $dateTime,
        SmsSender $smsSender,
        ConfigData $configHelper,
        EmailSender $emailSender,
        LoggerInterface $logger,
        StoreManagerInterface $storeManager,
        ReservationValidation $reservationValidation,
        UserReservationFactory $userReservationFactory,
        CounterRepositoryInterface $counterRepository,
        UserReservationRepositoryInterface $userReservationRepository
    ) {
        $this->logger = $logger;
        $this->dateTime = $dateTime;
        $this->smsSender = $smsSender;
        $this->emailSender = $emailSender;
        $this->configHelper = $configHelper;
        $this->storeManager = $storeManager;
        $this->counterRepository = $counterRepository;
        $this->reservationValidation = $reservationValidation;
        $this->userReservationFactory = $userReservationFactory;
        $this->userReservationRepository = $userReservationRepository;
        parent::__construct($context);
    }

    /**
     * To reserve event via ajax request
     *
     * @return ResponseInterface|ResultInterface
     */
    public function execute()
    {
        if ($this->_request->isAjax()) {
            $resultJson = $this->resultFactory->create(ResultFactory::TYPE_JSON);
            $token = substr(str_shuffle('0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ'), 1, 15);
            $post = (array) $this->getRequest()->getPost();
            $response = [
                'success' => false,
                'message' => ''
            ];
            if (!empty($post)) {
                try {
                    $counter = $this->counterRepository->getById($post['counter_id']);
                } catch (\Exception $exception) {
                    $this->logger->errot($exception->getMessage());
                    $response['message'] = __('This Counter doesn\'t exist.');
                    return $resultJson->setData($response);
                }

                $data = $post;
                $data['offline_store_id'] = $counter->getOfflineStoreId();
                $data['agreement'] = ($post['agreement'] == 'on') ? 1 : 0;
                $token = substr(str_shuffle('0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ'), 0, 15);
                $data['auth_token'] = $token;
                $data['date'] = $this->dateTime->gmtDate(self::DATE_FORMAT, $post['date']);
                try {
                    $data['store_id'] = $this->storeManager->getStore()->getId();
                } catch (\Exception $exception) {
                    $this->logger->error($exception->getMessage());
                    $response['message'] = $exception->getMessage();
                    return $resultJson->setData($response);
                }

                $canReserve = $this->reservationValidation->canReserveEvent($data);
                $seatsAvailable = $this->reservationValidation->seatsAvailable($data);

                if (!$canReserve || !$seatsAvailable) {
                    if (!$canReserve) {
                        $response['message'] = __('Your have already reserved this event');
                    } else {
                        $response['message'] = __('Sorry all seats against this counter are reserved');
                    }

                    $response['duplicate'] = true;
                    return $resultJson->setData($response);
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
                    $this->smsSender->sendSms($model->getData('user_reserve_id'));
                    $response['success'] = true;
                    $response['message'] = __('Successfully booked an event');
                    $response['reserve_id'] = $model->getData('user_reserve_id');
                } catch (LocalizedException $e) {
                    $response['message'] = $e->getMessage();
                } catch (\Exception $e) {
                    $response['message'] = __('Something went wrong while reserving the event');
                }
            }
            return $resultJson->setData($response);
        } elseif (!$this->_request->isAjax()) {
            $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
            $resultRedirect->setUrl('/');
            return $resultRedirect;
        }
    }
}
