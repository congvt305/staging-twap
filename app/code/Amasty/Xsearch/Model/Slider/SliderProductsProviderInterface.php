<?php
/**
* @author Amasty Team
* @copyright Copyright (c) 2022 Amasty (https://www.amasty.com)
* @package Advanced Search Base for Magento 2
*/

declare(strict_types=1);

namespace Amasty\Xsearch\Model\Slider;

use Magento\Catalog\Model\Product;

interface SliderProductsProviderInterface
{
    /**
     * @return Product[]
     */
    public function getProducts(): iterable;
}
