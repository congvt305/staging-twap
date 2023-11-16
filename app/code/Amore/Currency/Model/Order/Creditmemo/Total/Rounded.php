<?php
declare(strict_types=1);

namespace Amore\Currency\Model\Order\Creditmemo\Total;

use Amore\Currency\Model\PriceCurrency;
use Magento\Sales\Model\Order\Creditmemo;
use Magento\Sales\Model\Order\Creditmemo\Total\AbstractTotal;

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
     * Rounded Subtotal, Grand Total, Discount, Shipping after apply sales rule
     * @see \Magento\Sales\Model\Order\Creditmemo\Total\Discount::collect
     * @param Creditmemo $creditmemo
     * @return $this
     */
    public function collect(Creditmemo $creditmemo)
    {
        if ($this->priceCurrency->isEnabled()) {
            $oldDiscountAmount = $creditmemo->getDiscountAmount();
            $oldBaseDiscountAmount = $creditmemo->getBaseDiscountAmount();
            $creditmemo->setDiscountAmount($this->priceCurrency->round($oldDiscountAmount));
            $creditmemo->setBaseDiscountAmount($this->priceCurrency->round($oldBaseDiscountAmount));

            $creditmemo->setGrandTotal($creditmemo->getGrandTotal() -
                ($oldDiscountAmount - $creditmemo->getDiscountAmount()));
            $creditmemo->setBaseGrandTotal($creditmemo->getBaseGrandTotal() -
                ($oldBaseDiscountAmount - $creditmemo->getBaseDiscountAmount()));
        }
        return $this;
    }
}
