<?php
/**
 * Created by Eguana.
 * User: Brian
 * Date: 2020-06-05
 * Time: 오후 1:22
 */

namespace Amore\Sap\Model\SapOrder;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Api\InvoiceRepositoryInterface;
use Magento\Rma\Api\RmaRepositoryInterface;
use Magento\Sales\Model\Order;
use Magento\Store\Api\StoreRepositoryInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;

class SapOrderConfirmData
{
// 정상 주문
    const NORMAL_ORDER = 'ZA01';
    // 반품
    const RETURN_ORDER = 'ZR01';
    // 잡출 주문
    const SAMPLE_ORDER = 'ZFA1';
    // 잡출 반품
    const SAMPLE_RETURN = 'ZFR1';

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;
    /**
     * @var SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;
    /**
     * @var OrderRepositoryInterface
     */
    private $orderRepository;
    /**
     * @var InvoiceRepositoryInterface
     */
    private $invoiceRepository;
    /**
     * @var RmaRepositoryInterface
     */
    private $rmaRepository;
    /**
     * @var StoreRepositoryInterface
     */
    private $storeRepository;

    /**
     * SapOrderService constructor.
     * @param ScopeConfigInterface $scopeConfig
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param OrderRepositoryInterface $orderRepository
     * @param InvoiceRepositoryInterface $invoiceRepository
     * @param RmaRepositoryInterface $rmaRepository
     * @param StoreRepositoryInterface $storeRepository
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        OrderRepositoryInterface $orderRepository,
        InvoiceRepositoryInterface $invoiceRepository,
        RmaRepositoryInterface $rmaRepository,
        StoreRepositoryInterface $storeRepository
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->orderRepository = $orderRepository;
        $this->invoiceRepository = $invoiceRepository;
        $this->rmaRepository = $rmaRepository;
        $this->storeRepository = $storeRepository;
    }

    public function singleOrderData($incrementId)
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
        $bindData = [];

        if ($invoice != null) {
            if ($orderData == null) {
                throw new NoSuchEntityException(__('Such Order Data does not exist.'));
            }
            $shippingAddress = $orderData->getShippingAddress();

            $bindData[] = [
                'vkorg' => '영업조직. 중국:CN10, 프랑스:FR40, 미국:US10',
                'kunnr' => '각 직영몰 거래처코드. 국가코드 + 순번(6)',
                'odrno' => $orderData->getIncrementId(),
                'odrdt' => $orderData->getCreatedAt(),
                'odrtm' => $orderData->getCreatedAt(),
                'paymtd' => $orderData->getPayment()->getMethod(),
                'payde' => $invoice->getCreatedAt(),
                'paytm' => $invoice->getCreatedAt(),
                'auart' => $this->getOrderType($orderData->getEntityId()),
                'aurgu' => 'ORDER REASON CODE',
                'abrvw' => 'USAGE INDICATOR?',
                'augruText' => 'ORDER REASON TEXT',
                'custid' => '주문자회원코드-직영몰자체코드',
                'custnm' => $orderData->getCustomerLastname() . $orderData->getCustomerLastname(),
                'recvid' => '배송지 id - 직영몰 자체코드, 없으면 공백',
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
                'SHPTP' => '배송비 주체. A : 본사부담, B : 고객부담',
                'spitn1' => '',
                'vkorgOri' => '주문번호 영업조직',
                'kunnrOri' => '주문번호 거래처코드',
                'odrnoOri' => $orderData->getIncrementId(),
                // 이건 물건 종류 갯수(물건 전체 수량은 아님)
                'itemCnt' => $orderData->getTotalItemCount(),
                'werks' => '영업 플랜트 : 알수 없을 경우 공백',
                'lgort' => '영업저장위치 : 알수 없을 경우 공백',
                'rmano' => $this->getRma($orderData->getEntityId()) == null ? '' : $this->getRma($orderData->getEntityId())->getEntityId(),
                'IT_ITEM' => $this->getOrderItem($orderData)
            ];
        }

        return $bindData;
    }


    /**
     * @param string $incrementId
     */
    public function getOrderItem($incrementId)
    {
        /** @var Order $order */
        $order = $this->getOrderInfo($incrementId);

        $orderItems = $order->getAllVisibleItems();
        $orderItemData = [];
        $cnt = 1;
        /** @var \Magento\Sales\Api\Data\OrderItemInterface $orderItem */
        foreach ($orderItems as $orderItem) {
            $itemGrandTotal = $orderItem->getRowTotal() - $orderItem->getDiscountAmount() + $orderItem->getTaxAmount();
            $orderItemData[] = [
                'itemVkorg' => '영업조직. 중국:CN10, 프랑스:FR40, 미국:US10',
                'itemKunnr' => '각 직영몰 거래처코드. 국가코드 + 순번(6)',
                'itemOdrno' => $order->getIncrementId(),
                'itemPosnr' => $cnt,
                'itemMatnr' => 'item Material',
                'itemSatnr' => '??',
                'itemMenge' => $orderItem->getQtyOrdered(),
                'itemMeins' => '아이템 단위',
                'itemNsamt' => $orderItem->getPriceInclTax(),
                'itemDcamt' => $orderItem->getDiscountAmount(),
                'itemSlamt' => $itemGrandTotal,
                'itemMiamt' => '마일리지 사용비율',
                'itemFgflg' => '무상제공인경우 Y 아니면 N',
                'itemMilfg' => '마일리지 구매인 경우 Y 아니면 N',
                'itemAuart' => $this->getOrderType($order->getEntityId()),
                'itemAugru' => 'order reason',
                'itemAbrvw' => 'USAGE INDICATOR',
                'itemNetwr' => $orderItem->getRowTotal(),
                'itemMwsbp' => $orderItem->getTaxAmount(),
                'itemVkorg_ori' => '영업조직. 중국:CN10, 프랑스:FR40, 미국:US10',
                'itemKunnr_ori' => '각 직영몰 거래처코드. 국가코드 + 순번(6)',
                'itemOdrno_ori' => $order->getIncrementId(),
                'itemPosnr_ori' => $cnt
            ];
            $cnt++;
        }
        return $orderItemData;
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
