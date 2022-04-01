<?php

namespace Amasty\Feed\Block\Adminhtml\Field\Edit\Button;

class Save extends Generic
{
    /**
     * {@inheritdoc}
     */
    public function getButtonData()
    {
        return [
            'label' => __('Save'),
            'class' => 'save primary',
            'on_click' => ''
        ];
    }
}
