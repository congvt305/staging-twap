<?php

namespace Amasty\Feed\Block\Adminhtml;

class GoogleWizard extends \Magento\Backend\Block\Widget\Grid\Container
{
    protected function _construct()
    {
        $this->_controller = 'adminhtml';
        $this->_blockGroup = 'Amasty_Feed';
        $this->_headerText = __('GoogleWizard');
        $this->_addButtonLabel = __('Setup Google Wizard');
        parent::_construct();
    }
}
