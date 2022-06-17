<?php

declare(strict_types=1);

namespace CJ\Payoo\Cron;

use Magento\Sales\Model\ResourceModel\Order\CollectionFactory;
use Magento\Sales\Model\Order;
use Magento\Store\Api\StoreRepositoryInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Psr\Log\LoggerInterface;

class CancelOrdersCron
{
    //24 hours
    const EXPIRED_TIME = 24 * 60 * 60;
    const VN_LNG_STORE_CODE = 'vn_laneige';
    /**
     * @var CollectionFactory
     */
    protected CollectionFactory $orderCollectionFactory;

    /**
     * @var StoreRepositoryInterface
     */
    private StoreRepositoryInterface $storeRepository;

    /**
     * @var OrderRepositoryInterface
     */
    private OrderRepositoryInterface $orderRepository;

    /**
     * @var LoggerInterface
     */
    private LoggerInterface $logger;

    /**
     * @param OrderRepositoryInterface $orderRepository
     * @param StoreRepositoryInterface $storeRepository
     * @param CollectionFactory $collectionFactory
     * @param LoggerInterface $logger
     */
    public function __construct(
        OrderRepositoryInterface $orderRepository,
        StoreRepositoryInterface $storeRepository,
        CollectionFactory $collectionFactory,
        LoggerInterface $logger
    ) {
        $this->orderRepository = $orderRepository;
        $this->storeRepository= $storeRepository;
        $this->orderCollectionFactory = $collectionFactory;
        $this->logger = $logger;
    }

    /**
     * @return void
     */
    public function execute()
    {
        $storeId = $this->getVNStoreId();
        if ($storeId) {
            /** @var $orders \Magento\Sales\Model\ResourceModel\Order\Collection */
            $orders = $this->orderCollectionFactory->create();
            $orders->addFieldToFilter('store_id', $storeId);
            $orders->addFieldToFilter('status', Order::STATE_PAYMENT_REVIEW);
            $orders->getSelect()->where(
                new \Zend_Db_Expr('TIME_TO_SEC(TIMEDIFF(CURRENT_TIMESTAMP, `created_at`)) >= ' . self::EXPIRED_TIME)
            );

            foreach ($orders->getAllIds() as $entityId) {
                try {
                    $order = $this->orderRepository->get($entityId);
                    $order->setState('new');
                    $order->setStatus('pending');
                    $order->cancel();
                    $this->orderRepository->save($order);
                } catch (\Exception $exception) {
                    $this->logger->error($exception->getMessage());
                }
            }
        }
    }

    /**
     * @return int|null
     */
    public function getVNStoreId()
    {
        try {
            $store = $this->storeRepository->get(self::VN_LNG_STORE_CODE);
            return $store->getId();
        } catch (\Exception $exception) {
           $this->logger->error($exception->getMessage());
        }
        return null;
    }
}
