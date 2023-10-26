<?php
namespace Magenest\Popup\Controller\Adminhtml\Popup;

use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\View\Result\Page;

class Edit extends Popup
{
    /**
     * Execute
     *
     * @return ResponseInterface|Redirect|ResultInterface|Page
     */
    public function execute()
    {
        $widgetInstance = $this->_initWidgetInstance();
        $popupModel     = $this->_popupFactory->create();
        try {
            $popup_id = $this->_request->getParam('id');
            if ($popup_id) {
                $this->popupResources->load($popupModel, $popup_id);
                if (!$popupModel->getPopupId()) {
                    $this->messageManager->addErrorMessage(__('This Popup doesn\'t exist'));
                    return $this->resultRedirectFactory->create()->setPath('*/*/index');
                }

                $widgetInstanceData = $this->json->unserialize($popupModel['widget_instance']);
                $pageGroupData      = [];
                if (!empty($widgetInstanceData)) {
                    foreach ($widgetInstanceData as $data) {
                        $pageGroupData[] = array_merge(
                            $data[$data['page_group']],
                            ['page_group' => $data['page_group']]
                        );
                    }
                }
                $widgetInstance->setPageGroups($pageGroupData);
            }
        } catch (\Exception $exception) {
            $this->messageManager->addErrorMessage($exception->getMessage());
            $this->_logger->critical($exception->getMessage());
        }
        $this->_coreRegistry->unregister('current_widget_instance');
        $this->_coreRegistry->register('current_widget_instance', $widgetInstance);
        $this->_coreRegistry->register('popup', $popupModel);

        $resultPage = $this->resultPageFactory->create();
        $resultPage->getConfig()->getTitle()->prepend(
            $popupModel->getPopupId() ? __($popupModel->getPopupName()) : __('New Popup')
        );
        return $resultPage;
    }
}
