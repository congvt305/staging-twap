<?php
/**
 * @author Eguana Team
 * @copyriht Copyright (c) 2020 Eguana {http://eguanacommerce.com}
 * Created by PhpStorm
 * User: raheel
 * Date: 16/10/20
 * Time: 12:30 PM
 */
namespace Eguana\EventReservation\Controller\Adminhtml\UserReservation;

use Eguana\EventReservation\Api\EventRepositoryInterface;
use Eguana\EventReservation\Helper\ConfigData;
use Eguana\EventReservation\Model\Email\EmailSender;
use Eguana\EventReservation\Model\ResourceModel\UserReservation\CollectionFactory;
use Eguana\EventReservation\Model\UserReservation;
use Eguana\EventReservation\Model\UserReservation\ReservationValidation;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Ui\Component\MassAction\Filter;
use Magento\Framework\App\Request\DataPersistorInterface;
use Psr\Log\LoggerInterface;

/**
 * Change status to confirm of multiple reservations
 *
 * Class MassConfirm
 */
class MassConfirm extends Action implements HttpPostActionInterface
{
    /**#@+
     * Constant for date format.
     */
    const DATE_FORMAT = 'Y-m-d';
    /**#@-*/

    /**
     * @var Filter
     */
    private $filter;

    /**
     * @var CollectionFactory
     */
    private $collectionFactory;

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
     * @var DateTime
     */
    private $dateTime;

    /**
     * @var DataPersistorInterface
     */
    private $dataPersistor;

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
     * @param Filter $filter
     * @param DateTime $dateTime
     * @param ConfigData $configHelper
     * @param EmailSender $emailSender
     * @param LoggerInterface $logger
     * @param CollectionFactory $collectionFactory
     * @param ReservationValidation $reservationValidation
     * @param DataPersistorInterface $dataPersistor
     * @param EventRepositoryInterface $eventRepository
     */
    public function __construct(
        Context $context,
        Filter $filter,
        DateTime $dateTime,
        ConfigData $configHelper,
        EmailSender $emailSender,
        LoggerInterface $logger,
        CollectionFactory $collectionFactory,
        ReservationValidation $reservationValidation,
        DataPersistorInterface $dataPersistor,
        EventRepositoryInterface $eventRepository
    ) {
        $this->filter                   = $filter;
        $this->logger                   = $logger;
        $this->dateTime                 = $dateTime;
        $this->emailSender              = $emailSender;
        $this->configHelper             = $configHelper;
        $this->dataPersistor            = $dataPersistor;
        $this->eventRepository          = $eventRepository;
        $this->collectionFactory        = $collectionFactory;
        $this->reservationValidation    = $reservationValidation;
        parent::__construct($context);
    }

    /**
     * Execute action to enable events
     *
     * @return ResponseInterface|ResultInterface
     */
    public function execute()
    {
        try {
            $eventId = $this->dataPersistor->get('current_event_id');
            $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);

            try {
                $event = $this->eventRepository->getById($eventId);
            } catch (\Exception $e) {
                $this->logger->error($e->getMessage());
                $this->messageManager->addErrorMessage(__('No record exists against this request.'));
                return $resultRedirect->setPath($this->_redirect->getRefererUrl());
            }

            $storeId = $event->getStoreId() ? $event->getStoreId() : 0;
            $storeId = is_array($storeId) ? $storeId[0] : $storeId;

            $collection = $this->filter->getCollection($this->collectionFactory->create());
            $collection->addFieldToFilter(
                "main_table.event_id",
                ["eq" => $eventId]
            );

            $confirmed = 0;
            foreach ($collection as $item) {
                $data = [
                    'date'          => $this->dateTime->gmtDate(self::DATE_FORMAT, $item->getDate()),
                    'time_slot'     => $item->getTimeSlot(),
                    'counter_id'    => $item->getCounterId()
                ];
                $seatsAvailable = $this->reservationValidation->seatsAvailable($data, 'confirm');

                if ($seatsAvailable) {
                    $item->setStatus(UserReservation::STATUS_APPROVED);
                    $item->save();

                    if ($this->configHelper->getCustomerEmailEnabledForAdmin($storeId) == 1) {
                        $this->emailSender->sendEmailToCustomer(
                            $item->getId(),
                            'confirm'
                        );
                    }
                    if ($this->configHelper->getStaffEmailEnabledForAdmin($storeId) == 1) {
                        $this->emailSender->sendEmailToStaff(
                            $item->getId(),
                            'confirm'
                        );
                    }

                    $confirmed++;
                }
            }

            if ($confirmed) {
                $this->messageManager->addSuccessMessage(
                    __('A total of %1 user reservation(s) have been confirmed.', $confirmed)
                );
            } else {
                $this->messageManager->addNoticeMessage(__('Nothing to update.'));
            }
        } catch (LocalizedException $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
        } catch (\Exception $e) {
            $this->messageManager->addExceptionMessage($e, __($e->getMessage()));
        }

        return $resultRedirect->setPath($this->_redirect->getRefererUrl());
    }
}
