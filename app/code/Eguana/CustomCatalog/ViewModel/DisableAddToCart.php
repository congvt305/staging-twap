<?php
/**
 * @author Eguana Team
 * @copyright Copyright (c) 2020 Eguana {http://eguanacommerce.com}
 * Created by PhpStorm
 * User: umer
 * Date: 30/9/20
 * Time: 7:57 PM
 */
namespace Eguana\CustomCatalog\ViewModel;

use Magento\Framework\View\Element\Block\ArgumentInterface;
use Psr\Log\LoggerInterface;

/**
 * Class DisableAddToCart
 *
 * Disable add to cart when any of bundle child is out of stock
 */
class DisableAddToCart implements ArgumentInterface
{
    /**
     * @var \Magento\CatalogInventory\Model\Stock\StockItemRepository
     */
    private $stock;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * DisableAddToCart constructor.
     * @param \Magento\CatalogInventory\Model\Stock\StockItemRepository $stock
     */
    public function __construct(
        \Magento\CatalogInventory\Model\Stock\StockItemRepository $stock,
        LoggerInterface $logger
    ) {
        $this->stock = $stock;
        $this->logger = $logger;
    }

    /**
     * Disable add to cart when any of bundle child is out of stock
     * @param $product
     * @return bool
     */
    public function getBundleItemsStockStatus($product)
    {
        $outofstock = false;
        $optionsCollection = $product->getTypeInstance(true)
            ->getSelectionsCollection(
                $product->getTypeInstance(true)->getOptionsIds($product),
                $product
            );
        $options = $product->getTypeInstance(true)->getOptionsIds($product);
        $newOptions = [];
        foreach ($options as $option) {
            $optionProducts = [];
            foreach ($optionsCollection as $subproduct) {
                if ($subproduct->getOptionId() == $option) {
                    $data['entity_id'] = $subproduct->getEntityId();
                    $data['is_default'] = $subproduct->getIsDefault();
                    $data['option_id'] = $subproduct->getOptionId();
                    $optionProducts[] = $data;
                }
            }
            $newOptions[] = $optionProducts;
        }
        $productIds = [];
        if (count($newOptions) >= 2) {
            foreach ($newOptions as $suboptions) {
                foreach ($suboptions as $defaultoption) {
                    if ($defaultoption['is_default'] == 1) {
                        $productIds[] = $defaultoption['entity_id'];
                    }
                }
            }
        } else {
            $productIds[] = $optionProducts[0];
        }
        foreach ($productIds as $productId) {
            $id = $productId;
            try {
                $productStock = $this->stock->get($id);
            } catch (\Exception $exception) {
                $this->logger->error($exception->getMessage());
            }
            if (!$productStock->getIsInStock()) {
                $outofstock = true;
                break;
            }
        }
        return $outofstock;
    }
}
