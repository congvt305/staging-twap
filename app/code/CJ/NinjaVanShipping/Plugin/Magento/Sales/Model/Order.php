<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace CJ\NinjaVanShipping\Plugin\Magento\Sales\Model;

use Amore\Sap\Model\SapOrder\SapOrderConfirmData;
use CJ\NinjaVanShipping\Logger\Logger as NinjaVanShippingLogger;
use CJ\NinjaVanShipping\Model\Request\CancelShipment as NinjaVanCancelShipment;
use Magento\Sales\Model\Order\Shipment\Track;
use Magento\Sales\Model\OrderFactory;
use Magento\Sales\Model\ResourceModel\Order\Shipment\Track\Collection;
use CJ\NinjaVanShipping\Helper\Data as NinjaVanHelper;
use Magento\Setup\Exception;
use Magento\Sales\Model\order\CreditmemoFactory;
use Magento\Sales\Api\CreditmemoManagementInterface;


class Order
{
    /**
     * @var NinjaVanHelper
     */
    protected $ninjavanHelper;
    /**
     * @var NinjaVanShippingLogger
     */
    protected $logger;
    /**
     * @var NinjaVanCancelShipment
     */
    protected $ninjavanCancelShipment;
    /**
     * @var OrderFactory
     */
    protected $orderFactory;
    /**
     * @var \Magento\Framework\Message\ManagerInterface
     */
    protected $messageManager;
    /**
     * @var CreditmemoFactory
     */
    protected $creditmemoFactory;
    /**
     * @var CreditmemoManagementInterface
     */
    protected $_creditmemoService;

    /**
     * @param NinjaVanHelper $ninjavanHelper
     * @param NinjaVanShippingLogger $logger
     * @param NinjaVanCancelShipment $ninjavanCancelShipment
     * @param OrderFactory $orderFactory
     * @param \Magento\Framework\Message\ManagerInterface $messageManager
     * @param CreditmemoFactory $creditmemoFactory
     * @param CreditmemoManagementInterface $_creditmemoService
     */
    public function __construct(
        NinjaVanHelper         $ninjavanHelper,
        NinjaVanShippingLogger $logger,
        NinjaVanCancelShipment $ninjavanCancelShipment,
        OrderFactory           $orderFactory,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        CreditmemoFactory $creditmemoFactory,
        CreditmemoManagementInterface $_creditmemoService
    )
    {
        $this->ninjavanHelper = $ninjavanHelper;
        $this->logger = $logger;
        $this->ninjavanCancelShipment = $ninjavanCancelShipment;
        $this->orderFactory = $orderFactory;
        $this->messageManager = $messageManager;
        $this->creditmemoFactory = $creditmemoFactory;
        $this->_creditmemoService = $_creditmemoService;
    }
    public function beforeCancel(\Magento\Sales\Model\Order $subject)
    {
        $order = $subject;
        if (in_array($order->getData('sap_order_send_check'), [SapOrderConfirmData::ORDER_RESENT_TO_SAP_SUCCESS, SapOrderConfirmData::ORDER_SENT_TO_SAP_SUCCESS])){
            $this->messageManager->addErrorMessage(__('Plz cancel sap order first and then credit memo for refund and cancel ninjavan shipment'));
            throw new Exception(__('This order cannot cancel now.'));
        }

        if ((bool)$this->ninjavanHelper->isNinjaVanEnabled($order->getStoreId()) && $order->getShippingMethod() == 'ninjavan_tablerate') {
            try {
                /**
                 * create credit memo and refun order
                 */
                if (!$order->hasCreditmemos()){
                    $this->creatCreditMemo($order);
                }
            } catch (Exception $exception) {
                $this->messageManager->addErrorMessage(__($exception->getMessage()));
                throw new \Exception(__('Something went wrong while canceling'));
            }
        }
    }

    public function creatCreditMemo(\Magento\Sales\Model\Order $order)
    {
        $offlineRequest = true;
        if ($order->getPayment()->getLastTransId()){
            $offlineRequest = false;
        }
        $order->setBaseTotalPaid($order->getBaseGrandTotal());
        $order->setBaseTotalRefunded(0);
        $invoices = $order->getInvoiceCollection();
        if ($invoices->getSize()){
            foreach ($invoices as $invoice){
                /**
                 * @var \Magento\Sales\Model\Order\Invoice $invoice
                 */
                if (!$invoice->getId()){
                    continue;
                }
                try {
                    $creditMemo = $this->creditmemoFactory->createByOrder($order);
                    $this->logger->info($creditMemo->getOrder()->getBaseTotalRefunded() + $creditMemo->getBaseGrandTotal());
                    $this->logger->info($creditMemo->getOrder()->getBaseTotalPaid());
                    $creditMemo->setBaseGrandTotal($order->getBaseGrandTotal());
                    $creditMemo->setBaseSubtotal($order->getBaseSubtotal());
                    $creditMemo->setSubtotal($order->getSubtotal());
                    $creditMemo->setGrandTotal($order->getGrandTotal());
                    $creditMemo->setShippingAmount($order->getShippingAmount());
                    $creditMemo->setAdjustmentPositive($order->getAdjustmentPositive());
                    $creditMemo->setAdjustmentNegative($order->getAdjustmentNegative());
                    $creditMemo->setBaseAdjustmentNegative($order->getBaseAdjustmentNegative());
                    $creditMemo->setBaseAdjustmentPositive($order->getBaseAdjustmentPositive());
                    $creditMemo->setInvoice($invoice);
                    $this->_creditmemoService->refund($creditMemo, $offlineRequest);
                }catch (\Exception $exception){
                    $this->logger->info($exception->getMessage());
                }
            }
        }

    }
}
