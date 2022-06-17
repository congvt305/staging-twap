<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace CJ\NinjaVanShipping\Plugin\Magento\Sales\Model\Service;


use CJ\NinjaVanShipping\Helper\Data as NinjaVanHelper;
use CJ\NinjaVanShipping\Logger\Logger as NinjaVanShippingLogger;
use CJ\NinjaVanShipping\Model\Request\CancelShipment as NinjaVanCancelShipment;
use Magento\Sales\Model\Order\Shipment\Track;
use Magento\Sales\Model\ResourceModel\Order\Shipment\Track\Collection;

class CreditmemoService
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
     * @param NinjaVanHelper $ninjavanHelper
     * @param NinjaVanShippingLogger $logger
     * @param NinjaVanCancelShipment $ninjavanCancelShipment
     */
    public function __construct(
        NinjaVanHelper         $ninjavanHelper,
        NinjaVanShippingLogger $logger,
        NinjaVanCancelShipment $ninjavanCancelShipment)
    {
        $this->ninjavanHelper = $ninjavanHelper;
        $this->logger = $logger;
        $this->ninjavanCancelShipment = $ninjavanCancelShipment;
    }

    public function beforeRefund(
        \Magento\Sales\Model\Service\CreditmemoService $subject,
        \Magento\Sales\Api\Data\CreditmemoInterface    $creditmemo,
                                                       $offlineRequested = false
    ){
        $order = $creditmemo->getOrder();
        if ((bool)$this->ninjavanHelper->isNinjaVanEnabled() && $order->getShippingMethod() == 'ninjavan_tablerate'
            && !$order->getData('ninjavan_shipment_cancel') && $order->getStatus() == 'processing_with_shipment'
        ) {
            try {
                $trackingNumber = $this->getTrackingNumber($order);
                $response = $this->ninjavanCancelShipment->requestCancelShipment($trackingNumber, $order);
                $message = 'The NinjaVan Shipment successfully cancelled';
                if (isset($response['trackingId']) && $response['trackingId'] == $trackingNumber) {
                    $message .= ' - Tracking Id: ' . $response['trackingId'];
                    $order->setData('ninjavan_shipment_cancel', 1);
                    if (isset($response['status'])) {
                        $message .= ' - Status: ' . $response['status'];
                    }
                    if (isset($response['updatedAt'])) {
                        $message .= ' - Updated at: ' . $response['updatedAt'];
                    }
                    $this->logger->info($message);
                    $order->addCommentToStatusHistory($message);
                    $order->save();
                }else{
                    throw new \Magento\Framework\Exception\LocalizedException(
                        __('An error occurred when canceling the order on NinjaVan.')
                    );
                }
            }catch (\Exception $exception){
                throw new \Magento\Framework\Exception\LocalizedException(
                    __('We cannot register a credit memo: %1', $exception->getMessage())
                );
            }
        }
    }

    private function getTrackingNumber(\Magento\Sales\Model\Order $order)
    {
        $trackingNumber = '';
        /** @var Collection $trackingCollection */
        $trackingCollection = $order->getTracksCollection();
        if ($trackingCollection->getSize()) {
            // innis only has 1 shipment per 1 order
            /** @var Track $tracking */
            $tracking = $trackingCollection->getLastItem();
            $trackingNumber = $tracking->getTrackNumber();
        }
        return $trackingNumber;
    }
}
