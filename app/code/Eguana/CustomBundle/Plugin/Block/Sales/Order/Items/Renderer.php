<?php
/**
 * @author Eguana Team
 * @copyriht Copyright (c) 2020 Eguana {http://eguanacommerce.com}
 * Created by PhpStorm
 * User: arslan
 * Date: 15/9/20
 * Time: 5:50 PM
 */
namespace Eguana\CustomBundle\Plugin\Block\Sales\Order\Items;

use Magento\Bundle\Block\Sales\Order\Items\Renderer as RendererAlias;

/**
 * This class is used to change the bnundle product
 * Class Renderer
 */
class Renderer
{
    /**
     * After Plugin for GetValueHtml
     * This plugin change the bundle product options format
     * @param RendererAlias $subject
     * @param $result
     * @param $item
     * @return string
     */
    public function afterGetValueHtml(RendererAlias $subject, $result, $item)
    {
        if ($attributes = $subject->getSelectionAttributes($item)) {
            return sprintf($subject->escapeHtml($item->getName()) . ' x ' . '%d', $attributes['qty']) . " "
                . $subject->getOrder()->formatPrice($attributes['price']);
        }
        return $subject->escapeHtml($item->getName());
    }
}
