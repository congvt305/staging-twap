<?php
declare(strict_types=1);

namespace Amore\Currency\Model\Quote\Address\Total;

use Amore\Currency\Model\PriceCurrency;
use Magento\Quote\Api\Data\ShippingAssignmentInterface as ShippingAssignment;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\Quote\Address\Total;
use Magento\Quote\Model\Quote\Address\Total\AbstractTotal;
use Magento\SalesRule\Model\Quote\Discount;

class Rounded extends AbstractTotal
{
    /**
     * @var  PriceCurrency
     */
    private $priceCurrency;

    /**
     * Rounded constructor.
     * @param  PriceCurrency $priceCurrency
     */
    public function __construct(
        PriceCurrency $priceCurrency
    ) {
        $this->priceCurrency = $priceCurrency;
    }

    /**
     * Rounded Subtotal, Grand Total, Discount, Shipping after apply sales rule
     * @see \Magento\SalesRule\Model\Quote\Discount::collect
     * @see \Magento\SalesRule\Model\Quote\Address\Total\ShippingDiscount::collect
     * @param Quote $quote
     * @param ShippingAssignment $shippingAssignment
     * @param Total $total
     * @return $this
     */
    public function collect(Quote $quote, ShippingAssignment $shippingAssignment, Total $total): Rounded
    {
        if ($this->priceCurrency->isEnabled()) {
            $address = $shippingAssignment->getShipping()->getAddress();

            $total->setSubtotal($this->priceCurrency->round($total->getSubtotal()));
            $total->setBaseSubtotal($this->priceCurrency->round($total->getBaseSubtotal()));
            $total->setDiscountAmount($this->priceCurrency->round($total->getDiscountAmount()));
            $total->setBaseDiscountAmount($this->priceCurrency->round($total->getBaseDiscountAmount()));
            $total->setTotalAmount(
                Discount::COLLECTOR_TYPE_CODE,
                $this->priceCurrency->round(
                    $total->getTotalAmount(Discount::COLLECTOR_TYPE_CODE)
                )
            );
            $total->setBaseTotalAmount(
                Discount::COLLECTOR_TYPE_CODE,
                $this->priceCurrency->round(
                    $total->getBaseTotalAmount(Discount::COLLECTOR_TYPE_CODE)
                )
            );
            $total->setShippingDiscountAmount($address->getShippingDiscountAmount());
            $total->setBaseShippingDiscountAmount($address->getBaseShippingDiscountAmount());

            $total->setSubtotalWithDiscount($total->getSubtotal() + $total->getDiscountAmount());
            $total->setBaseSubtotalWithDiscount($total->getBaseSubtotal() + $total->getBaseDiscountAmount());

            $address->setDiscountAmount($this->priceCurrency->round($address->getDiscountAmount()));
            $address->setBaseDiscountAmount($this->priceCurrency->round($address->getBaseDiscountAmount()));
        }
        return $this;
    }
}
