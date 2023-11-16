<?php
namespace Amasty\Meta\Controller\Adminhtml\Custom;

class Index extends \Amasty\Meta\Controller\Adminhtml\Config\Index
{
    protected $_title = 'Meta Tags Template (URLs)';
    protected $_isCustom = true;

    public function execute()
    {
        $this->_blockName = 'custom';
        parent::execute();
    }
}