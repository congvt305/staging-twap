<?php

namespace Amore\PointsIntegration\Cron;

use Amore\PointsIntegration\Model\POSCancelledOrderSender;
use Amore\PointsIntegration\Model\PosReturnData;
use Amore\PointsIntegration\Model\PosReturnSender;
use Magento\Rma\Model\Rma;
use Magento\Sales\Model\Order;
use Magento\Store\Model\StoreManagerInterface;
use \Amore\PointsIntegration\Model\Source\Config as PointsIntegrationConfig;
use \Amore\PointsIntegration\Model\PosOrderSender;
use \Amore\PointsIntegration\Model\PosOrderData;

class PosOrderSynchronizer
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
        PosOrderSender          $posOrderSender,
        PosOrderData            $posOrderData,
        PosReturnData           $posReturnData,
        PosReturnSender         $posReturnSender,
        POSCancelledOrderSender $posCancelledOrderSender
    )
    {
        $this->storeManagerInterface = $storeManagerInterface;
        $this->config = $config;
        $this->posOrderSender = $posOrderSender;
        $this->posOrderData = $posOrderData;
        $this->posReturnData = $posReturnData;
        $this->posReturnSender = $posReturnSender;
        $this->posCancelledOrderSender = $posCancelledOrderSender;
    }

    /**
     * Synchronize paid, cancel and return order with POS
     */
    public function execute()
    {
        $stores = $this->storeManagerInterface->getStores();

        foreach ($stores as $store) {
            $storeId = $store->getId();
            $cronActive = $this->config->getCronActive($store->getWebsiteId());

            if ($cronActive) {
                $this->paidOrderToPOS($storeId);
                $this->cancelledOrderToPOS($storeId);
                $this->completedReturnToPOS($storeId);
            }
        }
    }

    /**
     * @param $storeId
     */
    private function paidOrderToPOS($storeId)
    {
        $orders = $this->posOrderData->getPaidOrdersToPOS($storeId);
        foreach ($orders as $order) {
            /**
             * @var Order $order
             */
            $this->posOrderSender->send($order);
        }
    }

    /**
     * @param $storeId
     */
    private function cancelledOrderToPOS($storeId)
    {
        $orders = $this->posOrderData->getCancelledOrdersToPOS($storeId);
        foreach ($orders as $order) {
            /**
             * @var Order $order
             */
            $this->posCancelledOrderSender->send($order);
        }
    }

    /**
     * @param $storeId
     */
    private function completedReturnToPOS($storeId)
    {
        $returns = $this->posReturnData->getCompletedReturnToPOS($storeId);
        foreach ($returns as $return) {
            /**
             * @var Rma $return
             */
            $this->posReturnSender->send($return);
        }
    }
}
