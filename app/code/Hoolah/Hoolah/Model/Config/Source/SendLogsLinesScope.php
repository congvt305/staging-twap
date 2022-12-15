<?php
    namespace Hoolah\Hoolah\Model\Config\Source;
    
    class SendLogsLinesScope implements \Magento\Framework\Option\ArrayInterface
    {
        // const
        const SCOPE_HOOLAH = 'hoolah';
        const SCOPE_ALL = 'all';
        
        /**
         * {@inheritdoc}
         */
        public function toOptionArray()
        {
            return [
                [
                    'value' => self::SCOPE_HOOLAH,
                    'label' => __('Created by ShopBack PayLater only (recommended)')
                ],
                [
                    'value' => self::SCOPE_ALL,
                    'label' => __('All (only if requested by our team)')
                ]
            ];
        }
    }
