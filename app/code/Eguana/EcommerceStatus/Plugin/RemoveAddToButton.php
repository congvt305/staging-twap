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
 * PLEASE ENTER ONE LINE SHORT DESCRIPTION OF CLASS
 *
 * Class RemoveAddToButton
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
     * SHORT DESCRIPTION
     * LONG DESCRIPTION LINE BY LINE
     * @param Product $product
     * @param $result
     * @return array
     */
    public function afterIsSaleable(Product $product, $result)
    {
        if (!$this->helperData->getECommerceStatus()) {
            return [];
        }
        return $result;
    }
}
