<?php

/**
 * Atome Payment Module for Magento 2
 *
 * @author Atome
 * @copyright 2020 Atome
 */

namespace Atome\MagentoPayment\Model\Source;

class OrderCreatedWhen implements \Magento\Framework\Option\ArrayInterface
{
  public function toOptionArray()
  {
    $result[] = ['value' => 'paid_successfully', 'label' => 'Paid successfully(default)'];
    $result[] = ['value' => 'before_paying', 'label' => 'Before paying'];
    return $result;
  }
}
