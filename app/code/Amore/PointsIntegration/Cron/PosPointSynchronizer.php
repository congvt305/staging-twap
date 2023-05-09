<?php
declare(strict_types=1);

namespace Amore\PointsIntegration\Cron;

use Amore\PointsIntegration\Model\POSCancelledOrderSender;
use Amore\PointsIntegration\Model\PosOrderData;
use Amore\PointsIntegration\Model\PosOrderSender;
use Amore\PointsIntegration\Model\PosReturnData;
use Amore\PointsIntegration\Model\PosReturnSender;
use Amore\PointsIntegration\Model\Source\Config as PointsIntegrationConfig;
use Magento\Rma\Model\Rma;
use Magento\Sales\Model\Order;
use Magento\Store\Model\StoreManagerInterface;

class PosPointSynchronizer
{
    /**
     * @var StoreManagerInterface
     */
    private $storeManagerInterface;

    /**
     * @var PointsIntegrationConfig
     */
    private $config;

    /**
     * @var PosOrderSender
     */
    private $posOrderSender;

    /**
     * @var PosOrderData
     */
    private $posOrderData;

    /**
     * @var PosReturnData
     */
    private $posReturnData;

    /**
     * @var PosReturnSender
     */
    private $posReturnSender;

    /**
     * @var POSCancelledOrderSender
     */
    private $posCancelledOrderSender;

    /**
     * @var \Amore\PointsIntegration\Model\PointUpdate
     */
    private $pointUpdate;

    /**
     * @param StoreManagerInterface $storeManagerInterface
     * @param PointsIntegrationConfig $config
     * @param PosOrderSender $posOrderSender
     * @param PosOrderData $posOrderData
     * @param PosReturnData $posReturnData
     * @param PosReturnSender $posReturnSender
     * @param POSCancelledOrderSender $posCancelledOrderSender
     */
    public function __construct(
        StoreManagerInterface   $storeManagerInterface,
        PointsIntegrationConfig $config,
        PosOrderData            $posOrderData,
        \Amore\PointsIntegration\Model\PointUpdate $pointUpdate
    ) {
        $this->storeManagerInterface = $storeManagerInterface;
        $this->config = $config;
        $this->posOrderData = $posOrderData;
        $this->pointUpdate = $pointUpdate;
    }

    /**
     * Synchronize paid, cancel and return order with POS
     */
    public function execute()
    {
        $stores = $this->storeManagerInterface->getStores();

        foreach ($stores as $store) {
            $storeId = $store->getId();
            $cronActive = $this->config->getResendPointCronActive($store->getWebsiteId());

            if ($cronActive) {
                try {
                    $this->resendUsePointToPOS($storeId);
                    $this->resendReturnPointToPOS($storeId);
                } catch (\Exception $e) {
                    continue;
                }
            }
        }
    }


    /**
     * Resend use point for order has invoice
     *
     * @param int $storeId
     * @return void
     */
    private function resendUsePointToPOS($storeId)
    {
        $orders = $this->posOrderData->getOrdersNeedToResendUsePointToPOS($storeId);
        foreach ($orders as $order) {
            /**
             * @var Order $order
             */
            $pointAmount = $order->getData('am_spent_reward_points') ?: 0;
            $this->pointUpdate->pointUpdate($order, $pointAmount, $this->pointUpdate::POINT_REDEEM);
        }
    }

    /**
     * Resend return point for order canceled
     *
     * @param $storeId
     * @return void
     */
    private function resendReturnPointToPOS($storeId)
    {
        $ordersData = $this->posOrderData->getOrdersNeedToResendReturnPointToPOS($storeId);
        foreach ($ordersData as $order) {
            /**
             * @var Order $order
             */
            $pointAmount = $order->getData('am_spent_reward_points') ?: 0;
            $this->pointUpdate->pointUpdate($order, $pointAmount);
        }
    }
}
