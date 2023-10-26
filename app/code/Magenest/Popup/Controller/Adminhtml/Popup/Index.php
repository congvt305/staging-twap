<?php
namespace Magenest\Popup\Controller\Adminhtml\Popup;

use Magento\Framework\View\Result\Page;

class Index extends Popup
{
    /**
     * Execute
     *
     * @return Page
     */
    public function execute()
    {
        $resultPage = $this->resultPageFactory->create();
        $resultPage->addBreadcrumb(__('Manage Popup'), __('Manage Popup'));
        $resultPage->getConfig()->getTitle()->prepend(__('Manage Popup'));
        return $resultPage;
    }
}
