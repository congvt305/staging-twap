<?php
/**
 * @author Eguana Team
 * @copyriht Copyright (c) 2020 Eguana {http://eguanacommerce.com}
 * Created by PhpStorm
 * User: arslan
 * Date: 15/10/20
 * Time: 1:10 PM
 */
declare(strict_types=1);

namespace Eguana\Redemption\Block\Adminhtml\Edit;

use Magento\Framework\View\Element\UiComponent\Control\ButtonProviderInterface;
use Eguana\Redemption\Block\Adminhtml\Edit\GenericButton;

/**
 * Class for the deletebutton toolbar on create/edit Redemption
 *
 * Class DeleteButton
 */
class DeleteButton extends GenericButton implements ButtonProviderInterface
{
    /**
     * @return array
     */
    public function getButtonData() : array
    {
        $data = [];
        if ($this->getId()) {
            $data = [
                'label' => __('Delete Redemption'),
                'class' => 'delete',
                'on_click' => 'deleteConfirm(\'' . __(
                    'Are you sure you want to do this?'
                ) . '\', \'' . $this->getDeleteUrl() . '\')',
                'sort_order' => 20,
            ];
        }
        return $data;
    }

    /**
     * @return string
     */
    private function getDeleteUrl() : string
    {
        return $this->getUrl('*/*/delete', ['redemption_id' => $this->getId()]);
    }
}
