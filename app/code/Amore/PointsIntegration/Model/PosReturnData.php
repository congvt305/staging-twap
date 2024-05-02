<?php
/**
 * Created by Eguana.
 * User: Brian
 * Date: 2020-12-17
 * Time: 오후 5:20
 */
declare(strict_types=1);

namespace Amore\PointsIntegration\Model;

use Amore\PointsIntegration\Model\Source\Config;
use Amore\StaffReferral\Helper\Config as ReferralConfig;
use CJ\Middleware\Helper\Data;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Framework\DataObject;
use Magento\Rma\Api\Data\CommentInterfaceFactory;
use Magento\Rma\Api\Data\RmaInterface;
use Magento\Rma\Api\RmaRepositoryInterface;
use Amore\PointsIntegration\Logger\Logger;

class PosReturnData extends AbstractPosOrder
{
    /**
     * @var Config
     */
    private $config;

    /**
     * @var RmaRepositoryInterface
     */

    private $rmaRepository;

    /**
     * @var \Magento\Rma\Model\ResourceModel\Rma\CollectionFactory
     */
    private $rmaCollectionFactory;

    /**
     * @var Logger
     */
    private $pointsIntegrationLogger;

    /**
     * @var CommentInterfaceFactory
     */
    private $commentInterfaceFactory;

    /**
     * @var Data
     */
    private $middlewareHelper;


    /**
     * @var \Amasty\Rewards\Model\Config
     */
    private $amConfig;

    /**
     * @var \CJ\Rewards\Model\Data
     */
    private $rewardData;

    /**
     * @var \CJ\Middleware\Model\Data
     */
    private $orderData;

    /**
     * @param Config $config
     * @param RmaRepositoryInterface $rmaRepository
     * @param CustomerRepositoryInterface $customerRepository
     * @param \Magento\Rma\Model\ResourceModel\Rma\CollectionFactory $rmaCollectionFactory
     * @param Logger $pointsIntegrationLogger
     * @param CommentInterfaceFactory $commentInterfaceFactory
     * @param Data $middlewareHelper
     * @param \Amasty\Rewards\Model\Config $amConfig
     * @param ReferralConfig $referralConfig
     * @param \CJ\Rewards\Model\Data $rewardData
     * @param \CJ\Middleware\Model\Data $orderData
     */
    public function __construct(
        Config $config,
        RmaRepositoryInterface $rmaRepository,
        CustomerRepositoryInterface $customerRepository,
        \Magento\Rma\Model\ResourceModel\Rma\CollectionFactory $rmaCollectionFactory,
        Logger $pointsIntegrationLogger,
        CommentInterfaceFactory $commentInterfaceFactory,
        Data $middlewareHelper,
        \Amasty\Rewards\Model\Config $amConfig,
        ReferralConfig $referralConfig,
        \CJ\Rewards\Model\Data $rewardData,
        \CJ\Middleware\Model\Data $orderData
    ) {
        $this->config = $config;
        $this->rmaRepository = $rmaRepository;
        $this->rmaCollectionFactory = $rmaCollectionFactory;
        $this->pointsIntegrationLogger = $pointsIntegrationLogger;
        $this->commentInterfaceFactory = $commentInterfaceFactory;
        $this->middlewareHelper = $middlewareHelper;
        $this->amConfig = $amConfig;
        $this->rewardData = $rewardData;
        $this->orderData = $orderData;
        parent::__construct($referralConfig, $customerRepository, $config, $orderData);
    }

    /**
     * Get rma data
     *
     * @param RmaInterface $rma
     */
    public function getRmaData($rma)
    {
        $this->resetData();
        $order = $rma->getOrder();
        $websiteId = $order->getStore()->getWebsiteId();
        $customer = $order->getCustomerId() ? $this->getCustomer($rma->getCustomerId()) : null;
        $posIntegrationNumber = $customer && $customer->getCustomAttribute('integration_number') ?
            $customer->getCustomAttribute('integration_number')->getValue() : null;

        $rmaItem = $this->getRmaItemData($rma);
        $invoice = $order->getInvoiceCollection()->getFirstItem();
        $couponCode = $order->getCouponCode();

        $redemptionFlag = 'N';
        $rewardPoints = 0;
        $storeId = $order->getStoreId();
        $isDecimalFormat = $this->middlewareHelper->getIsDecimalFormat('store', $storeId);
        if($this->amConfig->isEnabled($storeId)) {
            if ($order->getData('am_spent_reward_points')) {
                $rewardPoints = $this->orderData->roundingPrice($order->getData('am_spent_reward_points'), $isDecimalFormat);
            }

            $spendingRate = $this->amConfig->getPointsRate($storeId);
            if (!$spendingRate) {
                $spendingRate = 1;
            }
            if ($this->rewardData->isEnableShowListOptionRewardPoint($storeId)) {
                $listOptions = $this->rewardData->getListOptionRewardPoint($storeId);
                $discountFromPoints = $listOptions[$rewardPoints] ?? 0;
            } else {
                $discountFromPoints = $rewardPoints / $spendingRate;
            }
            if (($order->getGrandTotal() - $order->getShippingAmount()) == $discountFromPoints) {
                $redemptionFlag = 'Y';
            }
        }
        $baReferralCode = $this->getReferralBACode($order, $websiteId);
        $friendReferralCode = $this->getFriendReferralCode($order);
        $quantityRatioForPartial = $this->getQuantityRatioForPartial($rma);
        if ($rewardPoints && $quantityRatioForPartial < 1) {
            $rewardPoints = $rewardPoints * $quantityRatioForPartial;
        }

        $rmaData = [
            'salOrgCd' => $this->middlewareHelper->getSalesOrganizationCode('store', $websiteId),
            'salOffCd' => $this->middlewareHelper->getSalesOfficeCode('store', $websiteId),
            'saledate' => $this->orderData->dateFormatting($rma->getDateRequested(), 'Ymd'),
            'orderID' => 'R' . $order->getIncrementId(),
            'rcptNO' => 'I' . $invoice->getIncrementId(),
            'cstmIntgSeq' => $posIntegrationNumber,
            'orderType' => self::POS_ORDER_TYPE_RETURN,
            'promotionKey' => $couponCode,
            'orderInfo' => $rmaItem,
            'PointAccount' => (int)$rewardPoints,
            'redemptionFlag' => $redemptionFlag,
            'baReferralCode' => $baReferralCode,
            'ffReferralCode' => $friendReferralCode
        ];

        return $rmaData;
    }

    /**
     * Get rma data
     *
     * @param RmaInterface $rma
     */
    private function getRmaItemData($rma)
    {
        $rmaItems = $rma->getItems();
        $order = $rma->getOrder();
        $storeId = $order->getStoreId();
        $isDecimalFormat = $this->middlewareHelper->getIsDecimalFormat('store', $storeId);
        $quantityRatioForPartial = $this->getQuantityRatioForPartial($rma);
        foreach ($rmaItems as $rmaItem) {
            $orderItem = $order->getItemById($rmaItem->getOrderItemId());

            $quantityRatioPerItemPartial = $rmaItem->getQtyRequested() / $orderItem->getQtyOrdered();
            if ($orderItem->getProductType() != 'bundle') {
                $itemDcamt = $this->orderData->roundingPrice($orderItem->getData('sap_item_dcamt') * $quantityRatioPerItemPartial, $isDecimalFormat);
                $itemNsamt = $this->orderData->roundingPrice($orderItem->getData('sap_item_nsamt') * $quantityRatioPerItemPartial, $isDecimalFormat);
                $itemSlamt = $itemNsamt - $itemDcamt; // have to do this to correct price among Nsamt, Dcamt and Slamt
                $itemMiamt = $this->orderData->roundingPrice($orderItem->getData('sap_item_miamt') * $quantityRatioPerItemPartial, $isDecimalFormat);
                $itemNetwr = $itemSlamt - $itemMiamt; // have to do this to correct price among Nsamt, Dcamt, Slamt and Netwr

                $this->addOrderItemData($order, $orderItem, $rmaItem, $itemNsamt, $itemDcamt + $itemMiamt, $itemNetwr, $isDecimalFormat);
            }
        }
        $orderSubtotal = abs($this->orderData->roundingPrice($order->getSubtotalInclTax(), $isDecimalFormat));
        $orderGrandTotal = $order->getGrandTotal() == 0 ? $order->getGrandTotal() : $this->orderData->roundingPrice($order->getGrandTotal(), $isDecimalFormat);

        if ($this->middlewareHelper->getIsIncludeShippingAmountWhenSendRequest($storeId)) {
            $orderSubtotal += $order->getShippingAmount();
        } else {
            $orderGrandTotal -= $order->getShippingAmount();
        }
        if ($quantityRatioForPartial == 1) {
            $orderDiscountAmount = $orderSubtotal - $orderGrandTotal;
            $this->correctPricePOSOrderItemData($orderSubtotal, $orderDiscountAmount, $orderGrandTotal, $isDecimalFormat);
        }
        return $this->orderItemData;
    }

    /**
     * @param $storeId
     * @return DataObject[]
     */
    public function getCompletedReturnToPOS($storeId): array
    {
        $rmaCollection = $this->rmaCollectionFactory->create();
        $rmaCollection->addFieldToFilter('pos_rma_completed_send', true)
            ->addFieldToFilter('store_id', $storeId);

        return $rmaCollection->getItems();
    }

    /**
     * @param RmaInterface $rma
     */
    public function updatePosReturnOrderSendFlag(RmaInterface $rma)
    {
        try {
            $rma->setData('pos_rma_completed_sent', true);
            $rma->setData('pos_rma_completed_send', false);
            $comment = $this->commentInterfaceFactory->create();
            $comment->setRmaEntityId($rma->getEntityId());
            $comment->setComment(__('Send return info to POS successfully'));
            $comment->setIsAdmin(true);
            $rma->setComments([$comment]);
            $this->rmaRepository->save($rma);
        } catch (\Exception $exception) {
            $this->pointsIntegrationLogger->error($exception->getMessage());
        }
    }

    /**
     * Add order item data
     *
     * @param $order
     * @param $newOrderItem
     * @param $itemNsamt
     * @param $itemDcamt
     * @param $itemNetwr
     * @param $isDecimalFormat
     * @param $bundleChild
     * @return void
     */
    private function addOrderItemData(
        $order, $newOrderItem, $rmaItem, $itemNsamt, $itemDcamt,
        $itemNetwr, $isDecimalFormat, $bundleChild = null
    ) {
        $skuPrefix = $this->getSKUPrefix($order->getStoreId()) ?: '';
        $skuPrefix = $skuPrefix ?: '';
        $sku = $newOrderItem->getSku();
        $qty = $rmaItem->getQtyRequested();
        if ($bundleChild) {
            $sku = $bundleChild->getSku();
            $qty = $bundleChild->getQtyRequested();
        }

        $stripSku = str_replace($skuPrefix, '', $sku);
        $this->orderItemData[] = [
            'prdCD' => $stripSku,
            'qty' => (int)$qty,
            'price' => $this->orderData->roundingPrice($itemNsamt/(int)$qty, $isDecimalFormat),
            'salAmt' => $this->orderData->roundingPrice($itemNsamt, $isDecimalFormat),
            'dcAmt' => $this->orderData->roundingPrice($itemDcamt, $isDecimalFormat),
            'netSalAmt' => $this->orderData->roundingPrice($itemNetwr, $isDecimalFormat)
        ];

        $this->itemsSubtotal += $itemNsamt;
        $this->itemsDiscountAmount += $itemDcamt;
        $this->itemsGrandTotal += $itemNetwr;
    }

    /**
     * Get quantity ratio for partial
     *
     * @param $rma
     * @return float|int
     */
    private function getQuantityRatioForPartial($rma)
    {
        $totalReturn = 0;
        $totalOrder = 0;
        $rmaItems = $rma->getItems();
        $order = $rma->getOrder();
        foreach ($rmaItems as $rmaItem) {
            $orderItem = $order->getItemById($rmaItem->getOrderItemId());
            if ($orderItem->getProductType() == 'bundle') {
                continue;
            }
            $totalReturn += $rmaItem->getQtyRequested();
        }

        foreach ($order->getAllVisibleItems() as $orderItem) {
            if ($orderItem->getProductType() != 'bundle') {
                $totalOrder += $orderItem->getQtyOrdered();
            } else {
                foreach ($orderItem->getChildrenItems() as $bundleChild) {
                    $totalOrder += $bundleChild->getQtyOrdered();
                }
            }
        }
        return $totalReturn / $totalOrder;
    }
}
