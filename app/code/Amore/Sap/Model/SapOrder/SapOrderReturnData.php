<?php
/**
 * Created by Eguana.
 * User: Brian
 * Date: 2020-07-22
 * Time: 오후 8:14
 */

namespace Amore\Sap\Model\SapOrder;

use Amore\Sap\Exception\RmaTrackNoException;
use Amore\Sap\Model\Source\Config;
use Eguana\GWLogistics\Model\QuoteCvsLocationRepository;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Rma\Api\RmaRepositoryInterface;
use Magento\Sales\Api\OrderItemRepositoryInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Model\ResourceModel\Order\Item\Collection;
use Magento\Sales\Model\ResourceModel\Order\Item\CollectionFactory;
use Magento\Store\Api\StoreRepositoryInterface;

class SapOrderReturnData extends AbstractSapOrder
{
    const AUGRU_RETURN_CODE = 'R05';

    const ABRVW_RETURN_CODE = 'R51';

    /**
     * @var RmaRepositoryInterface
     */
    private $rmaRepository;
    /**
     * @var CustomerRepositoryInterface
     */
    private $customerRepository;
    /**
     * @var TimezoneInterface
     */
    private $timezoneInterface;
    /**
     * @var QuoteCvsLocationRepository
     */
    private $quoteCvsLocationRepository;
    /**
     * @var OrderItemRepositoryInterface
     */
    private $orderItemRepository;
    /**
     * @var CollectionFactory
     */
    private $itemCollectionFactory;

    /**
     * SapOrderReturnData constructor.
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param OrderRepositoryInterface $orderRepository
     * @param StoreRepositoryInterface $storeRepository
     * @param Config $config
     * @param RmaRepositoryInterface $rmaRepository
     * @param CustomerRepositoryInterface $customerRepository
     * @param TimezoneInterface $timezoneInterface
     * @param QuoteCvsLocationRepository $quoteCvsLocationRepository
     * @param OrderItemRepositoryInterface $orderItemRepository
     * @param CollectionFactory $itemCollectionFactory
     */
    public function __construct(
        SearchCriteriaBuilder $searchCriteriaBuilder,
        OrderRepositoryInterface $orderRepository,
        StoreRepositoryInterface $storeRepository,
        Config $config,
        RmaRepositoryInterface $rmaRepository,
        CustomerRepositoryInterface $customerRepository,
        TimezoneInterface $timezoneInterface,
        QuoteCvsLocationRepository $quoteCvsLocationRepository,
        OrderItemRepositoryInterface $orderItemRepository,
        CollectionFactory $itemCollectionFactory
    )
    {
        $this->rmaRepository = $rmaRepository;
        $this->customerRepository = $customerRepository;
        $this->timezoneInterface = $timezoneInterface;
        $this->quoteCvsLocationRepository = $quoteCvsLocationRepository;
        $this->orderItemRepository = $orderItemRepository;
        $this->itemCollectionFactory = $itemCollectionFactory;
        parent::__construct($searchCriteriaBuilder, $orderRepository, $storeRepository, $config);
    }

    /**
     * @param \Magento\Rma\Model\Rma $rma
     * @param string $sapIncrementId
     * @throws RmaTrackNoException
     * @throws NoSuchEntityException
     * @throws LocalizedException
     */
    public function singleOrderData($rma)
    {
        $source = $this->config->getSourceByStore('store', $rma->getStoreId());
        $rmaIncrementId = $this->getRmaIncrementId($rma);
        $rmaData = $this->getRmaData($rma, $rmaIncrementId);
        $rmaItemData = $this->getRmaItemData($rma, $rmaIncrementId);

        $request =  [
            "request" => [
                "header" => [
                    "source" => $source
                ],
                "input" => [
                    "itHead" => $rmaData,
                    'itItem' => $rmaItemData
                ]
            ]
        ];
        return $request;
    }

    /**
     * @param \Magento\Rma\Model\Rma $rma
     * @param string $sapIncrementId
     * @throws RmaTrackNoException
     * @throws NoSuchEntityException
     * @throws LocalizedException
     */
    public function getRmaData($rma, $sapIncrementId)
    {
        $storeId = $rma->getStoreId();
        $order = $rma->getOrder();
        $customer = $this->getCustomer($rma->getCustomerId());
        $shippingAddress = $order->getShippingAddress();
        $pointUsed = $order->getRewardPointsBalance();
        $orderTotal = round($order->getSubtotalInclTax() + $order->getDiscountAmount() + $order->getShippingAmount());
        $trackData = $this->getTracks($rma);

        $bindData[] = [
            'vkorg' => $this->config->getSalesOrg('store', $storeId),
            'kunnr' => $this->config->getClient('store', $storeId),
            'odrno' => "R" . $sapIncrementId,
            'odrdt' => $this->dateFormatting($rma->getDateRequested(), 'Ymd'),
            'odrtm' => $this->dateFormatting($rma->getDateRequested(), 'His'),
            'paymtd' => '',
            'payde' => '',
            'paytm' => '',
            'auart' => self::RETURN_ORDER,
            'augru' => self::AUGRU_RETURN_CODE,
            'augruText' => '',
            'abrvw' => self::ABRVW_RETURN_CODE,
            // 주문자회원코드-직영몰자체코드
            'custid' => $customer != '' ? $customer->getCustomAttribute('integration_number')->getValue() : '',
            'custnm' => $order->getCustomerLastname() . $order->getCustomerLastname(),
            //배송지 id - 직영몰 자체코드, 없으면 공백
            'recvid' => '',
            'recvnm' => $shippingAddress->getName(),
            'postCode' => $this->cvsShippingCheck($order) ? '00000' : $shippingAddress->getPostcode(),
            'addr1' => $this->cvsShippingCheck($order) ? $this->getCsvAddress($shippingAddress) : $shippingAddress->getRegion(),
            'addr2' => $this->cvsShippingCheck($order) ? '.' : $shippingAddress->getCity(),
            'addr3' => $this->cvsShippingCheck($order) ? '.' : preg_replace('/\r\n|\r|\n/', ' ', implode(PHP_EOL, $shippingAddress->getStreet())),
            'land1' => $shippingAddress->getCountryId(),
            'telno' => $shippingAddress->getTelephone(),
            'hpno' => $shippingAddress->getTelephone(),
            'waerk' => $order->getOrderCurrencyCode(),
            'nsamt' => $this->getRmaSubtotalInclTax($rma),
            'dcamt' => $this->getRmaDiscountAmount($rma),
            'slamt' => $order->getGrandTotal() == 0 ? $order->getGrandTotal() : $this->getRmaGrandTotal($rma, $orderTotal, $pointUsed),
            'miamt' => $this->getRmaPointsUsed($rma, $pointUsed, $orderTotal),
            'shpwr' => '',
            'mwsbp' => $this->getRmaTaxAmount($rma),
            'spitn1' => '',
            'vkorgOri' => $this->config->getSalesOrg('store', $storeId),
            'kunnrOri' => $this->config->getClient('store', $storeId),
            'odrnoOri' => $this->getSapOrderId($order),
            // 이건 물건 종류 갯수(물건 전체 수량은 아님)
            'itemCnt' => count($rma->getItems()),
            // 영업 플랜트 : 알수 없을 경우 공백
            'werks' => '',
            // 영업저장위치 : 알수 없을 경우 공백
            'lgort' => '',
            'rmano' => '',
            // 납품처
            'kunwe' => $this->cvsShippingCheck($order) ? $this->config->getSupplyContractor('store', $storeId) : $this->config->getHomeDeliveryContractor('store', $storeId),
            // trackNo 가져와야 함
            'ztrackId' => $trackData['track_number']
        ];
        return $bindData;
    }

    /**
     * @param \Magento\Rma\Model\Rma $rma
     * @param string $sapIncrementId
     */
    public function getRmaItemData($rma, $sapIncrementId)
    {
        $rmaItemData = [];
        $storeId = $rma->getStoreId();
        $rmaItems = $rma->getItems();
        $order = $rma->getOrder();
        $orderTotal = round($order->getSubtotalInclTax() + $order->getDiscountAmount() + $order->getShippingAmount());
        $mileageUsedAmount = $order->getRewardPointsBalance();
        $originPosnr = $this->getOrderItemPosnr($rma);

        $cnt = 1;
        foreach ($rmaItems as $rmaItem) {
            $orderItem = $this->orderItemRepository->get($rmaItem->getOrderItemId());
            $configurableCheckedItem = $this->configurableProductCheck($orderItem);
            $mileagePerItem = $this->mileageSpentRateByItem(
                $orderTotal,
                $orderItem->getRowTotalInclTax(),
                $orderItem->getDiscountAmount(),
                $mileageUsedAmount
            );
            $itemGrandTotal = $orderItem->getRowTotal()
                - $orderItem->getDiscountAmount()
                - $mileagePerItem;
            $itemGrandTotalInclTax = $orderItem->getRowTotalInclTax()
                - $orderItem->getDiscountAmount()
                - $mileagePerItem;

            $rmaItemData[] = [
                'itemVkorg' => $this->config->getSalesOrg('store', $storeId),
                'itemKunnr' => $this->config->getClient('store', $storeId),
                'itemOdrno' => "R" . $sapIncrementId,
                'itemPosnr' => $cnt,
                'itemMatnr' => $this->configurableProductCheck($orderItem)->getSku(),
                'itemMenge' => intval($rmaItem->getQtyRequested()),
                // 아이템 단위, Default : EA
                'itemMeins' => 'EA',
                'itemNsamt' => $orderItem->getPriceInclTax() * $rmaItem->getQtyRequested(),
                'itemDcamt' => $this->getRateAmount($orderItem->getDiscountAmount(), $this->getNetQty($orderItem), $rmaItem->getQtyRequested()),
                'itemSlamt' => $this->getRateAmount($itemGrandTotalInclTax, $this->getNetQty($orderItem), $rmaItem->getQtyRequested()),
                'itemMiamt' => $this->getRateAmount($mileagePerItem, $this->getNetQty($orderItem), $rmaItem->getQtyRequested()),
                // 상품이 무상제공인 경우 Y 아니면 N
                'itemFgflg' => $orderItem->getPrice() == 0 ? 'Y' : 'N',
                'itemMilfg' => empty($mileageUsedAmount) ? 'N' : 'Y',
                'itemAuart' => self::RETURN_ORDER,
                'itemAugru' => self::AUGRU_RETURN_CODE,
                'itemAbrvw' => self::ABRVW_RETURN_CODE,
                'itemNetwr' => $this->getRateAmount($itemGrandTotal, $this->getNetQty($orderItem), $rmaItem->getQtyRequested()),
                'itemMwsbp' => $this->getRateAmount($orderItem->getTaxAmount(), $this->getNetQty($orderItem), $rmaItem->getQtyRequested()),
                'itemVkorg_ori' => $this->config->getSalesOrg('store', $storeId),
                'itemKunnr_ori' => $this->config->getClient('store', $storeId),
                'itemOdrno_ori' => $this->getSapOrderId($order),
                'itemPosnr_ori' => $originPosnr[$configurableCheckedItem->getItemId()]
            ];
            $cnt++;
        }
        return $rmaItemData;
    }

    /**
     * @param $order \Magento\Sales\Model\Order
     */
    public function cvsShippingCheck($order)
    {
        switch ($order->getShippingMethod()) {
            case 'gwlogistics_CVS':
                $cvsCheck = true;
                break;
            case 'flatrate_flatrate':
                $cvsCheck = false;
                break;
            default:
                $cvsCheck = false;
        }
        return $cvsCheck;
    }

    /**
     * @param $shippingAddress
     * @return string
     * @throws NoSuchEntityException
     */
    public function getCsvAddress($shippingAddress)
    {
        $cvsLocationId = $shippingAddress->getData('cvs_location_id');
        $cvsStoreData = $this->quoteCvsLocationRepository->getById($cvsLocationId);
        $cvsAddress = $cvsStoreData->getCvsAddress() . ' ' . $cvsStoreData->getCvsStoreName() . ' ' . $cvsStoreData->getLogisticsSubType();

        return $cvsAddress;
    }

    public function dateFormatting($date, $format)
    {
        return $this->timezoneInterface->date($date)->format($format);
    }

    /**
     * @param \Magento\Sales\Model\Order $order
     */
    public function getSapOrderId($order)
    {
        $sapOrderIncrementId = $order->getData('sap_order_increment_id');
        if (empty($sapOrderIncrementId)) {
            $incrementId = $order->getIncrementId();
        } else {
            $incrementId = $sapOrderIncrementId;
        }
        return $incrementId;
    }

    /**
     * @param \Magento\Rma\Model\Rma $rma
     */
    public function getRmaIncrementId($rma)
    {
        $rmaSendCheck = $rma->getData('sap_return_send_check');
        $rmaIncrementId = $rma->getIncrementId();

        if (is_null($rmaSendCheck)) {
            $incrementIdForSap = $rmaIncrementId;
        } elseif ($rmaSendCheck == 0 || $rmaSendCheck == 2) {
            $currentDate = $this->timezoneInterface->date()->format('ymdHis');
            $incrementIdForSap = $rmaIncrementId . '_' . $currentDate;
        } else {
            $incrementIdForSap = $rmaIncrementId;
        }
        return $incrementIdForSap;
    }

    /**
     * @param $customerId
     * @return CustomerInterface|string
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function getCustomer($customerId)
    {
        if (empty($customerId)) {
            return '';
        } else {
            /** @var CustomerInterface $customer */
            $customer = $this->customerRepository->getById($customerId);
            return $customer;
        }
    }

    /**
     * @param \Magento\Rma\Model\Rma $rma
     */
    public function getTracks($rma)
    {
        $tracks = $rma->getTracks();
        $trackData = [];
        foreach ($tracks as $track) {
            $trackData[] = [
                'carrier_title' => $track->getCarrierTitle(),
                'carrier_code' => $track->getCarrierCode(),
                'rma_id' => $track->getRmaEntityId(),
                'track_number' => $track->getTrackNumber()
            ];
        }

        $trackCount = count($trackData);
        if ($trackCount == 1) {
            return $trackData[0];
        } elseif ($trackCount == 0) {
            throw new RmaTrackNoException(__("Tracking No Does Not Exist."));
        } else {
            throw new RmaTrackNoException(__("Tracking No Exist more than 1."));
        }
    }

    public function getRateAmount($orderItemAmount, $orderItemQty, $rmaItemQty)
    {
        return $orderItemAmount * ($rmaItemQty / $orderItemQty);
    }

    /**
     * @param \Magento\Rma\Model\Rma $rma
     */
    public function getOrderItemPosnr($rma)
    {
        $order = $rma->getOrder();
        $orderItems = $order->getAllItems();
        $originPosnrData = [];

        $cnt = 1;
        foreach ($orderItems as $orderItem) {
            if ($orderItem->getProductType() != 'simple') {
                continue;
            }
            $originPosnrData[$orderItem->getItemId()] = $cnt;
            $cnt++;
        }

        return $originPosnrData;
    }

    public function mileageSpentRateByItem($orderTotal, $itemRowTotal, $itemDiscountAmount, $mileageUsed)
    {
        $itemTotal = round($itemRowTotal - $itemDiscountAmount, 2);

        if ($mileageUsed) {
            return round(($itemTotal/$orderTotal) * $mileageUsed);
        }
        return is_null($mileageUsed) ? '0' : $mileageUsed;
    }

    /**
     * @param $orderItem \Magento\Sales\Api\Data\OrderItemInterface
     */
    public function configurableProductCheck($orderItem)
    {
        $simpleItemCollection = $this->getOrderChildItemCollection($orderItem->getOrderId(), $orderItem->getSku());
        $simpleItem = $simpleItemCollection->getFirstItem();

        if ($orderItem->getProductType() == "simple") {
            return $orderItem;
        } else {
            return $simpleItem;
        }
    }

    public function getOrderChildItemCollection($orderId, $sku)
    {
        /** @var Collection $collection */
        $collection = $this->itemCollectionFactory->create();

        $collection->addFieldToFilter('order_id', ['eq' => $orderId])
            ->addFieldToFilter('sku', ['eq' => $sku])
            ->addFieldToFilter('product_type', ['eq' => 'simple'])
            ->addFieldToSelect(["item_id", "order_id", "parent_item_id", "store_id", "product_id", "sku"]);

        return $collection;
    }

    /**
     * @param \Magento\Rma\Model\Rma $rma
     * @param $orderTotal
     * @param $pointsUsed
     * @return int
     */
    public function getRmaGrandTotal($rma, $orderTotal, $pointsUsed)
    {
        $grandTotal = 0;
        $rmaItems = $rma->getItems();
        foreach ($rmaItems as $rmaItem) {
            $orderItem = $this->orderItemRepository->get($rmaItem->getOrderItemId());
            $mileagePerItem = $this->mileageSpentRateByItem(
                $orderTotal,
                $orderItem->getRowTotalInclTax(),
                $orderItem->getDiscountAmount(),
                $pointsUsed
            );
            $itemGrandTotal = $orderItem->getRowTotal() - $orderItem->getDiscountAmount() - $mileagePerItem;
            $grandTotal += $this->getRateAmount($itemGrandTotal, $this->getNetQty($orderItem), $rmaItem->getQtyRequested());
        }
        return $grandTotal;
    }

    /**
     * @param \Magento\Rma\Model\Rma $rma
     */
    public function getRmaDiscountAmount($rma)
    {
        $discountAmount = 0;
        $rmaItems = $rma->getItems();
        foreach ($rmaItems as $rmaItem) {
            $orderItem = $this->orderItemRepository->get($rmaItem->getOrderItemId());
            $discountAmount +=  ($orderItem->getDiscountAmount() * $rmaItem->getQtyRequested() / $this->getNetQty($orderItem));
        }
        return $discountAmount;
    }

    /**
     * @param \Magento\Rma\Model\Rma $rma
     */
    public function getRmaSubtotalInclTax($rma)
    {
        $subtotalInclTax = 0;
        $rmaItems = $rma->getItems();
        foreach ($rmaItems as $rmaItem) {
            $orderItem = $this->orderItemRepository->get($rmaItem->getOrderItemId());
            $subtotalInclTax += ($orderItem->getPriceInclTax() * $rmaItem->getQtyRequested());
        }
        return $subtotalInclTax;
    }

    /**
     * @param \Magento\Rma\Model\Rma $rma
     * @param $pointsUsed
     * @param $orderTotal
     * @return float|int|string
     */
    public function getRmaPointsUsed($rma, $pointsUsed, $orderTotal)
    {
        $mileage = 0;
        $rmaItems = $rma->getItems();

        foreach ($rmaItems as $rmaItem) {
            $orderItem = $this->orderItemRepository->get($rmaItem->getOrderItemId());
            $mileagePerItem = $this->mileageSpentRateByItem(
                $orderTotal,
                $orderItem->getRowTotalInclTax(),
                $orderItem->getDiscountAmount(),
                $pointsUsed
            );
            $mileage += $this->getRateAmount($mileagePerItem, $this->getNetQty($orderItem), $rmaItem->getQtyRequested());
        }
        return $mileage;
    }

    /**
     * @param \Magento\Rma\Model\Rma $rma
     */
    public function getRmaTaxAmount($rma)
    {
        $taxAmount = 0;
        $rmaItems = $rma->getItems();

        foreach ($rmaItems as $rmaItem) {
            $orderItem = $this->orderItemRepository->get($rmaItem->getOrderItemId());
            $taxAmount += $orderItem->getTaxAmount();
        }
        return $taxAmount;
    }


    /**
     * @param \Magento\Sales\Api\Data\OrderItemInterface $orderItem
     */
    public function getNetQty($orderItem)
    {
        return $orderItem->getQtyOrdered() - $orderItem->getQtyRefunded() - $orderItem->getQtyReturned();
    }
}
