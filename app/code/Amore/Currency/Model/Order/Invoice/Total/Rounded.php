<?php
declare(strict_types=1);

namespace Amore\Currency\Model\Order\Invoice\Total;

use Amore\Currency\Model\PriceCurrency;
use Magento\Sales\Model\Order\Invoice;
use Magento\Sales\Model\Order\Invoice\Total\AbstractTotal;

class Rounded extends AbstractTotal
{
    /**
     * @var  PriceCurrency
     */
    private $priceCurrency;

    /**
     * Rounded constructor.
     * @param PriceCurrency $priceCurrency
     * @param array $data
     */
    public function __construct(
        PriceCurrency $priceCurrency,
        array $data = []
    ) {
        parent::__construct($data);
        $this->priceCurrency = $priceCurrency;
    }

    /**
     * Rounded Totals
     * @see \Magento\Sales\Model\Order\Invoice\Total\Discount::collect
     * @param Invoice $invoice
     * @return Rounded
     */
    public function collect(Invoice $invoice)
    {
        if ($this->priceCurrency->isEnabled()) {
            $oldDiscountAmount = $invoice->getDiscountAmount();
            $oldBaseDiscountAmount = $invoice->getBaseDiscountAmount();
            $invoice->setDiscountAmount($this->priceCurrency->round($oldDiscountAmount));
            $invoice->setBaseDiscountAmount($this->priceCurrency->round($oldBaseDiscountAmount));

            $invoice->setGrandTotal($invoice->getGrandTotal() -
                ($oldDiscountAmount - $invoice->getDiscountAmount()));
            $invoice->setBaseGrandTotal($invoice->getBaseGrandTotal() -
                ($oldBaseDiscountAmount - $invoice->getBaseDiscountAmount()));
        }
        return $this;
    }
}
