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

    /**
     * @param $incrementId
     * @return false|mixed
     */
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

    /**
     * Convert price values to string if is new middleware
     * @param SapOrderConfirmData $subject
     * @param $result
     * @param $incrementId
     * @return mixed
     */
    public function afterGetOrderData(SapOrderConfirmData $subject, $result, $incrementId)
    {
        if ($this->isNewMiddleware($incrementId)) {
            array_walk_recursive($result, [$this, 'convertNumberToString']);
        }
        return $result;
    }

    /**
     * Convert price values to string if is new middleware
     * @param SapOrderConfirmData $subject
     * @param $result
     * @param $incrementId
     * @return mixed
     */
    public function afterGetOrderItem(SapOrderConfirmData $subject, $result, $incrementId)
    {
        if ($this->isNewMiddleware($incrementId)) {
            array_walk_recursive($result, [$this, 'convertNumberToString']);
        }
        return $result;
    }

    /**
     * @param $value
     * @param $key
     * @return void
     */
    public function convertNumberToString(&$value, $key)
    {
        if (is_float($value) || is_int($value)) {
            $value = "$value";
        }
    }
}
