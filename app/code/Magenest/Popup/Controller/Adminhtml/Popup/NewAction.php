<?php
namespace Magenest\Popup\Controller\Adminhtml\Popup;

use Magento\Backend\App\Action;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\ResultInterface;

class NewAction extends Action
{
    /**
     * Execute
     *
     * @return ResponseInterface|ResultInterface|void
     */
    public function execute()
    {
        $this->_forward('edit');
    }

    /**
     * Get ACL
     *
     * @return bool
     */
    public function _isAllowed()
    {
        return $this->_authorization->isAllowed('Magenest_Popup::popup');
    }
}
