<?php
/**
* @author Amasty Team
* @copyright Copyright (c) 2022 Amasty (https://www.amasty.com)
* @package SEO Toolkit Base for Magento 2
*/

declare(strict_types=1);

namespace Amasty\SeoToolkitLite\Controller\Adminhtml\Redirect;

use Amasty\SeoToolkitLite\Api\Data\RedirectInterface;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Exception\NoSuchEntityException;

class Edit extends AbstractAction
{
    /**
     * {@inheritdoc}
     */
    public function execute()
    {
        $redirectId = (int)$this->getRequest()->getParam(RedirectInterface::REDIRECT_ID);

        try {
            $model = $this->getRedirectModel($redirectId);
        } catch (NoSuchEntityException $exception) {
            $this->messageManager->addErrorMessage(__('This Redirect no longer exists.'));
            $resultRedirect = $this->resultRedirectFactory->create();

            return $resultRedirect->setPath('*/*/');
        }

        $resultPage = $this->resultFactory->create(ResultFactory::TYPE_PAGE);

        $text = $model->getRedirectId() ? __('Edit Redirect') : __('New Redirect');
        $this->initPage($resultPage)->getConfig()->getTitle()->prepend($text);

        return $resultPage;
    }

    private function getRedirectModel(int $redirectId): RedirectInterface
    {
        if ($redirectId) {
            $model = $this->redirectRepository->getById($redirectId);
        } else {
            $model = $this->redirectFactory->create();
        }

        return $model;
    }
}
