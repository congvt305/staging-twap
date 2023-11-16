<?php

namespace Amore\SalesRule\Plugin;

use Magento\Quote\Model\Quote;
use Magento\Quote\Model\Quote\Address\Total;
use Magento\SalesRule\Model\Quote\Discount as DiscountCollector;
use Psr\Log\LoggerInterface;

class ShippingDiscount
{

    /**
     * @var LoggerInterface
     */
    private $_logger;

    /**
     * @param LoggerInterface $logger
     */
    public function __construct(
        LoggerInterface $logger
    )
    {
        $this->_logger = $logger;
    }

    /**
     * @param $subject
     * @param callable $proceed
     * @param Quote $quote
     * @param Total $total
     * @return array
     */
    public function aroundFetch(
        $subject,
        callable $proceed,
        Quote $quote,
        Total $total
    )
    {
        try {
            $result = [];
            $amount = $total->getDiscountAmount();

            if ($amount != 0) {
                $description = (string)$total->getDiscountDescription() ?: '';
                $result = [
                    'code' => DiscountCollector::COLLECTOR_TYPE_CODE,
                    'title' => strlen($description) ? __('Discount') : __('Discount'),
                    'value' => $amount
                ];
            }
            return $result;

        } catch (\Exception $e) {
            $this->_logger->critical("Sales rule error: " . $e->getMessage());
            return $proceed($quote, $total);
        }
    }


}
