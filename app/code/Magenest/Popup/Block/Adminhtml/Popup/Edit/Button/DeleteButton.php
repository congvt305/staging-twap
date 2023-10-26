<?php
namespace Magenest\Popup\Block\Adminhtml\Popup\Edit\Button;

use Magento\Framework\View\Element\UiComponent\Control\ButtonProviderInterface;

class DeleteButton extends GenericButton implements ButtonProviderInterface
{
    /**
     * Get Button Data
     *
     * @return array
     */
    public function getButtonData()
    {
        $data = [];
        if ($this->getPopupId() || $this->getPopupTemplateId()) {
            $data = [
                'label' => __('Delete'),
                'class' => 'delete',
                'on_click' => 'deleteConfirm(\'' . __('Are you sure you want to do this?')
                    . '\', \'' . $this->getDeleteUrl() . '\', {data: {}})',
            ];
        }
        return $data;
    }

    /**
     * Get Delete URL
     *
     * @return string
     */
    private function getDeleteUrl()
    {
        return $this->urlBuilder->getUrl('*/*/delete', ['id' => $this->getPopupId() ?? $this->getPopupTemplateId()]);
    }
}
