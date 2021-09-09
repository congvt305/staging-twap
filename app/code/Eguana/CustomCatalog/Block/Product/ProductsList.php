<?php
/**
 * @author Eguana Team
 * @copyriht Copyright (c) 2020 Eguana {http://eguanacommerce.com}
 * Created by PhpStorm
 * User: abbas
 * Date: 20. 8. 20
 * Time: 오후 12:14
 */

namespace Eguana\CustomCatalog\Block\Product;

use Magento\Catalog\Api\CategoryRepositoryInterface;
use Magento\Catalog\Model\Product\Visibility;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Magento\CatalogWidget\Model\Rule;
use Magento\Framework\App\Http\Context;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\Url\EncoderInterface;
use Magento\Framework\View\LayoutFactory;
use Magento\Rule\Model\Condition\Sql\Builder;
use Magento\Widget\Helper\Conditions;

/**
 * To get the default name of the prdouct
 * Class ProductList
 * @package Eguana\CustomCatalog\Block\Product
 */
class ProductsList extends \Magento\CatalogWidget\Block\Product\ProductsList
{
    /**
     * @var \Magento\Catalog\Api\ProductRepositoryInterface
     */
    private $productRepository;

    public function __construct(
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
        \Magento\Catalog\Block\Product\Context $context,
        CollectionFactory $productCollectionFactory,
        Visibility $catalogProductVisibility,
        Context $httpContext,
        Builder $sqlBuilder,
        Rule $rule,
        Conditions $conditionsHelper,
        CategoryRepositoryInterface $categoryRepository,
        array $data = [],
        Json $json = null,
        LayoutFactory $layoutFactory = null,
        EncoderInterface $urlEncoder = null
    ) {
        parent::__construct(
            $context,
            $productCollectionFactory,
            $catalogProductVisibility,
            $httpContext,
            $sqlBuilder,
            $rule,
            $conditionsHelper,
            $categoryRepository,
            $data,
            $json,
            $layoutFactory,
            $urlEncoder
        );
        $this->productRepository = $productRepository;
    }

    /**
     * Return the product default name against sku
     * @param $productSku
     * @return string|null
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getProductDefaultName($productSku)
    {
        $prdouct = $this->productRepository->get($productSku, false, 0);
        return $prdouct->getName();
    }
}