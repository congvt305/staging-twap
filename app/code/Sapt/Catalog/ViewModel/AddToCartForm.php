<?php
declare(strict_types=1);

namespace Sapt\Catalog\ViewModel;

use Magento\Framework\View\Element\Block\ArgumentInterface;
use Magento\CatalogWidget\Block\Product\ProductsList;
use Magento\Framework\Serialize\Serializer\Json;

/**
 * Class AddToCartForm
 * Generate add to cart form data
 */
class AddToCartForm implements ArgumentInterface
{
    /**
     * @var ProductsList
     */
    protected ProductsList $productsListBlock;

    /**
     * @var Json
     */
    protected Json $serializer;

    /**
     * @param ProductsList $productsListBlock
     * @param Json $serializer
     */
    public function __construct(
        ProductsList $productsListBlock,
        Json $serializer
    )
    {
        $this->productsListBlock = $productsListBlock;
        $this->serializer = $serializer;
    }

    /**
     * @param $product
     * @return string
     */
    public function getAddToCartPostParams($product, $reloadSetting = []): string
    {
        $params = $this->productsListBlock->getAddToCartPostParams($product);
        if (!empty($reloadSetting)) {
            $params['reload'] = $reloadSetting;
        }
        return $this->serializer->serialize($params);
    }
}
