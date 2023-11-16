<?php

declare(strict_types=1);

namespace Amasty\ShopbyFilterAnalytics\Plugin\CatalogGraphQl\Model\Resolver\Category\Products;

use Amasty\ShopbyFilterAnalytics\Model\CollectGraphQlFilterData;
use Magento\CatalogGraphQl\Model\Resolver\Category\Products;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\GraphQl\Model\Query\Context;

class CollectFilterData
{
    /**
     * @var CollectGraphQlFilterData
     */
    private $collectGraphQlFilterData;

    public function __construct(CollectGraphQlFilterData $collectGraphQlFilterData)
    {
        $this->collectGraphQlFilterData = $collectGraphQlFilterData;
    }

    /**
     * @param Products $products
     * @param Field $field
     * @param Context $context
     * @param ResolveInfo $info
     * @param array|null $value
     * @param array|null $args
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function beforeResolve(
        Products $products,
        Field $field,
        Context $context,
        ResolveInfo $info,
        array $value = null,
        array $args = null
    ): void {
        if (isset($args['filter'])) {
            $this->collectGraphQlFilterData->execute($args['filter'], null);
        }
    }
}
