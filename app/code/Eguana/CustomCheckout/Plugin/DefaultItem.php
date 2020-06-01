<?php
/**
 * Created by PhpStorm.
 * User: hyuna
 * Date: 2020-05-25
 * Time: 오후 6:43
 */

namespace Eguana\CustomCheckout\Plugin;

use Magento\Quote\Model\Quote\Item;
use \Magento\Checkout\CustomerData\AbstractItem;

class DefaultItem
{
    public function afterGetItemData(AbstractItem $subject, $result, Item $item)
    {
        $data['laneige_size'] = $item->getProduct()->getAttributeText('laneige_size');

        return array_merge(
            $result,
            $data
        );
    }
}
