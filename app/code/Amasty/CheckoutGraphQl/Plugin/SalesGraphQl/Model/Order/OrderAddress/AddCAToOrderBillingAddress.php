<?php
declare(strict_types=1);

namespace Amasty\CheckoutGraphQl\Plugin\SalesGraphQl\Model\Order\OrderAddress;

use Amasty\CheckoutGraphQl\Model\Utils\Address\CAToOrderAddressSetter;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\SalesGraphQl\Model\Order\OrderAddress;

class AddCAToOrderBillingAddress
{
    /**
     * @var CAToOrderAddressSetter
     */
    private $caToOrderAddressSetter;

    public function __construct(
        CAToOrderAddressSetter $caToOrderAddressSetter
    ) {
        $this->caToOrderAddressSetter = $caToOrderAddressSetter;
    }

    /**
     * @param OrderAddress $subject
     * @param $result
     * @param OrderInterface $order
     * @return array|null
     */
    public function afterGetOrderBillingAddress(
        OrderAddress $subject,
        $result,
        OrderInterface $order
    ): ?array {
        if ($result) {
            $result = $this->caToOrderAddressSetter->execute(
                $result,
                (int)$order->getEntityId(),
                CAToOrderAddressSetter::BILLING_TYPE
            );
        }

        return $result;
    }
}
