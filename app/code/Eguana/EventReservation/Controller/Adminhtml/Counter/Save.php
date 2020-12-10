<?php
/**
 * @author Eguana Team
 * @copyright Copyright (c) 2020 Eguana {http://eguanacommerce.com}
 * Created by PhpStorm
 * User: umer
 * Date: 4/11/20
 * Time: 5:49 PM
 */
namespace Eguana\EventReservation\Controller\Adminhtml\Counter;

use Eguana\EventReservation\Api\UserReservationRepositoryInterface;
use Eguana\EventReservation\Api\CounterRepositoryInterface;
use Eguana\EventReservation\Model\Counter\TimeSlotSeats;
use Eguana\EventReservation\Model\CounterFactory;
use Eguana\EventReservation\Model\ResourceModel\Counter\Grid\CollectionFactory;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\Result\Json;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Framework\View\Result\PageFactory;

/**
 * Class Save
 *
 * Save counter data
 */
class Save extends Action
{
    /**#@+
     * Constants for date & time formats.
     */
    const DATE_FORMAT = 'Y-m-d';
    const TIME_FORMAT = 'H:i';
    /**#@-*/

    /**
     * @var PageFactory
     */
    private $resultPageFactory;

    /**
     * @var JsonFactory
     */
    private $resultJsonFactory;

    /**
     * @var CollectionFactory
     */
    private $counterFactory;

    /**
     * @var CounterFactory
     */
    private $counter;

    /**
     * @var CounterRepositoryInterface
     */
    private $counterRepository;

    /**
     * @var TimeSlotSeats
     */
    private $timeSlotSeats;

    /**
     * @var DateTime
     */
    private $dateTime;

    /**
     * @var SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    /**
     * @var UserReservationRepositoryInterface
     */
    private $userReservationRepository;

    /**
     * @param Context $context
     * @param DateTime $dateTime
     * @param PageFactory $pageFactory
     * @param JsonFactory $resultJsonFactory
     * @param TimeSlotSeats $timeSlotSeats
     * @param CollectionFactory $counterFactory
     * @param CounterRepositoryInterface $counterRepository
     * @param CounterFactory $counter
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param UserReservationRepositoryInterface $userReservationRepository
     */
    public function __construct(
        Context $context,
        DateTime $dateTime,
        PageFactory $pageFactory,
        JsonFactory $resultJsonFactory,
        TimeSlotSeats $timeSlotSeats,
        CollectionFactory $counterFactory,
        CounterRepositoryInterface $counterRepository,
        CounterFactory $counter,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        UserReservationRepositoryInterface $userReservationRepository
    ) {
        $this->dateTime = $dateTime;
        $this->resultPageFactory = $pageFactory;
        $this->resultJsonFactory = $resultJsonFactory;
        $this->counterFactory = $counterFactory;
        $this->counterRepository = $counterRepository;
        $this->counter = $counter;
        $this->timeSlotSeats = $timeSlotSeats;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->userReservationRepository = $userReservationRepository;
        parent::__construct($context);
    }

    /**
     * Save counter data
     *
     * @return ResponseInterface|Json|ResultInterface
     */
    public function execute()
    {
        $post = $this->getRequest()->getParams();

        $data = $post;
        if (!isset($data['reserved_users'])) {
            $slot_time = $data['slot_time'];
            if (strpos($slot_time, ':') !== false) {
                $slot = explode(':', $slot_time);
                $data['slot_time'] = ($slot[0]*60) + ($slot[1]);
            }
            $break = $data['break'] ? $data['break'] : 0;
            if (strpos($break, ':') !== false) {
                $time = explode(':', $break);
                $data['break'] = ($time[0]*60) + ($time[1]);
            }
            $data['start_time'] = $this->dateTime->gmtDate(self::DATE_FORMAT) . ' ' . $data['start_time'];
            $start = $this->dateTime->gmtTimestamp($data['start_time']);
            $end = $this->dateTime->gmtTimestamp($data['end_time']);
            if ($start > $end) {
                $from_date = $this->dateTime->gmtTimestamp($data['from_date']);
                $to_date = $this->dateTime->gmtTimestamp($data['to_date']);
                if ($from_date == $to_date) {
                    $error['startTimeIsGreater'] = true;
                }
                $data['end_time'] = date(self::DATE_FORMAT, strtotime('+1 day')) . ' ' . $data['end_time'];
                $end = $this->dateTime->gmtTimestamp($data['end_time']);
            } else {
                $data['end_time'] = $this->dateTime->gmtDate(self::DATE_FORMAT) . ' ' . $data['end_time'];
            }
            $timeDiff = ($end - $start)/60;
            if ($data['slot_time'] > $timeDiff) {
                $error['slotIsGreater'] = true;
            }
            if (isset($data['close_days']) && is_array($data['close_days'])) {
                if (count($data['close_days']) == 7) {
                    $error['allDaysClosed'] = true;
                }
                $data['close_days'] = implode(',', $data['close_days']);
            } else {
                $data['close_days'] = '';
            }
            $totalSlots = $this->timeSlotSeats->totalSlots($data);
            $data['total_slots'] = ((int) $totalSlots < 0) ? 0 : (int) $totalSlots;
            $remainingMins = $this->timeSlotSeats->getTimeSlots(0, $data, true);
            if ($remainingMins) {
                $error['remainingMins'] = true;
            }
        }

        $eventId = $data['event_id'];
        $counterId = $data['counter_id'];
        if (!isset($error)) {
            $model = $this->counterFactory->create()
                ->addFieldToSelect('reservation_counter_id')
                ->addFilter('event_id', $eventId)
                ->addFilter('offline_store_id', $counterId);
            $items = $model->getItems();
            if (count($items)) {
                foreach ($items as $item) {
                    $item = $this->counterRepository->getById($item->getReservationCounterId());
                    $item->setData('status', $data['status']);
                    $item->setData('staff_email', $data['staff_email']);

                    if (!isset($data['reserved_users'])) {
                        $item->setData('from_date', $data['from_date']);
                        $item->setData('to_date', $data['to_date']);
                        $item->setData('slot_time', $data['slot_time']);
                        $item->setData('break', $data['break']);
                        $item->setData('per_time_slot_seats', $data['per_time_slot_seats']);
                        $item->setData('start_time', $data['start_time']);
                        $item->setData('end_time', $data['end_time']);
                        $item->setData('close_days', $data['close_days']);
                        $item->setData('total_slots', $data['total_slots']);
                    }
                    $this->counterRepository->save($item);
                }
            } else {
                $model = $this->counter->create();
                $model->setData('status', $data['status']);
                $model->setData('staff_email', $data['staff_email']);
                $model->setData('from_date', $data['from_date']);
                $model->setData('to_date', $data['to_date']);
                $model->setData('slot_time', $data['slot_time']);
                $model->setData('break', $data['break']);
                $model->setData('per_time_slot_seats', $data['per_time_slot_seats']);
                $model->setData('start_time', $data['start_time']);
                $model->setData('end_time', $data['end_time']);
                $model->setData('close_days', $data['close_days']);
                $model->setData('event_id', $eventId);
                $model->setData('offline_store_id', $counterId);
                $model->setData('total_slots', $data['total_slots']);
                $this->counterRepository->save($model);
            }
        }

        $response = ['success' => true];
        if (isset($error)) {
            $response = ['success' => false];

            if (isset($error['startTimeIsGreater'])) {
                $response['errorMessage'] = 'The Start time should be less than End time.';
            }
            if (isset($error['allDaysClosed'])) {
                $response['errorMessage'] = 'All days can\'t be close.';
            }
            if (isset($error['remainingMins'])) {
                $response['errorMessage'] = 'Please select exact time for slots.';
            }
            if (isset($error['slotIsGreater'])) {
                $response['errorMessage'] = 'The Slot time should be less than difference between Start and End time.';
            }
        }
        $result = $this->resultJsonFactory->create();
        $result->setData($response);
        return $result;
    }
}
