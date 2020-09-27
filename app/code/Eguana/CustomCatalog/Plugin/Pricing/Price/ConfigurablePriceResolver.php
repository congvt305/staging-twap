<?php
/**
 * @author Eguana Team
 * @copyright Copyright (c) 2020 Eguana {http://eguanacommerce.com}
 * Created by PhpStorm
 * User: umer
 * Date: 24/9/20
 * Time: 12:15 PM
 */
namespace Eguana\CustomCatalog\Plugin\Pricing\Price;

use Magento\Framework\Pricing\SaleableInterface;
use Magento\ConfigurableProduct\Pricing\Price\ConfigurablePriceResolver as ConfigurablePriceResolverAlias;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Psr\Log\LoggerInterface;

/**
 * Class SalableResolver
 *
 * Show out of stock configurable child products price
 */
class ConfigurablePriceResolver
{
    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * ConfigurablePriceResolver constructor.
     * @param ProductRepositoryInterface $productRepository
     * @param LoggerInterface $logger
     */
    public function __construct(
        ProductRepositoryInterface $productRepository,
        LoggerInterface $logger
    ) {
        $this->productRepository = $productRepository;
        $this->logger = $logger;
    }

    /**
     * Show lowest price when configurable product childs are out of stock
     * @param ConfigurablePriceResolverAlias $subject
     * @param $result
     * @param SaleableInterface $product
     * @return float
     */
    public function afterResolvePrice(
        ConfigurablePriceResolverAlias $subject,
        $result,
        SaleableInterface $product
    ) {
        if ($result == null) {
            $price = null;
            try {
                $productId = $product->getId();
                $parentProduct = $this->productRepository->getById($productId);
            } catch (\Exception $exception) {
                $this->logger->error($exception->getMessage());
            }
            $children = $parentProduct->getTypeInstance()->getUsedProducts($parentProduct);
            foreach ($children as $child) {
                $productPrice = $child->getPrice();
                $price = isset($price) ? min($price, $productPrice) : $productPrice;
            }
            return (float)$price;
        }
        return $result;
    }
}
