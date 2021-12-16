<?php
namespace CJ\Middleware\Plugin\Model\SapOrder;

use CJ\Middleware\Helper\Data as MiddlewareHelper;
use Amore\Sap\Model\SapOrder\SapOrderConfirmData;
use Magento\Sales\Api\Data\OrderInterfaceFactory;

class SapOrderConfirmDataPlugin
{
    /**
     * @var OrderInterfaceFactory
     */
    protected $orderFatory;

    /**
     * @var MiddlewareHelper
     */
    protected $middlewareHelper;

    /**
     * @param MiddlewareHelper $middlewareHelper
     * @param OrderInterfaceFactory $orderFactory
     */
    public function __construct(
        MiddlewareHelper $middlewareHelper,
        OrderInterfaceFactory $orderFactory
    ) {
        $this->orderFatory = $orderFactory;
        $this->middlewareHelper = $middlewareHelper;
    }

    public function isNewMiddleware($incrementId)
    {
        $order = $this->orderFatory->create()->loadByIncrementId($incrementId);
        if (!$order->getEntityId() ||
            !in_array($order->getStatus(), ['processing', 'sap_fail', 'processing_with_shipment'])
        ) {
            return false;
        }
        return $this->middlewareHelper->isNewMiddlewareEnabled('store', $order->getStoreId());
    }

    public function afterGetOrderData(SapOrderConfirmData $subject, $result, $incrementId)
    {
        if ($this->isNewMiddleware($incrementId)) {
            array_walk_recursive($result, [$this, 'convertNumberToString']);
        }
        return $result;
    }

    public function afterGetOrderItem(SapOrderConfirmData $subject, $result, $incrementId)
    {
        if ($this->isNewMiddleware($incrementId)) {
            array_walk_recursive($result, [$this, 'convertNumberToString']);
        }
        return $result;
    }

    public function convertNumberToString(&$value, $key)
    {
        if (is_float($value)) {
            $value = "$value";
        }
    }
}
