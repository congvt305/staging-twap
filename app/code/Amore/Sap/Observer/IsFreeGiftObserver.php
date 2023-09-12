<?php

namespace Amore\Sap\Observer;

use Magento\Catalog\Model\Product\Action as ProductAction;
use Magento\Framework\Event\ObserverInterface;
use Psr\Log\LoggerInterface;

class IsFreeGiftObserver  implements ObserverInterface
{
    /**
     * @var ProductAction
     */
    protected $productAction;

    /**
     * @var Logger
     */
    protected $logger;

    /**
     * @param LoggerInterface $logger
     * @param ProductAction $productAction
     */
    public function __construct(
        LoggerInterface $logger,
        ProductAction $productAction
    ) {
        $this->logger = $logger;
        $this->productAction = $productAction;
    }

    /**
     * @param \Magento\Framework\Event\Observer $observer
     * @return void
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        try {
            $product = $observer->getProduct();
            if ($product->getEntityId() && $product->getPrice() == 0 && $product->getTypeId() == 'simple') {
                $product->setIsFreeGift(1);
            } else {
                $product->setIsFreeGift(0);
            }
        } catch (\Exception $exception) {
            $this->logger->error('Cannot update product free gift: ' . $exception->getMessage());
        }
    }

}
