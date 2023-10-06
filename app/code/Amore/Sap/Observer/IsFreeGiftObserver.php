<?php

namespace Amore\Sap\Observer;

use Magento\Catalog\Model\ProductRepository;
use Magento\Framework\Event\ObserverInterface;
use Psr\Log\LoggerInterface;

class IsFreeGiftObserver  implements ObserverInterface
{
    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @var ProductRepository
     */
    private $productRepository;

    /**
     * @param LoggerInterface $logger
     * @param ProductRepository $productRepository
     */
    public function __construct(
        LoggerInterface $logger,
        ProductRepository $productRepository
    ) {
        $this->logger = $logger;
        $this->productRepository = $productRepository;
    }

    /**
     * @param \Magento\Framework\Event\Observer $observer
     * @return void
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        try {
            $product = $observer->getProduct();
            $price = $product->getPrice();
            if ($product->getTypeId() == 'simple') {
                if ($price == null) {
                    $product->setIsFreeGift(null); // remove store view config
                } elseif ($price == 0) {
                    $product->setIsFreeGift(1);
                } else {
                    $product->setIsFreeGift(0);
                }
            }


        } catch (\Exception $exception) {
            $this->logger->error('Cannot update product free gift: ' . $exception->getMessage());
        }
    }

}
