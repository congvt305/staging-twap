<?php
/**
 * Created by Eguana.
 * User: Brian
 * Date: 2020-06-02
 * Time: 오전 11:16
 */

namespace Amore\Sap\Model\SapOrder;

use Amore\Sap\Api\Data\SapOrderStatusInterface;
use Magento\Framework\Model\AbstractExtensibleModel;

class SapOrderStatus extends AbstractExtensibleModel implements SapOrderStatusInterface
{
    /**
     * @var \Magento\Sales\Api\OrderRepositoryInterface
     */
    private $orderRepository;

    /**
     * SapOrderStatus constructor.
     * @param \Magento\Sales\Api\OrderRepositoryInterface $orderRepository
     */
    public function __construct(
      \Magento\Sales\Api\OrderRepositoryInterface $orderRepository
    ) {
        $this->orderRepository = $orderRepository;
    }

    public function getOrderId()
    {
        return $this->getData(self::ORDER_ID);
    }

    public function setOrderId($orderId)
    {
        return $this->setData(self::ORDER_ID, $orderId);
    }

    public function getIncrementId()
    {
        return $this->getData(self::INCREMENT_ID);
    }

    public function setIncrementId($incrementId)
    {
        return $this->setData(self::INCREMENT_ID, $incrementId);
    }

    public function getStoreId()
    {
        return $this->getData(self::STORE_ID);
    }

    public function setStoreId($storeId)
    {
        return $this->setData(self::STORE_ID, $storeId);
    }

    public function getStatus()
    {
        return $this->getData(self::STATUS);
    }

    public function setStatus($status)
    {
        return $this->setData(self::STATUS, $status);
    }

    public function getOrder($orderId)
    {
        return $this->orderRepository->get($orderId);
    }
}
