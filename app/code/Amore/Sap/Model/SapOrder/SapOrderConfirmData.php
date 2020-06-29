<?php
/**
 * Created by Eguana.
 * User: Brian
 * Date: 2020-06-05
 * Time: 오후 1:22
 */

namespace Amore\Sap\Model\SapOrder;

use Amore\Sap\Model\Source\Config;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Api\InvoiceRepositoryInterface;
use Magento\Rma\Api\RmaRepositoryInterface;
use Magento\Sales\Model\Order;
use Magento\Store\Api\StoreRepositoryInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;

class SapOrderConfirmData extends AbstractSapOrder
{
    /**
     * @var InvoiceRepositoryInterface
     */
    private $invoiceRepository;
    /**
     * @var RmaRepositoryInterface
     */
    private $rmaRepository;

    /**
     * SapOrderConfirmData constructor.
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param OrderRepositoryInterface $orderRepository
     * @param StoreRepositoryInterface $storeRepository
     * @param Config $config
     * @param InvoiceRepositoryInterface $invoiceRepository
     * @param RmaRepositoryInterface $rmaRepository
     */
    public function __construct(
        SearchCriteriaBuilder $searchCriteriaBuilder,
        OrderRepositoryInterface $orderRepository,
        StoreRepositoryInterface $storeRepository,
        Config $config,
        InvoiceRepositoryInterface $invoiceRepository,
        RmaRepositoryInterface $rmaRepository
    ) {
        parent::__construct($searchCriteriaBuilder, $orderRepository, $storeRepository, $config);
        $this->invoiceRepository = $invoiceRepository;
        $this->rmaRepository = $rmaRepository;
    }

    /**
     * @param $incrementId
     * @return array[]
     * @throws NoSuchEntityException
     */
    public function singleOrderData($incrementId)
    {
        /** @var Order $order */
        $order = $this->getOrderInfo($incrementId);

        $source = $this->config->getSourceByStore('store' ,$order->getStoreId());
        $orderData = $this->getOrderData($incrementId);
        $itemData = $this->getOrderItem($incrementId);

        if (empty($orderData) && empty($itemData)) {
            $msg = __("Order Data and Item Data do not exist.");
            return ['code' => "0001", "message" => $msg];
        } elseif (empty($orderData)) {
            $msg = __("Order Data does not exist.");
            return ['code' => "0001", "message" => $msg];
        } elseif (empty($itemData)) {
            $msg = __("Item Data does not exist.");
            return ['code' => "0001", "message" => $msg];
        } else {
            $request = [
                "request" => [
                    "header" => [
                        "source" => $source
                    ],
                    "input" => [
                        "itHead" => $this->getOrderData($incrementId),
                        'itItem' => $this->getOrderItem($incrementId)
                    ]
                ]
            ];
        }
        return $request;
    }

    public function massSendOrderData($incrementId)
    {
        $request = [
            "request" => [
                "header" => [
                    "source" => "source"
                ],
                "input" => [
                    "itHead" => $this->getOrderData($incrementId),
                    'itItem' => $this->getOrderItem($incrementId)
                ]
            ]
        ];
    }

    public function massOrderData($incrementId)
    {

    }

    public function massOrderItemData()
    {

    }

    public function getOrderType($orderId)
    {
        $rma = $this->getRma($orderId);
        $orderType = self::NORMAL_ORDER;

        if ($rma != null) {
            $orderType = self::RETURN_ORDER;
        }

        return $orderType;
    }

    public function getOrderInfo($incrementId)
    {
        $searchCriteria = $this->searchCriteriaBuilder
            ->addFilter('increment_id', $incrementId, 'eq')
            ->addFilter('status', 'processing', 'eq')
            ->create();

        $orderList = $this->orderRepository->getList($searchCriteria)->getItems();
        $orderCount = $this->orderRepository->getList($searchCriteria)->getTotalCount();

        if ($orderCount == 1) {
            return reset($orderList);
        } else {
            return null;
        }
    }

    public function getOrderData($incrementId)
    {
        /** @var Order $orderData */
        $orderData = $this->getOrderInfo($incrementId);
        $invoice = $this->getInvoice($orderData->getEntityId());
        $storeId = $orderData->getStoreId();
        $bindData = [];

        if ($orderData == null) {
            throw new NoSuchEntityException(
                __("Such order does not exist. Check the data and try again")
            );
        }

        if ($invoice != null) {
            $shippingAddress = $orderData->getShippingAddress();
            $trackingNumbers = implode(",",$orderData->getTrackingNumbers());

            $bindData[] = [
                'vkorg' => $this->config->getMallId('store', $storeId),
                'kunnr' => $this->config->getClient('store', $storeId),
                'odrno' => $orderData->getIncrementId(),
                'odrdt' => $orderData->getCreatedAt(),
                'odrtm' => $orderData->getCreatedAt(),
                'paymtd' => $this->getPaymentCode($orderData->getPayment()->getMethod()),
                'payde' => $invoice->getCreatedAt(),
                'paytm' => $invoice->getCreatedAt(),
                'auart' => $this->getOrderType($orderData->getEntityId()),
                'aurgu' => self::NORMAL_ORDER,
                'augruText' => 'ORDER REASON TEXT',
                'custid' => '주문자회원코드-직영몰자체코드',
                'custnm' => $orderData->getCustomerLastname() . $orderData->getCustomerLastname(),
                //배송지 id - 직영몰 자체코드, 없으면 공백
                'recvid' => '',
                'recvnm' => $shippingAddress->getName(),
                'postCode' => $shippingAddress->getPostcode(),
                'addr1' => $shippingAddress->getRegion(),
                'addr2' => $shippingAddress->getCity(),
                'addr3' => $shippingAddress->getStreet(),
                'land1' => $shippingAddress->getCountryId(),
                'telno' => $shippingAddress->getTelephone(),
                'hpno' => $shippingAddress->getTelephone(),
                'waerk' => $orderData->getOrderCurrencyCode(),
                'nsamt' => $orderData->getSubtotalInclTax(),
                'dcamt' => $orderData->getDiscountAmount(),
                'slamt' => $orderData->getGrandTotal(),
                'miamt' => $orderData->getRewardPointsBalance(),
                'shpwr' => $orderData->getShippingAmount(),
                'mwsbp' => $orderData->getTaxAmount(),
                // 새로 받은거에서 이 필드 사라졌는데 확인 필요
                'spitn1' => 'shipping meme',
                'vkorgOri' => $this->config->getMallId('store', $storeId),
                'kunnrOri' => $this->config->getClient('store', $storeId),
                'odrnoOri' => $orderData->getIncrementId(),
                // 이건 물건 종류 갯수(물건 전체 수량은 아님)
                'itemCnt' => $orderData->getTotalItemCount(),
                // 영업 플랜트 : 알수 없을 경우 공백
                'werks' => '',
                // 영업저장위치 : 알수 없을 경우 공백
                'lgort' => '',
                'rmano' => $this->getRma($orderData->getEntityId()) == null ? '' : $this->getRma($orderData->getEntityId())->getEntityId(),
                // 납품처
                'kunwe' => $this->config->getSupplyContractor('store', $storeId),
                'ztrackId' => $trackingNumbers
            ];
        }

        return $bindData;
    }


    /**
     * @param string $incrementId
     */
    public function getOrderItem($incrementId)
    {
        $orderItemData = [];

        /** @var Order $order */
        $order = $this->getOrderInfo($incrementId);
        $storeId = $order->getStoreId();
        $orderTotal = round($order->getSubtotalInclTax() + $order->getDiscountAmount());
        $invoice = $this->getInvoice($order->getEntityId());
        $mileageUsedAmount = $order->getRewardPointsBalance();

        if ($order == null) {
            throw new NoSuchEntityException(
                __("Such order does not exist. Check the data and try again")
            );
        }

        if ($invoice != null) {

            $orderItems = $order->getAllVisibleItems();
            $cnt = 1;
            /** @var \Magento\Sales\Api\Data\OrderItemInterface $orderItem */
            foreach ($orderItems as $orderItem) {
                $itemGrandTotal = $orderItem->getRowTotal() - $orderItem->getDiscountAmount() + $orderItem->getTaxAmount();
                $orderItemData[] = [
                    'itemVkorg' => $this->config->getMallId('store', $storeId),
                    'itemKunnr' => $this->config->getClient('store', $storeId),
                    'itemOdrno' => $order->getIncrementId(),
                    'itemPosnr' => $cnt,
                    'itemMatnr' => $orderItem->getSku(),
                    'itemMenge' => $orderItem->getQtyOrdered(),
                    // 아이템 단위, Default : EA
                    'itemMeins' => 'EA',
                    'itemNsamt' => $orderItem->getPriceInclTax(),
                    'itemDcamt' => $orderItem->getDiscountAmount(),
                    'itemSlamt' => $orderItem->getRowTotal(),
                    'itemMiamt' => $this->mileageSpentRateByItem(
                        $orderTotal,
                        $orderItem->getRowTotal(),
                        $orderItem->getDiscountAmount(),
                        $mileageUsedAmount),
                    // 상품이 무상제공인 경우 Y 아니면 N
                    'itemFgflg' => $orderItem->getPrice() == 0 ? 'Y' : 'N',
                    'itemMilfg' => empty($mileageUsedAmount) ? 'N' : 'Y',
                    'itemAuart' => $this->getOrderType($order->getEntityId()),
                    'itemAugru' => 'order reason,',
                    'itemNetwr' => $itemGrandTotal,
                    'itemMwsbp' => $orderItem->getTaxAmount(),
                    'itemVkorg_ori' => $this->config->getMallId('store', $storeId),
                    'itemKunnr_ori' => $this->config->getClient('store', $storeId),
                    'itemOdrno_ori' => $order->getIncrementId(),
                    'itemPosnr_ori' => $cnt
                ];
                $cnt++;
            }
        }
        return $orderItemData;
    }

    public function mileageSpentRateByItem($orderTotal, $itemRowTotal, $itemDiscountAmount, $mileageUsed)
    {
        $itemTotal = round($itemRowTotal - $itemDiscountAmount, 2);

        if ($mileageUsed) {
            return round(($itemTotal/$orderTotal) * $mileageUsed);
        }
        return $mileageUsed;
    }

    public function getInvoice($orderId)
    {
        $searchCriteria = $this->searchCriteriaBuilder
            ->addFilter('order_id', $orderId, 'eq')
            ->create();

        $invoice = $this->invoiceRepository->getList($searchCriteria)->getItems();
        $invoiceCount = $this->invoiceRepository->getList($searchCriteria)->getTotalCount();

        if ($invoiceCount >= 1) {
            return reset($invoice);
        } else {
            return null;
        }
    }

    public function getPaymentCode($paymentMethod)
    {
        if ($paymentMethod == 'ecpay_ecpaypayment') {
            $paymentCode = "P";
        } else {
            $paymentCode = "S";
        }
        return $paymentCode;
    }

    public function getRma($orderId)
    {
        $searchCriteria = $this->searchCriteriaBuilder
            ->addFilter('order_id', $orderId, 'eq')
            ->create();

        $rma = $this->rmaRepository->getList($searchCriteria)->getItems();
        $rmaCount = $this->rmaRepository->getList($searchCriteria)->getTotalCount();

        if ($rmaCount >= 1) {
            return reset($rma);
        } else {
            return null;
        }
    }

    /**
     * @param \Magento\Sales\Model\Order\Address $shippingAddress
     * @param int $storeId
     * @return string
     */
    public function getName($shippingAddress, $storeId)
    {
        $firstName = $shippingAddress->getFirstname();
        $lastName = $shippingAddress->getLastname();
        $name = $lastName . $firstName;

        $store = $this->getStore($storeId);

        if ($store->getCode() == 'tw') {
            return $name;
        } else {
            $name = $firstName . $lastName;
            return $name;
        }
    }

    public function getStore($storeId)
    {
        try {
            return $this->storeRepository->get($storeId);
        } catch (\Magento\Framework\Exception\NoSuchEntityException $e) {
            $e->getMessage();
        }
    }
}
