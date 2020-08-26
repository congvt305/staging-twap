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
        $product = $item->getProduct();
        if ($product->getCustomAttribute('prdvl')) {
            $data['ap_size'] = $product->getPrdvl().$product->getAttributeText('vlunt');

            if ($product->getCustomAttribute('product_count')) {
                $size = $data['ap_size'];
                $data['ap_size'] = $size.'*'.$product->getAttributeText('product_count');
            }
        } else {
            $data['ap_size'] = '';
        }

        return array_merge(
            $result,
            $data
        );
    }
}
