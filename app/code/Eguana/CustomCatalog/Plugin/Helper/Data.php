<?php
/**
 * @author Eguana Team
 * @copyright Copyright (c) 2020 Eguana {http://eguanacommerce.com}
 * Created by PhpStorm
 * User: umer
 * Date: 16/9/20
 * Time: 8:40 PM
 */
namespace Eguana\CustomCatalog\Plugin\Helper;

use Magento\ConfigurableProduct\Helper\Data as ConfigurableProductHelper;

/**
 * Class Data
 *
 * Show configurable product out of stock prices
 */
class Data
{

    /**
     * Set is salable to 1 for out of stock child products of configurable product
     *
     * @param ConfigurableProductHelper $subject
     * @param $currentProduct
     * @param $allowedProducts
     */
    public function beforeGetOptions(ConfigurableProductHelper $subject, $currentProduct, $allowedProducts)
    {
        foreach ($allowedProducts as $product) {
            if (!$product->isSalable()) {
                $product['is_salable'] = '1';
            }
        }
        return [$currentProduct, $allowedProducts];
    }
}
