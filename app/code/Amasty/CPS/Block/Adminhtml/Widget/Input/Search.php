<?php

namespace Amasty\CPS\Block\Adminhtml\Widget\Input;

class Search extends \Magento\Backend\Block\Widget
{
    /**
     * Define block template
     *
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setTemplate('Amasty_VisualMerchUi::widget/input.phtml');
    }
}
