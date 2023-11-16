<?php
declare(strict_types=1);

namespace Amasty\ShopByQuickConfig\Ui\Component;

use Magento\Framework\View\Element\UiComponent\Control\ButtonProviderInterface;

class AddButton implements ButtonProviderInterface
{
    /**
     * @return array
     */
    public function getButtonData(): array
    {
        return [
            'label' => __('Add/Remove Filters'),
            'class' => 'action-secondary',
            'on_click' => '',
            'data_attribute' => [
                'mage-init' => [
                    'Magento_Ui/js/form/button-adapter' => [
                        'actions' => [
                            [
                                'targetName' => 'index = add_attribute_modal',
                                'actionName' => 'openModal'
                            ],
                        ],
                    ],
                ],
            ],
            'sort_order' => 10
        ];
    }
}
