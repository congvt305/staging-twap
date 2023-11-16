<?php

declare(strict_types=1);

namespace Amasty\ShopByQuickConfig\Controller\Adminhtml\Filters;

use Magento\Backend\App\Action;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\Controller\ResultFactory;

class Index extends Action implements HttpGetActionInterface
{
    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    const ADMIN_RESOURCE = 'Amasty_ShopByQuickConfig::navigation_attributes';

    public function execute()
    {
        $resultPage = $this->resultFactory->create(ResultFactory::TYPE_PAGE);
        $resultPage->setActiveMenu('Amasty_ShopByQuickConfig::navigation_attributes');
        $resultPage->getConfig()->getTitle()->prepend(__('Navigation Filters'));

        return $resultPage;
    }
}
