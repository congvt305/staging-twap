<?php

declare(strict_types=1);

namespace Amasty\ShopbyFilterAnalytics\Ui\Component\Form\Button;

use Amasty\ShopbyFilterAnalytics\Model\ConfigProvider;
use Magento\Framework\View\Element\UiComponent\Control\ButtonProviderInterface;

class FilterAnalytics implements ButtonProviderInterface
{
    /**
     * @var ConfigProvider
     */
    private $configProvider;

    public function __construct(ConfigProvider $configProvider)
    {
        $this->configProvider = $configProvider;
    }

    /**
     * Get button data
     *
     * @return array
     */
    public function getButtonData(): array
    {
        if (!$this->configProvider->isAnalyticsEnabled()) {
            return [];
        }

        return [
            'label' => __('Filter Analytics'),
            'class' => 'action-primary',
            'data_attribute' => [
                'mage-init' => [
                    'Magento_Ui/js/form/button-adapter' => [
                        'actions' => [
                            [
                                'targetName' => 'amasty_shopby_filters.amasty_shopby_filters.analytics_modal',
                                'actionName' => 'openModal'
                            ]
                        ]
                    ]
                ]
            ],
            'on_click' => ''
        ];
    }
}
