<?php
declare(strict_types=1);

namespace Eguana\Faq\Controller\Adminhtml\Faq;

use Magento\Backend\App\Action;

class NewFaqCatalog extends Action
{
    /**
     * Execute method
     *
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface|void
     */
    public function execute()
    {
        $this->_forward('editfaqcatalog');
    }
}
