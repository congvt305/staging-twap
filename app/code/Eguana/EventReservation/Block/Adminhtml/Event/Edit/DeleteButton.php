<?php
/**
 * @author Eguana Team
 * @copyriht Copyright (c) 2020 Eguana {http://eguanacommerce.com}
 * Created by PhpStorm
 * User: raheel
 * Date: 19/10/20
 * Time: 07:04 PM
 */
declare(strict_types=1);

namespace Eguana\EventReservation\Block\Adminhtml\Event\Edit;

use Magento\Framework\View\Element\UiComponent\Control\ButtonProviderInterface;

/**
 * For delete button on create/edit event
 *
 * Class DeleteButton
 */
class DeleteButton extends GenericButton implements ButtonProviderInterface
{
    /**
     * Get delete button data
     *
     * @return array
     */
    public function getButtonData() : array
    {
        $data = [];
        if ($this->getEventId()) {
            $data = [
                'label' => __('Delete Event'),
                'class' => 'delete',
                'on_click' => 'deleteConfirm(\'' . __(
                    'Are you sure you want to do this?'
                ) . '\', \'' . $this->getDeleteUrl() . '\', {"data": {}})',
                'sort_order' => 20,
            ];
        }
        return $data;
    }

    /**
     * Url to send delete requests to.
     *
     * @return string
     */
    public function getDeleteUrl() : string
    {
        return $this->getUrl('*/*/delete', ['event_id' => $this->getEventId()]);
    }
}
