<?php
/**
 * @author Eguana Team
 * @copyriht Copyright (c) 2020 Eguana {http://eguanacommerce.com}
 * Created by PhpStorm
 * User: raheel
 * Date: 9/2/21
 * Time: 5:12 AM
 */
namespace Eguana\FacebookPixel\Observer;

use Eguana\FacebookPixel\Helper\Data;
use Eguana\FacebookPixel\Model\SessionFactory;
use Magento\Framework\Event\ObserverInterface;
use Magento\Catalog\Model\ProductRepository;
use Magento\Framework\Event\Observer;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use Magento\Sales\Model\Order\Item;
use Psr\Log\LoggerInterface;

/**
 * Add To Cart observer class
 *
 * Class AddToCart
 */
class AddToCart implements ObserverInterface
{
    /**
     * @var SessionFactory
     */
    private $fbPixelSession;

    /**
     * @var Data
     */
    private $helper;

    /**
     * @var ProductRepository
     */
    private $productRepository;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @param Data $helper
     * @param SessionFactory $fbPixelSession
     * @param LoggerInterface $logger
     * @param ProductRepository $productRepository
     */
    public function __construct(
        Data $helper,
        SessionFactory $fbPixelSession,
        LoggerInterface $logger,
        ProductRepository $productRepository
    ) {
        $this->helper = $helper;
        $this->logger = $logger;
        $this->fbPixelSession = $fbPixelSession;
        $this->productRepository = $productRepository;
    }

    /**
     * Execute method
     *
     * @param Observer $observer
     * @return bool
     */
    public function execute(Observer $observer)
    {
        try {
            $items = $observer->getItems();
            $typeConfi = Configurable::TYPE_CODE;
            if (!$this->helper->isAddToCart() || !$items) {
                return true;
            }
            $product = [
                'content_ids' => [],
                'value' => 0,
                'currency' => ""
            ];

            /** @var Item $item */
            foreach ($items as $item) {
                if ($item->getProduct()->getTypeId() == $typeConfi) {
                    continue;
                }
                if ($item->getParentItem()) {
                    if ($item->getParentItem()->getProductType() == $typeConfi) {
                        $product['contents'][] = [
                            'id' => $item->getSku(),
                            'name' => $item->getName(),
                            'quantity' => $item->getParentItem()->getQtyToAdd()
                        ];
                        $product['value'] += $item->getProduct()->getFinalPrice()
                            *$item->getParentItem()->getQtyToAdd();
                    } else {
                        $product['contents'][] = [
                            'id' => $item->getSku(),
                            'name' => $item->getName(),
                            'quantity' => $item->getData('qty')
                        ];
                    }
                } else {
                    $product['contents'][] = [
                        'id' => $this->checkBundleSku($item),
                        'name' => $item->getName(),
                        'quantity' => $item->getQtyToAdd()
                    ];
                    $product['value'] += $item->getProduct()->getFinalPrice() * $item->getQtyToAdd();
                }
                $product['content_ids'][] = $this->checkBundleSku($item);
            }

            $data = [
                'content_type' => 'product',
                'content_ids' => $product['content_ids'],
                'contents' => $product['contents'],
                'currency' => $this->helper->getCurrencyCode(),
                'value' => $product['value']
            ];

            $this->fbPixelSession->create()->setAddToCart($data);
        } catch (\Exception $e) {
            $this->logger->error($e->getMessage());
        }
        return true;
    }

    /**
     * Check Bundle Sku
     *
     * @param $item
     * @return string
     */
    private function checkBundleSku($item)
    {
        try {
            $typeBundle = \Magento\Bundle\Model\Product\Type::TYPE_CODE;
            if ($item->getProductType() == $typeBundle) {
                $skuBundleProduct = $this->productRepository->getById($item->getProductId())->getSku();
                return $skuBundleProduct;
            }
        } catch (\Exception $e) {
            $this->logger->error($e->getMessage());
        }
        return $item->getSku();
    }
}
