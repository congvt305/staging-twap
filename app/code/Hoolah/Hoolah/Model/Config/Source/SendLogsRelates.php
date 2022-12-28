<?php
    namespace Hoolah\Hoolah\Model\Config\Source;
    
    class SendLogsRelates implements \Magento\Framework\Option\ArrayInterface
    {
        // const
        const RELATES_TICKET = 'ticket';
        const RELATES_TEAM = 'team';
        
        /**
         * {@inheritdoc}
         */
        public function toOptionArray()
        {
            return [
                [
                    'value' => null,
                    'label' => __('')
                ],
                [
                    'value' => self::RELATES_TICKET,
                    'label' => __('Existing merchant support ticket (please specify ticket reference)')
                ],
                [
                    'value' => self::RELATES_TEAM,
                    'label' => __('As per request by ShopBack PayLater team (please specify name)')
                ]
            ];
        }
    }
