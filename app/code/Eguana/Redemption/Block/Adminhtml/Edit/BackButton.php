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
 * Class for the backbutton toolbar on create/edit Redemption
 *
 * Class BackButton
 */
class BackButton extends GenericButton implements ButtonProviderInterface
{
    /**
     * @return array
     */
    public function getButtonData() : array
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
    private function getBackUrl() : string
    {
        return $this->getUrl('*/*/');
    }
}
