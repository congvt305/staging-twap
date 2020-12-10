<?php
/**
 * @author Eguana Team
 * @copyright Copyright (c) 2020 Eguana {http://eguanacommerce.com}
 * Created by PhpStorm
 * User: umer
 * Date: 4/11/20
 * Time: 1:31 PM
 */
namespace Eguana\EventReservation\Block\Adminhtml\Counter\Edit;

use Eguana\EventReservation\Api\EventRepositoryInterface;
use Eguana\EventReservation\Api\UserReservationRepositoryInterface;
use Eguana\EventReservation\Helper\ConfigData;
use Eguana\EventReservation\Model\Counter;
use Eguana\EventReservation\Model\ResourceModel\Counter\Grid\CollectionFactory;
use Eguana\EventReservation\Model\UserReservation;
use Magento\Backend\Block\Template\Context;
use Magento\Backend\Block\Widget\Form\Generic;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Data\FormFactory;
use Magento\Framework\Registry;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Stdlib\DateTime\DateTime;

/**
 * Class Main
 *
 * Counter form
 */
class Main extends Generic
{
    /**#@+
     * Constants for time format.
     */
    const TIME_FORMAT = 'H:i';
    /**#@-*/

    /**
     * @var ConfigData
     */
    private $configHelper;

    /**
     * @var Counter
     */
    private $counterModel;

    /**
     * @var DateTime
     */
    private $dateTime;

    /**
     * @var SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    /**
     * @var EventRepositoryInterface
     */
    private $eventRepository;

    /**
     * @var UserReservationRepositoryInterface
     */
    private $userReservationRepository;

    /**
     * @param Context $context
     * @param Registry $registry
     * @param DateTime $dateTime
     * @param FormFactory $formFactory
     * @param CollectionFactory $collectionFactory
     * @param ConfigData $configHelper
     * @param Counter $counterModel
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param EventRepositoryInterface $eventRepository
     * @param UserReservationRepositoryInterface $userReservationRepository
     * @param array $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        DateTime $dateTime,
        FormFactory $formFactory,
        CollectionFactory $collectionFactory,
        ConfigData $configHelper,
        Counter $counterModel,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        EventRepositoryInterface $eventRepository,
        UserReservationRepositoryInterface $userReservationRepository,
        array $data = []
    ) {
        $this->dateTime = $dateTime;
        $this->collection = $collectionFactory->create();
        $this->configHelper = $configHelper;
        $this->counterModel = $counterModel;
        $this->eventRepository = $eventRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->userReservationRepository = $userReservationRepository;
        parent::__construct(
            $context,
            $registry,
            $formFactory,
            $data
        );
    }

    /**
     * Prepare counter form
     *
     * @return Main
     * @throws LocalizedException
     */
    protected function _prepareForm()
    {
        $eventId = $this->getRequest()->getParam('eventId');
        $counterId = $this->getRequest()->getParam('counterId');
        $this->collection->getSelect()->joinRight(
            ['si' => $this->collection->getTable('storeinfo')],
            'si.entity_id = main_table.offline_store_id AND main_table.event_id = "' . $eventId . '"',
            ['entity_id', 'title']
        );
        $this->collection->addFieldToSelect('reservation_counter_id');
        $this->collection->getSelect()->where('si.available_for_events = 1');
        $collection = $this->collection->addFilter('entity_id', $counterId);
        $data = $collection->getFirstItem()->getData();

        $event = $this->eventRepository->getById($eventId);
        $storeId = $event->getStoreId();
        $storeId = ($storeId && is_array($storeId)) ? $storeId[0] : 0;

        $data['status'] = $data['status'] ? $data['status'] : 0;
        $data['staff_email'] = ($data['staff_email']) ? $data['staff_email'] : $this->getStaffUserEmail($storeId);
        if ($data['start_time']) {
            $data['start_time'] = $this->dateTime->gmtDate(self::TIME_FORMAT, $data['start_time']);
        }
        if ($data['end_time']) {
            $data['end_time'] = $this->dateTime->gmtDate(self::TIME_FORMAT, $data['end_time']);
        }
        if ($data['slot_time']) {
            $slotTime = mktime(0, (int) $data['slot_time']);
            $data['slot_time'] = $this->dateTime->gmtDate(self::TIME_FORMAT, $slotTime);
        }
        if ($data['break']) {
            $break = mktime(0, (int) $data['break']);
            $data['break'] = $this->dateTime->gmtDate(self::TIME_FORMAT, $break);
        }

        $isElementDisabled = false;
        if ($data['event_id'] && $data['reservation_counter_id']) {
            $search = $this->searchCriteriaBuilder
                ->addFilter('main_table.event_id', $data['event_id'], 'eq')
                ->addFilter('main_table.counter_id', $data['reservation_counter_id'], 'eq')
                ->addFilter('main_table.status', UserReservation::STATUS_CANCELED, 'neq')
                ->create();
            $reservedUsers = $this->userReservationRepository->getList($search)->getTotalCount();

            if ($reservedUsers) {
                $isElementDisabled = true;
            }
        }

        $form = $this->_formFactory->create(
            [
                'data' => [
                    'id' => 'edit_form',
                    'action' => $this->getData('action'),
                    'method' => 'post',
                    'enctype' => 'multipart/form-data'
                ]
            ]
        );
        $form->setHtmlIdPrefix('counter_');

        $fieldset = $form->addFieldset('base_fieldset', [
            'class' => 'fieldset-wide',
        ]);

        $fieldset->addField(
            'status',
            'select',
            [
                'name' => 'status',
                'label' => __('Status'),
                'title' => __('Status'),
                'required' => true,
                'options' => $this->counterModel->getAvailableStatuses(),
                'disabled' => false
            ]
        );

        $fieldset->addField(
            'title',
            'text',
            [
                'name' => 'title',
                'label' => __('Store Name'),
                'title' => __('Store Name'),
                'required' => true,
                'disabled' => true
            ]
        );

        $fieldset->addField(
            'staff_email',
            'text',
            [
                'name' => 'staff_email',
                'label' => __('Staff Email'),
                'title' => __('Staff Email'),
                'required' => true,
                'class' => 'validate-email',
                'value' => $this->getStaffUserEmail($storeId),
                'disabled' => false
            ]
        );

        $fieldset->addField(
            'from_date',
            'text',
            [
                'name' => 'from_date',
                'label' => __('From Date'),
                'title' => __('From Date'),
                'class' => 'modal-date-picker',
                'required' => true,
                'readonly' => 'true',
                'disabled' => $isElementDisabled,
                'style' => 'width: 15rem;'
            ]
        );

        $fieldset->addField(
            'to_date',
            'text',
            [
                'name' => 'to_date',
                'label' => __('To Date'),
                'title' => __('To Date'),
                'class' => 'modal-date-picker',
                'required' => true,
                'readonly' => 'true',
                'disabled' => $isElementDisabled,
                'style' => 'width: 15rem;'
            ]
        );

        $fieldset->addField(
            'slot_time',
            'text',
            [
                'name' => 'slot_time',
                'label' => __('Slot Time'),
                'title' => __('Slot Time'),
                'required' => true,
                'class' => 'modal-time-picker',
                'disabled' => $isElementDisabled,
                'style' => 'width: 15rem;'
            ]
        );

        $fieldset->addField(
            'break',
            'text',
            [
                'name' => 'break',
                'label' => __('Break'),
                'title' => __('Break'),
                'class' => 'modal-time-picker',
                'disabled' => $isElementDisabled,
                'style' => 'width: 15rem;'
            ]
        );

        $fieldset->addField(
            'per_time_slot_seats',
            'text',
            [
                'name' => 'per_time_slot_seats',
                'label' => __('Per Time Slot Seats'),
                'title' => __('Per Time Slot Seats'),
                'required' => true,
                'class' => 'validate-number',
                'disabled' => $isElementDisabled
            ]
        );

        $fieldset->addField(
            'start_time',
            'text',
            [
                'name' => 'start_time',
                'label' => __('Start Time'),
                'title' => __('Start Time'),
                'required' => true,
                'class' => 'modal-time-picker',
                'disabled' => $isElementDisabled,
                'style' => 'width: 15rem;'
            ]
        );

        $fieldset->addField(
            'end_time',
            'text',
            [
                'name' => 'end_time',
                'label' => __('End Time'),
                'title' => __('End Time'),
                'required' => true,
                'disabled' => $isElementDisabled,
                'class' => 'modal-time-picker',
                'style' => 'width: 15rem;'
            ]
        );

        $fieldset->addField(
            'close_days',
            'multiselect',
            [
                'name' => 'close_days',
                'label' => __('Close Days'),
                'title' => __('Close Days'),
                'disabled' => $isElementDisabled,
                'values' => $this->getAvailableDays(),
            ]
        );

        if (isset($reservedUsers) && $reservedUsers) {
            $fieldset->addField(
                'reserved_users',
                'hidden',
                ['name' => 'reserved_users']
            );
        }

        $form->setValues($data);

        $this->setForm($form);

        return parent::_prepareForm();
    }

    /**
     * Get dyas
     *
     * @return array
     */
    private function getAvailableDays()
    {
        $data = [];
        foreach (Counter::CLOSE_DAYS_LIST as $value) {
            $data[] = ['value' => $value, 'label' => $value];
        }
        return $data;
    }

    /**
     * Get staff user email
     *
     * @param $storeId
     * @return mixed
     */
    private function getStaffUserEmail($storeId)
    {
        return $this->configHelper->getStaffEmail($storeId);
    }
}
