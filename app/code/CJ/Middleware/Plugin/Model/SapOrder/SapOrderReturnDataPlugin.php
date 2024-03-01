<?php
namespace CJ\Middleware\Plugin\Model\SapOrder;

use CJ\Middleware\Helper\Data as MiddlewareHelper;
use Amore\Sap\Model\SapOrder\SapOrderReturnData;
use Magento\Sales\Api\Data\OrderInterfaceFactory;

class SapOrderReturnDataPlugin
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
     * Convert price values to string if is new middleware
     * @param SapOrderReturnData $subject
     * @param $result
     * @param \Magento\Rma\Model\Rma $rma
     * @return mixed
     */
    public function afterGetRmaData(SapOrderReturnData $subject, $result, \Magento\Rma\Model\Rma $rma)
    {
        $order = $rma->getOrder();
        if ($this->middlewareHelper->isNewMiddlewareEnabled('store', $order->getStoreId())) {
            array_walk_recursive($result, [$this, 'convertNumberToString']);
        }
        return $result;
    }

    /**
     * Convert price values to string if is new middleware
     * @param SapOrderReturnData $subject
     * @param $result
     * @param \Magento\Rma\Model\Rma $rma
     * @return mixed
     */
    public function afterGetRmaItemData(SapOrderReturnData $subject, $result, \Magento\Rma\Model\Rma $rma)
    {
        $order = $rma->getOrder();
        if ($this->middlewareHelper->isNewMiddlewareEnabled('store', $order->getStoreId())) {
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
