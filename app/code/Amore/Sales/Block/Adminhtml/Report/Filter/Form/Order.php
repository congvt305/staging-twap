<?php
declare(strict_types=1);

namespace Amore\Sales\Block\Adminhtml\Report\Filter\Form;

class Order extends \Magento\Sales\Block\Adminhtml\Report\Filter\Form\Order
{
    const SHOW_ORDER_STATUS = [
        'canceled',
        'closed',
        'complete',
        'delivery_complete',
        'processing',
        'shipment_processing'
    ];

    /**
     * Preparing form
     *
     * @return \Magento\Sales\Block\Adminhtml\Report\Filter\Form\Order
     * @SuppressWarnings(PHPMD.UnusedLocalVariable)
     */
    protected function _prepareForm()
    {
        parent::_prepareForm();
        /** @var \Magento\Framework\Data\Form\Element\Fieldset $fieldset */
        $fieldset = $this->getForm()->getElement('base_fieldset');

        if (is_object($fieldset) && $fieldset instanceof \Magento\Framework\Data\Form\Element\Fieldset) {
            $fieldset->getElements()->remove('order_statuses');
            $this->getForm()->removeField('order_statuses');

            $statuses = $this->_orderConfig->create()->getStatuses();
            $values = [];
            foreach ($statuses as $code => $label) {
                if (false === strpos($code, 'pending') && in_array($code, self::SHOW_ORDER_STATUS)) {
                    $values[] = ['label' => __($label), 'value' => $code];
                }
            }
            $fieldset->addField(
                'order_statuses',
                'multiselect',
                [
                    'name' => 'order_statuses',
                    'label' => '',
                    'values' => $values,
                    'display' => 'none'
                ],
                'show_order_statuses'
            );
        }
        return $this;
    }
}
