<?php
/**
 * Copyright � 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Used in creating options for Payment URL config value selection
 *
 */
namespace Eghl\PaymentMethod\Model\Config\Source;

class PaymentURL implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        return [
            ['value' => 'https://pay.e-ghl.com/ipgsg/payment.aspx', 'label' => __('Test payment URL (MY)')],
            ['value' => 'http://test2ph.ghl.com:86/IPGSG/Payment.aspx', 'label' => __('Test payment URL (PH)')],
            ['value' => 'https://securepay.e-ghl.com/IPG/Payment.aspx', 'label' => __('Production payment URL')],
            ['value' => 'http://localhost:8080/IPGLocal/Payment.aspx', 'label' => __('Localhost')]
        ];
    }

    /**
     * Get options in "key-value" format
     *
     * @return array
     */
    public function toArray()
    {
        return [
            'https://pay.e-ghl.com/ipgsg/payment.aspx' => __('Test payment URL (MY)'),
            'http://test2ph.ghl.com:86/IPGSG/Payment.aspx' => __('Test payment URL (PH)'),
            'https://securepay.e-ghl.com/IPG/Payment.aspx' => __('Production payment URL'),
            'http://localhost:8080/IPGLocal/Payment.aspx' => __('Localhost')
        ];
    }
}
