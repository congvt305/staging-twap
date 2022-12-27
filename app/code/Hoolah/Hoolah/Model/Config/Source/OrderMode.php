<?php
    namespace Hoolah\Hoolah\Model\Config\Source;
    
    class OrderMode implements \Magento\Framework\Option\ArrayInterface
    {
        // const
        const MODE_PAYMENT_ORDER = 'p2o';
        const MODE_ORDER_PAYMENT = 'o2p';
        
        /**
         * {@inheritdoc}
         */
        public function toOptionArray()
        {
            return [
                [
                    'value' => self::MODE_PAYMENT_ORDER,
                    'label' => __('Order conversion after payment completed (default)')
                ],
                [
                    'value' => self::MODE_ORDER_PAYMENT,
                    'label' => __('Order conversion prior payment')
                ]
            ];
        }
    }
