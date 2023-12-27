<?php
namespace Magenest\Popup\Block\Adminhtml\Popup\Edit\Button;

use Magento\Framework\View\Element\UiComponent\Control\ButtonProviderInterface;

class TemplatePreviewButton extends GenericButton implements ButtonProviderInterface
{
    /**
     * Get Button Data
     *
     * @return array
     */
    public function getButtonData()
    {
        $data = [];
        if ($this->getPopupId()) {
            $data = [
                'label' => __('Preview Popup'),
                'on_click' => sprintf(
                    "require(['uiRegistry'], function(uiRegistry) {let content = uiRegistry.get('index = html_content').value();window.open('%s&background_image='+(document.getElementsByClassName('preview-link')[0]?document.getElementsByClassName('preview-link')[0].href : '0')+'&template_id='+document.getElementsByTagName('select')['popup_template_id'].value+'&html_content='+escape(content))})",
                    $this->getUrlPreview()
                )
            ];
        }
        return $data;
    }

    /**
     * Get Preview URL
     *
     * @return string
     */
    public function getUrlPreview()
    {
        return $this->urlBuilder->getBaseUrl() . 'magenest_popup/popup/preview?popup_id=' . $this->getPopupId();
    }
}
