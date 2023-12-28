<?php
namespace Magenest\Popup\Controller\Adminhtml\Template;

use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\ResultInterface;

class Save extends Template
{
    /**
     * Execute
     *
     * @return ResponseInterface|ResultInterface|void
     */
    public function execute()
    {
        $params = $this->getRequest()->getParams();
        try {
            $popupTemplate = $this->_popupTemplateFactory->create();
            $template_name = $params['template_name'];
            $html_content = $params['html_content'];
            $css_style = $params['css_style'];
            if (!empty($params['template_id'])) {
                $this->popupTemplateResources->load($popupTemplate, $params['template_id']);
                $template_name_before_edit = $popupTemplate->getTemplateName();
                $template_type = $popupTemplate->getTemplateType();
                $html_content_before_edit = $popupTemplate->getHtmlContent();
                $css_style_before_edit = $popupTemplate->getCssStyle();
                if ($template_name !== $template_name_before_edit ||
                    $html_content !== $html_content_before_edit ||
                    $css_style !== $css_style_before_edit) {
                    $status = $popupTemplate->getStatus();
                    if ($status == 1 || $status == 2) {
                        $popupTemplate->setStatus(2);
                    } else {
                        $popupTemplate->setStatus(0);
                    }
                }
            } else {
                $template_type = $params['template_type'];
            }
            $popupTemplate->setTemplateName($template_name);
            $popupTemplate->setTemplateType($template_type);
            $popupTemplate->setHtmlContent($html_content);
            $popupTemplate->setCssStyle($css_style);
            $this->_eventManager->dispatch('save_template', ['template' => $popupTemplate]);
            $this->popupTemplateResources->save($popupTemplate);
            $this->_redirect('*/*/index');
            $this->messageManager->addSuccessMessage(__('The Popup Template template has been saved.'));
        } catch (\Exception $e) {
            $this->_logger->critical($e);
            $this->messageManager->addErrorMessage($e->getMessage());
        }
    }
}
