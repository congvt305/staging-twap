<?php
    namespace Hoolah\Hoolah\Model\Config\Source;
    
    class SendLogsTimeScope implements \Magento\Framework\Option\ArrayInterface
    {
        // const
        const SCOPE_1 = 1;
        const SCOPE_7 = 7;
        const SCOPE_14 = 14;
        const SCOPE_30 = 30;
        const SCOPE_45 = 45;
        
        /**
         * {@inheritdoc}
         */
        public function toOptionArray()
        {
            return [
                [
                    'value' => self::SCOPE_45,
                    'label' => __('Last 45 days')
                ],
                [
                    'value' => self::SCOPE_30,
                    'label' => __('Last 30 days')
                ],
                [
                    'value' => self::SCOPE_14,
                    'label' => __('Last 14 days')
                ],
                [
                    'value' => self::SCOPE_7,
                    'label' => __('Last 7 days (recommended)')
                ],
                [
                    'value' => self::SCOPE_1,
                    'label' => __('Last day')
                ]
            ];
        }
    }
