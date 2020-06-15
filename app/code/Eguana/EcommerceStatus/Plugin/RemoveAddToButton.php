<?php
/**
 * @author Eguana Team
 * @copyriht Copyright (c) 2020 Eguana {http://eguanacommerce.com}
 * Created by PhpStorm
 * User: yasir
 * Date: 6/12/20
 * Time: 6:11 AM
 */

namespace Eguana\EcommerceStatus\Plugin;

use Eguana\EcommerceStatus\Helper\Data;
use Magento\Catalog\Model\Product;

/**
 * this class is used for Remove cart button
 *
 * Class RemoveAddToButton
 * Eguana\EcommerceStatus\Plugin
 */
class RemoveAddToButton
{

    /**
     * @var Data
     */
    private $helperData;

    /**
     * RemoveAddToButton constructor.
     * @param Data $helperData
     */
    public function __construct(
        Data $helperData
    ) {
        $this->helperData = $helperData;
    }

    /**
     * this after plugin is used to get config store value
     * @param Product $product
     * @param $result
     * @return array
     */
    public function afterIsSaleable(Product $product, $result)
    {
        if (!$this->helperData->getECommerceStatus()) {
            return false;
        }
        return $result;
    }
}
