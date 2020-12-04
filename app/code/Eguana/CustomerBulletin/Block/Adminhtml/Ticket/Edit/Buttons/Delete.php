<?php
/**
 * @author Eguana Team
 * @copyriht Copyright (c) 2020 Eguana {http://eguanacommerce.com}
 * Created by PhpStorm
 * User: bilalyounas
 * Date: 11/10/20
 * Time: 06:48 PM
 */
declare(strict_types=1);

namespace Eguana\CustomerBulletin\Block\Adminhtml\Ticket\Edit\Buttons;

use Magento\Framework\View\Element\UiComponent\Control\ButtonProviderInterface;

/**
 * Class Delete to return delete button block
 */
class Delete extends Generic implements ButtonProviderInterface
{
    /**
     * Get delete button data
     *
     * @return array
     */
    public function getButtonData() : array
    {
        $data = [];
        if ($this->getId()) {
            $data = [
                'label' => __('Delete Ticket'),
                'class' => 'delete',
                'on_click' => 'deleteConfirm(\'' . (
                    'Are you sure you want to do this?'
                ) . '\', \'' . $this->getDeleteUrl() . '\')',
                'sort_order' => 20,
            ];
        }
        return $data;
    }

    /**
     * URL to send delete requests to.
     *
     * @return string
     */
    private function getDeleteUrl() : string
    {
        return $this->getUrl('*/*/delete', ['ticket_id' => $this->getId()]);
    }
}
