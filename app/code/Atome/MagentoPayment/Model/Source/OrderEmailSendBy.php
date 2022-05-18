<?php
/**
 * Atome Payment Module for Magento 2
 *
 * @author Atome
 * @copyright 2020 Atome
 */
namespace Atome\MagentoPayment\Model\Source;

class OrderEmailSendBy implements \Magento\Framework\Option\ArrayInterface
{
    public function toOptionArray()
    {
        $result[] = ['value' => 'default', 'label' => 'Default'];
        $result[] = ['value' => 'atome', 'label' => 'Atome'];
        return $result;
    }
}

