<?php
/**
 * Created by Eguana.
 * User: Brian
 * Date: 2020-06-12
 * Time: 오후 1:25
 */

namespace Amore\Sap\Model\SapOrder;

use Amore\Sap\Api\SapOrderManagementInterface;
use Amore\Sap\Model\SapOrder\SapOrderConfirm;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\LocalizedException;

class SapOrderManagement implements SapOrderManagementInterface
{
    const SAP_ORDER_CREATION = 'sap_order_creation';

    const SAP_ORDER_CREATION_ERROR = 'sap_order_creation_error';

    const SAP_ORDER_DELIVERY_CREATION = 'sap_order_delivery_creation';

    const SAP_ORDER_DELIVERY_START_OR_PRODUCT_RETURNED = 'sap_order_delivery_start_or_product_returned';

    const SAP_ORDER_CANCEL = 'sap_order_cancel';

    /**
     * @var \Magento\Sales\Api\Data\OrderInterfaceFactory
     */
    private $orderInterfaceFactory;
    /**
     * @var \Magento\Sales\Api\OrderRepositoryInterface
     */
    private $orderRepository;

    /**
     * SapOrderManagement constructor.
     * @param \Magento\Sales\Api\Data\OrderInterfaceFactory $orderInterfaceFactory
     * @param \Magento\Sales\Api\OrderRepositoryInterface $orderRepository
     */
    public function __construct(
        \Magento\Sales\Api\Data\OrderInterfaceFactory $orderInterfaceFactory,
        \Magento\Sales\Api\OrderRepositoryInterface $orderRepository
    ) {
        $this->orderInterfaceFactory = $orderInterfaceFactory;
        $this->orderRepository = $orderRepository;
    }

    public function orderStatus($source, $mallId, $orderStatusData)
    {
        $result = [];
        foreach ($orderStatusData as $orderStatusDatum) {
            $incrementId = $orderStatusDatum['ordno'];
            /** @var \Magento\Sales\Model\Order $orderModel */
            $orderModel = $this->orderInterfaceFactory->create();
            $order = $orderModel->loadByIncrementId($incrementId);


        }
    }

    public function orderConfirm($incrementId)
    {
        if (!$incrementId) {
            throw new InputException(__('An Increment Id is needed. Please set the Increment Id and try again.'));
        }


    }

    public function orderStatusList($orderStatus)
    {
        switch ($orderStatus) {
            case 1:
                $status = self::SAP_ORDER_CREATION;
                break;
            case 2:
                $status = self::SAP_ORDER_CREATION_ERROR;
                break;
            case 3:
                $status = self::SAP_ORDER_DELIVERY_CREATION;
                break;
            case 4:
                $status = self::SAP_ORDER_DELIVERY_START_OR_PRODUCT_RETURNED;
                break;
            case 9:
                $status = self::SAP_ORDER_CANCEL;
                break;
            default:
                $status = self::SAP_ORDER_DELIVERY_CREATION;
        }
        return $status;
    }
}
