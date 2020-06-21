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

    public function orderDataForMiddleware($incrementId)
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
                'VKORG' => '영업조직. 중국:CN10, 프랑스:FR40, 미국:US10',
                'KUNNR' => '각 직영몰 거래처코드. 국가코드 + 순번(6)',
                'ODRNO' => $orderData->getIncrementId(),
                'ODRDT' => $orderData->getCreatedAt(),
                'ODRTM' => $orderData->getCreatedAt(),
                'PAYMTD' => $orderData->getPayment()->getMethod(),
                'PAYDT' => $invoice->getCreatedAt(),
                'PAYTM' => $invoice->getCreatedAt(),
                'AUART' => $this->getOrderType($orderData->getEntityId()),
                'AUGRU' => 'ORDER REASON CODE',
                'ABRVW' => 'USAGE INDICATOR?',
                'AUGRU_TEXT' => 'ORDER REASON TEXT',
                'CUSTID' => '주문자회원코드-직영몰자체코드',
                'CUSTNAM' => $orderData->getCustomerLastname() . $orderData->getCustomerLastname(),
                'RECVID' => '배송지 id - 직영몰 자체코드, 없으면 공백',
                'RECVNM' => $shippingAddress->getName(),
                'POST_CODE' => $shippingAddress->getPostcode(),
                'ADDR1' => $shippingAddress->getRegion(),
                'ADDR2' => $shippingAddress->getCity(),
                'ADDR3' => $shippingAddress->getStreet(),
                'LAND1' => $shippingAddress->getCountryId(),
                'TELNO' => $shippingAddress->getTelephone(),
                'HPNO' => $shippingAddress->getTelephone(),
                'WAERK' => $orderData->getOrderCurrencyCode(),
                'NSAMT' => $orderData->getSubtotalInclTax(),
                'DCAMT' => $orderData->getDiscountAmount(),
                'SLAMT' => $orderData->getGrandTotal(),
                'MIAMT' => $orderData->getRewardPointsBalance(),
                'SHPWR' => $orderData->getShippingAmount(),
                'MWSBP' => $orderData->getTaxAmount(),
                'SHPTP' => '배송비 주체. A : 본사부담, B : 고객부담',
                'SPIND1' => '',
                'SPIND2' => '',
                'SPIND3' => '',
                'SPIND4' => '',
                'SPIND5' => '',
                'SPITN1' => '',
                'SPITN2' => '',
                'SPITN3' => '',
                'SPITN4' => '',
                'SPITN5' => '',
                'VKORG_ORI' => '주문번호 영업조직',
                'KUNNR_ORI' => '주문번호 거래처코드',
                'ORDNO_ORI' => $orderData->getIncrementId(),
                'ITEM_CNT' => $orderData->getTotalItemCount(),
                'WERKS' => '영업 플랜트 : 알수 없을 경우 공백',
                'LGORT' => '영업저장위치 : 알수 없을 경우 공백',
                'RMANO' => $this->getRma($orderData->getEntityId()) == null ? '' : $this->getRma($orderData->getEntityId())->getEntityId(),
                'IT_ITEM' => $this->getOrderItem($orderData)
            ];
        }

        return $bindData;
    }


    /**
     * @param OrderInterface $order
     */
    public function getOrderItem($order)
    {
        $orderItems = $order->getAllVisibleItems();
        $orderItemData = [];
        $cnt = 1;
        /** @var \Magento\Sales\Api\Data\OrderItemInterface $orderItem */
        foreach ($orderItems as $orderItem) {
            $itemGrandTotal = $orderItem->getRowTotal() - $orderItem->getDiscountAmount() + $orderItem->getTaxAmount();
            $orderItemData[] = [
                'VKORG' => '영업조직. 중국:CN10, 프랑스:FR40, 미국:US10',
                'KUNNR' => '각 직영몰 거래처코드. 국가코드 + 순번(6)',
                'ODRNO' => $order->getIncrementId(),
                'POSNR' => $cnt,
                'MATNR' => 'item Material',
                'SATNR' => '뭐지이거',
                'MENGE' => $orderItem->getQtyOrdered(),
                'MEINS' => '아이템 단위',
                'NSAMT' => $orderItem->getPriceInclTax(),
                'DCAMT' => $orderItem->getDiscountAmount(),
                'SLAMT' => $itemGrandTotal,
                'MIAMT' => '마일리지 사용비율',
                'FGFLG' => '무상제공인경우 Y 아니면 N',
                'MILFG' => '마일리지 구매인 경우 Y 아니면 N',
                'AUART' => $this->getOrderType($order->getEntityId()),
                'AUGRU' => 'order reason, 뭐여 이건',
                'ABRVW' => 'USAGE INDICATOR, 뭐임',
                'NETWR' => $orderItem->getRowTotal(),
                'MWSBP' => $orderItem->getTaxAmount(),
                'VKORG_ORI' => '영업조직. 중국:CN10, 프랑스:FR40, 미국:US10',
                'KUNNR_ORI' => '각 직영몰 거래처코드. 국가코드 + 순번(6)',
                'ODRNO_ORI' => $order->getIncrementId(),
                'POSNR_ORI' => $cnt
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
