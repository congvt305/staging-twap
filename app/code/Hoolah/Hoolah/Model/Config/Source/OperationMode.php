<?php
    namespace Hoolah\Hoolah\Model\Config\Source;
    
    class OperationMode implements \Magento\Framework\Option\ArrayInterface
    {
        // const
        const MODE_TEST = 'Test_mode';
        const MODE_LIVE = 'Live';
        
        /**
         * {@inheritdoc}
         */
        public function toOptionArray()
        {
            return [
                [
                    'value' => self::MODE_TEST,
                    'label' => __('Sandbox')
                ],
                [
                    'value' => self::MODE_LIVE,
                    'label' => __('Production')
                ]
            ];
        }
    }
