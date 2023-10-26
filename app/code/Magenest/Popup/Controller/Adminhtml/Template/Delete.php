<?php
namespace Magenest\Popup\Controller\Adminhtml\Template;

use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Exception\LocalizedException;

class Delete extends Template
{
    /**
     * Execute
     *
     * @return ResponseInterface|ResultInterface|void
     */
    public function execute()
    {
        $params = $this->_request->getParams();
        try {
            $popupTemplate = $this->_popupTemplateFactory->create();
            if (!empty($params['id'])) {
                $this->popupTemplateResources->load($popupTemplate, $params['id']);
                if ($this->getPopupsByTemplateId($params['id'])) {
                    throw new LocalizedException(__(
                        '%1 is currently being used for a popup. '
                        . 'Please remove a template from all popups before deleting it.',
                        $popupTemplate->getTemplateName()
                    ));
                }
                $this->popupTemplateResources->delete($popupTemplate);
                $this->messageManager->addSuccessMessage(__('The Popup template has been deleted.'));
            }
        } catch (\Exception $exception) {
            $this->messageManager->addErrorMessage($exception->getMessage());
            $this->_logger->critical($exception);
        }
        $this->_redirect('*/*/index');
    }
}
