<?php
/**
 * @author Eguana Team
 * @copyright Copyright (c) 2021 Eguana {http://eguanacommerce.com}
 * Created by PhpStorm
 * User: umer
 * Date: 6/1/21
 * Time: 2:27 PM
 */
namespace Eguana\CustomOrderGrid\Block\Adminhtml\Order\Creditmemo\Create;

use Magento\Sales\Block\Adminhtml\Order\Creditmemo\Create\Items as ItemsBlock;

/**
 * Class Items
 *
 * Change Refund buttons labels
 */
class Items extends ItemsBlock
{

    /**
     * Prepare child blocks
     *
     * Change Refund buttons labels
     *
     * @return $this
     */
    protected function _prepareLayout()
    {
        parent::_prepareLayout();
        if ($this->getChildBlock('submit_button')) {
            if ($this->getChildBlock('submit_button')->getData('class') == 'save submit-button refund primary') {
                $this->getChildBlock('submit_button')->setData('label', __('Proper Refund'));
            }
            if ($this->getChildBlock('submit_button')->getData('class') == 'save submit-button primary') {
                $this->getChildBlock('submit_button')->setData('label', __('Manually Refund'));
            }
        }
        if ($this->getChildBlock('submit_offline')) {
            $this->getChildBlock('submit_offline')->setData('label', __('Manually Refund'));
        }
    }
}
