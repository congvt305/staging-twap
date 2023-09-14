<?php

declare(strict_types=1);

namespace Amore\Sap\Plugin;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\QuoteManagement;

class CheckFreeGiftItem
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
     * Before submitOrder plugin.
     *
     * @param QuoteManagement $subject
     * @param Quote $quote
     * @param array $orderData
     * @return void
     * @throws LocalizedException
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function beforeSubmit(
        QuoteManagement $subject,
        Quote $quote,
        array $orderData = []
    ): void {
        //Must use this because LinePay mobile cannot go through observer checkout_submit_before
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
