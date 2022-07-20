<?php
declare(strict_types=1);

namespace Eguana\Faq\Controller\Adminhtml\Faq;

use Eguana\Faq\Controller\Adminhtml\AbstractController;

class EditFaqCatalog extends AbstractController
{
    /**
     * Execute Method
     *
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $resultPage = $this->_init($this->resultPageFactory->create());
        return $resultPage;
    }
}
