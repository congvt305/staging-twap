<?php
declare(strict_types=1);

namespace Amore\Currency\Observer;

use Amore\Currency\Model\PriceCurrency;
use Magento\Catalog\Model\Product;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

class RoundFinalPrice implements ObserverInterface
{
    /**
     * @var PriceCurrency
     */
    private $priceCurrency;

    /**
     * RoundFinalPrice constructor.
     * @param PriceCurrency $priceCurrency
     */
    public function __construct(
        PriceCurrency $priceCurrency
    ) {
        $this->priceCurrency = $priceCurrency;
    }

    /**
     * @see Product\Type\Price::getFinalPrice()
     * @param Observer $observer
     * @return void
     */
    public function execute(Observer $observer)
    {
        if ($this->priceCurrency->isEnabled()) {
            /**
             * @var Product $product
             */
            $product = $observer->getData('product');
            $product->setData('final_price', $this->priceCurrency->round($product->getData('final_price')));
        }
    }
}
