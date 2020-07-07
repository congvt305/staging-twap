<?php
/**
 * @author Eguana Team
 * @copyriht Copyright (c) 2020 Eguana {http://eguanacommerce.com}
 * Created by PhpStorm
 * User: arslan
 * Date: 29/6/20
 * Time: 5:50 PM
 */
namespace Eguana\EventManager\Block\Adminhtml\Edit;

use Magento\Framework\View\Element\UiComponent\Control\ButtonProviderInterface;
use Eguana\EventManager\Block\Adminhtml\Edit\GenericButton;

/**
 * Class for the backbutton toolbar on create/edit EventManager
 *
 * Class BackButton
 */
class BackButton extends GenericButton implements ButtonProviderInterface
{
    /**
     * @return array
     */
    public function getButtonData()
    {
        return [
            'label' => __('Back'),
            'on_click' => sprintf("location.href = '%s';", $this->getBackUrl()),
            'class' => 'back',
            'sort_order' => 10,
        ];
    }

    /**
     * Get URL for back (reset) button
     *
     * @return string
     */
    private function getBackUrl()
    {
        return $this->getUrl('*/*/');
    }
}
