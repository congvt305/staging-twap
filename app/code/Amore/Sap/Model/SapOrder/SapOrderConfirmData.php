<?php
/**
 * Created by Eguana.
 * User: Brian
 * Date: 2020-06-05
 * Time: 오후 1:22
 */

namespace Amore\Sap\Model\SapOrder;

use Amore\Sap\Exception\ShipmentNotExistException;
use Amore\Sap\Model\Source\Config;
use Eguana\GWLogistics\Model\QuoteCvsLocationRepository;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Eav\Api\AttributeRepositoryInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Api\InvoiceRepositoryInterface;
use Magento\Rma\Api\RmaRepositoryInterface;
use Magento\Sales\Model\Order;
use Magento\Store\Api\StoreRepositoryInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Customer\Api\CustomerRepositoryInterface;

class SapOrderConfirmData extends AbstractSapOrder
{
    const ORDER_SENT_TO_SAP_BEFORE = 0;

    const ORDER_SENT_TO_SAP_SUCCESS = 1;

    const ORDER_SENT_TO_SAP_FAIL = 2;

    const ORDER_RESENT_TO_SAP_SUCCESS = 3;

    /**
     * @var InvoiceRepositoryInterface
     */
    private $invoiceRepository;
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
     * @var \Magento\Catalog\Api\ProductRepositoryInterface
     */
    private $productRepository;
    /**
     * @var AttributeRepositoryInterface
     */
    private $eavAttributeRepositoryInterface;


    /**
     * SapOrderConfirmData constructor.
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param OrderRepositoryInterface $orderRepository
     * @param StoreRepositoryInterface $storeRepository
     * @param Config $config
     * @param InvoiceRepositoryInterface $invoiceRepository
     * @param RmaRepositoryInterface $rmaRepository
     * @param CustomerRepositoryInterface $customerRepository
     * @param TimezoneInterface $timezoneInterface
     * @param QuoteCvsLocationRepository $quoteCvsLocationRepository
     * @param \Magento\Catalog\Api\ProductRepositoryInterface $productRepository
     * @param AttributeRepositoryInterface $eavAttributeRepositoryInterface
     */
    public function __construct(
        SearchCriteriaBuilder $searchCriteriaBuilder,
        OrderRepositoryInterface $orderRepository,
        StoreRepositoryInterface $storeRepository,
        Config $config,
        InvoiceRepositoryInterface $invoiceRepository,
        RmaRepositoryInterface $rmaRepository,
        CustomerRepositoryInterface $customerRepository,
        TimezoneInterface $timezoneInterface,
        QuoteCvsLocationRepository $quoteCvsLocationRepository,
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
        AttributeRepositoryInterface $eavAttributeRepositoryInterface
    ) {
        parent::__construct($searchCriteriaBuilder, $orderRepository, $storeRepository, $config);
        $this->invoiceRepository = $invoiceRepository;
        $this->rmaRepository = $rmaRepository;
        $this->customerRepository = $customerRepository;
        $this->timezoneInterface = $timezoneInterface;
        $this->quoteCvsLocationRepository = $quoteCvsLocationRepository;
        $this->productRepository = $productRepository;
        $this->eavAttributeRepositoryInterface = $eavAttributeRepositoryInterface;
    }

    /**
     * @param $incrementId
     * @return array[]
     * @throws ShipmentNotExistException
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function singleOrderData($incrementId)
    {
        /** @var Order $order */
        $order = $this->getOrderInfo($incrementId);

        $source = $this->config->getSourceByStore('store', $order->getStoreId());
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
                        "itHead" => $orderData,
                        'itItem' => $itemData
                    ]
                ]
            ];
        }
        return $request;
    }

    public function massSendOrderData($orderData, $itemData)
    {
        $source = $this->config->getDefaultValue('sap/mall_info/source');
        if (isset($orderData[0])) {
            $sampleOrderData = $orderData[0];

            $incrementId = $sampleOrderData['odrno'];

            $sampleOrder = $this->getOrderInfo($incrementId);
            $source = $this->config->getSourceByStore('store', $sampleOrder->getStoreId());
        }

        $request = [
            "request" => [
                "header" => [
                    "source" => $source
                ],
                "input" => [
                    "itHead" => $orderData,
                    'itItem' => $itemData
                ]
            ]
        ];
        return $request;
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

    /**
     * @param $incrementId string
     * @return array
     * @throws NoSuchEntityException
     * @throws ShipmentNotExistException
     */
    public function getOrderData($incrementId)
    {
        /** @var Order $orderData */
        $orderData = $this->getOrderInfo($incrementId);
        $invoice = $this->getInvoice($orderData->getEntityId());
        $storeId = $orderData->getStoreId();
        $shippingMethod = $orderData->getShippingMethod();

        $bindData = [];

        if ($orderData == null) {
            throw new NoSuchEntityException(
                __("Such order %1 does not exist. Check the data and try again", $incrementId)
            );
        }

        if ($shippingMethod == 'gwlogistics_CVS' && !$orderData->hasShipments()) {
            throw new ShipmentNotExistException(
                __("Order %1 is CVS shipping and shipment does not Exist. Please create shipment and try again.", $incrementId)
            );
        }

        if ($invoice != null) {
            $shippingAddress = $orderData->getShippingAddress();
            $trackingNumbers = implode(",", $this->getTrackNumber($orderData));
            $customer = $this->getCustomerByOrder($orderData);

            $bindData[] = [
                'vkorg' => $this->config->getSalesOrg('store', $storeId),
                'kunnr' => $this->config->getClient('store', $storeId),
                'odrno' => $orderData->getIncrementId(),
                'odrdt' => $this->dateFormatting($orderData->getCreatedAt(), 'Ymd'),
                'odrtm' => $this->dateFormatting($orderData->getCreatedAt(), 'His'),
                'paymtd' => $this->getPaymentCode($orderData->getPayment()->getMethod()),
                'payde' => $this->dateFormatting($invoice->getCreatedAt(), 'Ymd'),
                'paytm' => $this->dateFormatting($invoice->getCreatedAt(), 'His'),
                'auart' => self::NORMAL_ORDER,
                'augru' => '',
                'augruText' => 'ORDER REASON TEXT',
                // 주문자회원코드-직영몰자체코드
                'custid' => $customer != '' ? $customer->getCustomAttribute('integration_number')->getValue() : '',
                'custnm' => $orderData->getCustomerLastname() . $orderData->getCustomerLastname(),
                //배송지 id - 직영몰 자체코드, 없으면 공백
                'recvid' => '',
                'recvnm' => $shippingAddress->getName(),
                'postCode' => $this->cvsShippingCheck($orderData) ? '00000' : $shippingAddress->getPostcode(),
                'addr1' => $this->cvsShippingCheck($orderData) ? $this->getCsvAddress($shippingAddress) : $shippingAddress->getRegion(),
                'addr2' => $this->cvsShippingCheck($orderData) ? '.' : $shippingAddress->getCity(),
                'addr3' => $this->cvsShippingCheck($orderData) ? '.' : preg_replace('/\r\n|\r|\n/', ' ', implode(PHP_EOL, $shippingAddress->getStreet())),
                'land1' => $shippingAddress->getCountryId(),
                'telno' => $shippingAddress->getTelephone(),
                'hpno' => $shippingAddress->getTelephone(),
                'waerk' => $orderData->getOrderCurrencyCode(),
                'nsamt' => $orderData->getSubtotalInclTax(),
                'dcamt' => abs($orderData->getDiscountAmount()),
                'slamt' => $orderData->getGrandTotal() == 0 ? $orderData->getGrandTotal() : $orderData->getGrandTotal() - $orderData->getShippingAmount(),
                'miamt' => is_null($orderData->getRewardPointsBalance()) ? '0' : $orderData->getRewardPointsBalance(),
                'shpwr' => $orderData->getShippingAmount(),
                'mwsbp' => $orderData->getTaxAmount(),
                'spitn1' => $orderData->getDeliveryMessage(),
                'vkorgOri' => $this->config->getSalesOrg('store', $storeId),
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
                'kunwe' => $this->kunweCheck($orderData),
                'ztrackId' => $trackingNumbers
            ];
        }

        return $bindData;
    }

    public function getOrderIncrementId($incrementId, $orderSendCheck)
    {
        if (is_null($orderSendCheck)) {
            $incrementIdForSap = $incrementId;
        } elseif ($orderSendCheck == 0 || $orderSendCheck == 2) {
            $currentDate = $this->timezoneInterface->date()->format('ymdHis');
            $incrementIdForSap = $incrementId . '_' . $currentDate;
        } else {
            $incrementIdForSap = $incrementId;
        }
        return $incrementIdForSap;
    }

    /**
     * @param $order \Magento\Sales\Model\Order
     */
    public function kunweCheck($order)
    {
        $kunwe = $this->config->getHomeDeliveryContractor('store', $order->getStoreId());
        if ($this->cvsShippingCheck($order)) {
            $shippingAddress = $order->getShippingAddress();
            $cvsLocationId = $shippingAddress->getData('cvs_location_id');
            $cvsStoreData = $this->quoteCvsLocationRepository->getById($cvsLocationId);
            $cvsType = $cvsStoreData->getLogisticsSubType();
            if ($cvsType == 'FAMI') {
                $kunwe = $this->config->getFamilyMartCode('store', $order->getStoreId());
            } else {
                $kunwe = $this->config->getSevenElevenCode('store', $order->getStoreId());
            }
            return $kunwe;
        } else {
            return $kunwe;
        }
    }

    /**
     * @param $order \Magento\Sales\Model\Order
     */
    public function getTrackNumber($order)
    {
        $trackNumbers = [];
        $trackCollection = $order->getTracksCollection();
        foreach ($trackCollection->getItems() as $track) {
            $trackNumbers[] = $track->getTrackNumber();
        }
        return $trackNumbers;
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

    /**
     * @param $order Order
     */
    public function getCustomerByOrder($order)
    {
        $customerId = $order->getCustomerId();
        if (empty($customerId)) {
            return '';
        } else {
            try {
                /** @var CustomerInterface $customer */
                $customer = $this->customerRepository->getById($customerId);
                return $customer;
            } catch (NoSuchEntityException $e) {
                return $e;
            } catch (LocalizedException $e) {
                return $e;
            }
        }
    }

    public function dateFormatting($date, $format)
    {
        return $this->timezoneInterface->date($date)->format($format);
    }

    /**
     * @param string $incrementId
     * @return array
     * @throws NoSuchEntityException
     */
    public function getOrderItem($incrementId)
    {
        $orderItemData = [];

        /** @var Order $order */
        $order = $this->getOrderInfo($incrementId);
        $storeId = $order->getStoreId();
//        $orderTotal = round($order->getSubtotalInclTax() + $order->getDiscountAmount());
        $orderTotal = round($order->getSubtotalInclTax() + $order->getDiscountAmount() + $order->getShippingAmount());
        $invoice = $this->getInvoice($order->getEntityId());
        $mileageUsedAmount = $order->getRewardPointsBalance();

        if ($order == null) {
            throw new NoSuchEntityException(
                __("Such order does not exist. Check the data and try again")
            );
        }

        if ($invoice != null) {

            $orderItems = $order->getAllItems();

            $cnt = 1;
            /** @var \Magento\Sales\Model\Order\Item $orderItem */
            foreach ($orderItems as $orderItem) {
                if ($orderItem->getProductType() != 'simple') {
                    continue;
                }
                $configurableCheckedItem = $this->productTypeCheck($orderItem);
                $mileagePerItem = $this->mileageSpentRateByItem(
                    $orderTotal,
                    $configurableCheckedItem->getRowTotalInclTax(),
                    $configurableCheckedItem->getDiscountAmount(),
                    $mileageUsedAmount);
                $itemGrandTotal = $configurableCheckedItem->getRowTotal()
                    - $configurableCheckedItem->getDiscountAmount()
                    - $mileagePerItem;
                $itemGrandTotalInclTax = $this->productTypeCheck($orderItem)->getRowTotalInclTax()
                    - $this->productTypeCheck($orderItem)->getDiscountAmount()
                    - $mileagePerItem;

                $product = $this->productRepository->getById($orderItem->getProductId());
                $meins = $product->getData('meins');

                $orderItemData[] = [
                    'itemVkorg' => $this->config->getSalesOrg('store', $storeId),
                    'itemKunnr' => $this->config->getClient('store', $storeId),
                    'itemOdrno' => $order->getIncrementId(),
                    'itemPosnr' => $cnt,
                    'itemMatnr' => $orderItem->getSku(),
                    'itemMenge' => intval($orderItem->getQtyOrdered()),
                    // 아이템 단위, Default : EA
                    'itemMeins' => $this->getMeins($meins),
                    'itemNsamt' => $configurableCheckedItem->getRowTotalInclTax(),
                    'itemDcamt' => $configurableCheckedItem->getDiscountAmount(),
                    'itemSlamt' => $itemGrandTotalInclTax,
                    'itemMiamt' => $mileagePerItem,
                    // 상품이 무상제공인 경우 Y 아니면 N
                    'itemFgflg' => $orderItem->getPrice() == 0 ? 'Y' : 'N',
                    'itemMilfg' => empty($mileageUsedAmount) ? 'N' : 'Y',
                    'itemAuart' => self::NORMAL_ORDER,
                    'itemAugru' => '',
                    'itemNetwr' => $itemGrandTotal,
                    'itemMwsbp' => $configurableCheckedItem->getTaxAmount(),
                    'itemVkorgOri' => $this->config->getSalesOrg('store', $storeId),
                    'itemKunnrOri' => $this->config->getClient('store', $storeId),
                    'itemOdrnoOri' => $order->getIncrementId(),
                    'itemPosnrOri' => $cnt
                ];
                $cnt++;
            }
        }
        return $orderItemData;
    }

    public function getMeins($value)
    {
        try {
            $attribute = $this->eavAttributeRepositoryInterface->get('catalog_product', 'meins');
            $options = $attribute->getOptions();

            $label = 'EA';
            foreach ($options as $option) {
                if ($option->getValue() == $value) {
                    $label = $option->getLabel();
                }
            }
            return $label;
        } catch (\Exception $exception) {
            return null;
        }
    }

    /**
     * @param $orderItem \Magento\Sales\Model\Order\Item
     */
    public function productTypeCheck($orderItem)
    {
        if (empty($orderItem->getParentItem())) {
            return $orderItem;
        } elseif ($orderItem->getParentItem()->getProductType() == 'bundle') {
            return $orderItem;
        } elseif ($orderItem->getParentItem()->getProductType() == 'configurable') {
            return $orderItem->getParentItem();
        } else {
            return $orderItem;
        }
    }

    public function mileageSpentRateByItem($orderTotal, $itemRowTotal, $itemDiscountAmount, $mileageUsed)
    {
        $itemTotal = round($itemRowTotal - $itemDiscountAmount, 2);

        if ($mileageUsed) {
            return round(($itemTotal/$orderTotal) * $mileageUsed);
        }
        return is_null($mileageUsed) ? '0' : $mileageUsed;
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

    public function getTestOrderConfirm()
    {
        $testOrderValue = $this->config->getDefaultValue('sap/order_confirm_test/confirm_order_test');
        $testItemValue = $this->config->getDefaultValue('sap/order_confirm_test/confirm_order_item_test');
        $arrayTestOrderValue = explode(",", $testOrderValue);
        $arrayTestItemValue = explode(",",$testItemValue);

        $request = [
            "request" => [
                "header" => [
                    "source" => $this->config->getSourceByStore('default', null)
                ],
                "input" => [
                    "itHead" => $this->getTestOrderData($arrayTestOrderValue),
                    'itItem' => $this->getTestOrderItemData($arrayTestOrderValue, $arrayTestItemValue)
                ]
            ]
        ];

        return $request;
    }

    public function getTestOrderData($testOrderData)
    {
        $bindData[] = [
            'vkorg' => $testOrderData[0],
            'kunnr' => $testOrderData[1],
            'odrno' => $testOrderData[2],
            'odrdt' => $testOrderData[3],
            'odrtm' => $testOrderData[4],
            'paymtd' => $testOrderData[5],
            'payde' => $testOrderData[6],
            'paytm' => $testOrderData[7],
            'auart' => self::NORMAL_ORDER,
            'augru' => '',
            'augruText' => '',
            'custid' => $testOrderData[8],
            'custnm' => 'Test Customer Name',
            'recvid' => $testOrderData[8],
            'recvnm' => 'Test Receiver Name',
            'postCode' => "300",
            'addr1' => '新竹市',
            'addr2' => '北區',
            'addr3' => 'test street',
            'land1' => 'TW',
            'telno' => '0911112222',
            'hpno' => '',
            'waerk' => 'TWD',
            'nsamt' => $testOrderData[9],
            'dcamt' => $testOrderData[10],
            'slamt' => $testOrderData[11],
            'miamt' => $testOrderData[12],
            'shpwr' => $testOrderData[13],
            'mwsbp' => $testOrderData[14],
            'spitn1' => '',
            'vkorgOri' => $testOrderData[0],
            'kunnrOri' => $testOrderData[1],
            'odrnoOri' => $testOrderData[2],
            'itemCnt' => "1",
            'werks' => '',
            'lgort' => '',
            'rmano' => '',
            'kunwe' => $testOrderData[15],
            'ztrackId' => $testOrderData[16]
        ];
        return $bindData;
    }

    public function getTestOrderItemData($testOrderData, $testItemData)
    {
        $orderItemData[] = [
            'itemVkorg' => $testOrderData[0],
            'itemKunnr' => $testOrderData[1],
            'itemOdrno' => $testOrderData[2],
            'itemPosnr' => "1",
            'itemMatnr' => $testItemData[0],
            'itemMenge' => $testItemData[1],
            'itemMeins' => 'EA',
            'itemNsamt' => $testItemData[2],
            'itemDcamt' => $testItemData[3],
            'itemSlamt' => $testItemData[4],
            'itemMiamt' => $testItemData[5],
            'itemFgflg' => $testItemData[6],
            'itemMilfg' => $testItemData[7],
            'itemAuart' => self::NORMAL_ORDER,
            'itemAugru' => '',
            'itemNetwr' => $testItemData[8],
            'itemMwsbp' => $testItemData[9],
            'itemVkorgOri' => $testOrderData[0],
            'itemKunnrOri' => $testOrderData[1],
            'itemOdrnoOri' => $testOrderData[2],
            'itemPosnrOri' => "1"
        ];
        return $orderItemData;
    }
}
