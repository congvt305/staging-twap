<?php
/**
 * @author Eguana Team
 * @copyright Copyright (c) 2020 Eguana {http://eguanacommerce.com}
 * Created by PhpStorm
 * User: umer
 * Date: 24/9/20
 * Time: 12:05 PM
 */
namespace Eguana\CustomCatalog\Plugin\Model\Product\Pricing\Renderer;

use Magento\ConfigurableProduct\Model\Product\Type\Configurable as TypeConfigurable;
use Magento\Catalog\Model\Product\Pricing\Renderer\SalableResolver as CatalogSalableResolver;
use Magento\Framework\Pricing\SaleableInterface;

/**
 * Class SalableResolver
 *
 * Show out of stock configurable product price
 */
class SalableResolver
{
    /**
     * Return true to show out of stock configurable product price
     * @param CatalogSalableResolver $subject
     * @param $result
     * @param SaleableInterface $salableItem
     * @return bool
     */
    public function afterIsSalable(
        CatalogSalableResolver $subject,
        $result,
        SaleableInterface $salableItem
    ) {
        if ($salableItem->getTypeId() == 'configurable') {
            return true;
        }
        return $result;
    }
}
