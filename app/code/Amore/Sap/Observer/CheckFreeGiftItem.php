<?php
declare(strict_types=1);
namespace Amore\Sap\Observer;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\Event\Observer as EventObserver;
use Magento\Framework\Event\ObserverInterface;

class CheckFreeGiftItem implements ObserverInterface
{
    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * @param ProductRepositoryInterface $productRepository
     */
    public function __construct(
        ProductRepositoryInterface $productRepository
    ) {
        $this->productRepository = $productRepository;
    }

    /**
     * @param EventObserver $observer
     * @return void
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function execute(EventObserver $observer)
    {
        $quote = $observer->getEvent()->getQuote();
        foreach ($quote->getAllItems() as $item) {
            if ($item->getProductType() == 'bundle' || $item->getProductType() == 'configurable') {
                continue;
            }
            $product = $this->productRepository->getById($item->getProductId());
            if ($product->getPrice() == 0 || $product->getIsFreeGift()) {
                $item->setIsFreeGift(1);
            }
        }
    }
}
