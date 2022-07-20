<?php
/**
 * Atome Payment Module for Magento 2
 *
 * @author Atome
 * @copyright 2020 Atome
 */
namespace Atome\MagentoPayment\Model\Source;

class ApiEnv implements \Magento\Framework\Option\ArrayInterface
{
    public function toOptionArray()
    {
        $result[] = ['value' => 'test', 'label' => 'Test'];
        $result[] = ['value' => 'production', 'label' => 'Production'];
        return $result;
    }
}

