<?php

namespace Payoo\PayNow\Model\Adminhtml\Source;

class EnvironmentSource implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * {@inheritdoc}
     */
    public function toOptionArray()
    {
        return [
            [
                'value' =>  '118.69.56.194',
                'label' => __('Sandbox')
            ],
            [
                'value' =>  '118.69.206.8',
                'label' => __('Production')
            ]
        ];
    }
}