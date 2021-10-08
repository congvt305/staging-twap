<?php
/**
 * Created by Eguana.
 * User: Brian
 * Date: 2020-06-05
 * Time: 오후 1:22
 */

namespace Amore\Sap\Model\SapOrder;

use Amore\Sap\Exception\ShipmentNotExistException;
use Amore\Sap\Logger\Logger;
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
use Magento\Sales\Model\Order\Item;
use Magento\Store\Api\StoreRepositoryInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Store\Model\StoreManagerInterface;

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
     * @var \Magento\Bundle\Api\ProductLinkManagementInterface
     */
    private $productLinkManagement;
    /**
     * @var \Magento\Sales\Api\OrderItemRepositoryInterface
     */
    private $orderItemRepository;

    /**
     * @var Logger
     */
    private $logger;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

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
     * @param \Magento\Bundle\Api\ProductLinkManagementInterface $productLinkManagement
     * @param \Magento\Sales\Api\OrderItemRepositoryInterface $orderItemRepository
     * @param Logger $logger
     * @param StoreManagerInterface $storeManager
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
        AttributeRepositoryInterface $eavAttributeRepositoryInterface,
        \Magento\Bundle\Api\ProductLinkManagementInterface $productLinkManagement,
        \Magento\Sales\Api\OrderItemRepositoryInterface $orderItemRepository,
        Logger $logger,
        StoreManagerInterface $storeManager
    ) {
        parent::__construct($searchCriteriaBuilder, $orderRepository, $storeRepository, $config);
        $this->invoiceRepository = $invoiceRepository;
        $this->rmaRepository = $rmaRepository;
        $this->customerRepository = $customerRepository;
        $this->timezoneInterface = $timezoneInterface;
        $this->quoteCvsLocationRepository = $quoteCvsLocationRepository;
        $this->productRepository = $productRepository;
        $this->eavAttributeRepositoryInterface = $eavAttributeRepositoryInterface;
        $this->productLinkManagement = $productLinkManagement;
        $this->orderItemRepository = $orderItemRepository;
        $this->logger = $logger;
        $this->storeManager = $storeManager;
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

        if (is_null($order)) {
            throw new NoSuchEntityException(__("Available order data does not exist."));
        }

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
            ->addFilter('status', ['processing', 'sap_fail', 'processing_with_shipment'], 'in')
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

        if ($orderData == null) {
            $exception = new NoSuchEntityException(
                __("Such order %1 does not exist. Check the data and try again", $incrementId)
            );
            $this->logger->log('INFO', $exception->getMessage(), [
                'order_id' => $incrementId,
                'error_trace' => $exception->getTraceAsString()
            ]);
            throw $exception;
        }

        $invoice = $this->getInvoice($orderData->getEntityId());
        $storeId = $orderData->getStoreId();
        $shippingMethod = $orderData->getShippingMethod();
        $trackingNumbers = implode(",", $this->getTrackNumber($orderData));
        $bindData = [];

        if ($shippingMethod == 'gwlogistics_CVS' && !$orderData->hasShipments()) {
            throw new ShipmentNotExistException(
                __("Order %1 is CVS shipping and shipment does not Exist. Please create shipment and try again.", $incrementId)
            );
        }

        if ($shippingMethod == 'gwlogistics_CVS' && empty($trackingNumbers)) {
            throw new ShipmentNotExistException(
                __("Order %1 is CVS shipping and does not have Tracking numbers. Please create track and try again.", $incrementId)
            );
        }

        if ($invoice != null) {
            $shippingAddress = $orderData->getShippingAddress();
            $customer = $this->getCustomerByOrder($orderData);

            $paymtd = $this->getPaymentCode($orderData->getPayment()->getMethod());
            $websiteId = (int)$this->storeManager->getStore($storeId)->getWebsiteId();
            $websiteCode = $this->storeManager->getWebsite($websiteId)->getCode();
            $paymtd = ($websiteCode != 'vn_laneige_website') ? $paymtd : 10;

            $orderSubTotal = abs(round($orderData->getSubtotalInclTax() + $this->getBundleExtraAmount($orderData) + $this->getCatalogRuleDiscountAmount($orderData)));
            $orderGrandTotal = $orderData->getGrandTotal() == 0 ? $orderData->getGrandTotal() : abs(round($orderData->getGrandTotal() - $orderData->getShippingAmount()));

            $bindData[] = [
                'vkorg' => $this->config->getSalesOrg('store', $storeId),
                'kunnr' => $this->config->getClient('store', $storeId),
                'odrno' => $orderData->getIncrementId(),
                'odrdt' => $this->dateFormatting($orderData->getCreatedAt(), 'Ymd'),
                'odrtm' => $this->dateFormatting($orderData->getCreatedAt(), 'His'),
                'paymtd' => $paymtd,
                'paydt' => $this->dateFormatting($invoice->getCreatedAt(), 'Ymd'),
                'paytm' => $this->dateFormatting($invoice->getCreatedAt(), 'His'),
                // added for VN start
                'payMode' => $orderData->getPayment()->getMethod() === 'cashondelivery' ? 'COD' : '', //todo need to create payment method cod
                'dhlId' => $orderData->getShippingMethod() === 'eguanadhl_tablerate' ? 'TBD' : '', //todo need to make configuration for this
                'shpSvccd' => $orderData->getShippingMethod() === 'eguanadhl_tablerate' ? 'PDE' : '',
                'ordWgt' => $orderData->getShippingMethod() === 'eguanadhl_tablerate' ? '1000' : '',
                'insurance' => $orderData->getShippingMethod() === 'eguanadhl_tablerate' ? 'Y' : '',
                'insurnaceValue' => $orderData->getShippingMethod() === 'eguanadhl_tablerate' ? $orderGrandTotal : null, //todo null is okay?
                // added for VN start end
                'auart' => self::NORMAL_ORDER,
                'augru' => '',
                'augruText' => 'ORDER REASON TEXT',
                // 주문자회원코드-직영몰자체코드
                'custid' => $customer != '' ? $orderData->getCustomerId() : '',
                'custnm' => $orderData->getCustomerLastname() . $orderData->getCustomerFirstname(),
                //배송지 id - 직영몰 자체코드, 없으면 공백
                'recvid' => '',
                'recvnm' => $shippingAddress->getLastname() . ' ' . $shippingAddress->getFirstname(),
                'postCode' => $this->cvsShippingCheck($orderData) ? '00000' : $shippingAddress->getPostcode(),
                'addr1' => $this->cvsShippingCheck($orderData) ? $this->getCsvAddress($shippingAddress) : $shippingAddress->getRegion(),
                'addr2' => $this->cvsShippingCheck($orderData) ? '.' : $shippingAddress->getCity(),
                'addr3' => $this->cvsShippingCheck($orderData) ? '.' : preg_replace('/\r\n|\r|\n/', ' ', implode(PHP_EOL, $shippingAddress->getStreet())),
                'land1' => $shippingAddress->getCountryId(),
                'telno' => $this->getTelephone($shippingAddress->getTelephone()),
                'hpno' => $this->getTelephone($shippingAddress->getTelephone()),
                'waerk' => $orderData->getOrderCurrencyCode(),
                'nsamt' => $orderSubTotal,
                'dcamt' => $this->getOrderDiscountAmount($orderData, $orderSubTotal, $orderGrandTotal),
                'slamt' => $orderGrandTotal,
                'miamt' => is_null($orderData->getRewardPointsBalance()) ? '0' : round($orderData->getRewardPointsBalance()),
                'shpwr' => round($orderData->getShippingAmount()),
                'mwsbp' => round($orderData->getTaxAmount()),
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

    /**
     * @param \Magento\Sales\Model\Order $order
     * @param $orderSubtotal
     * @param $orderGrandTotal
     * @throws NoSuchEntityException
     */
    public function getOrderDiscountAmount($order, $orderSubtotal, $orderGrandTotal)
    {
        $differenceBtwSubAndGrand = $orderSubtotal - $orderGrandTotal;

        $orderDiscountAmount = abs(round(abs($order->getDiscountAmount()) + $this->getBundleExtraAmount($order) + $this->getCatalogRuleDiscountAmount($order)));

        if ($differenceBtwSubAndGrand != $orderDiscountAmount) {
            $orderDiscountAmount = $differenceBtwSubAndGrand;
        }
        return $orderDiscountAmount;
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
     * @param $incrementId
     * @return mixed
     * @throws NoSuchEntityException
     * @throws \Exception
     */
    public function getOrderItem($incrementId)
    {
        $orderItemData = [];

        /** @var Order $order */
        $order = $this->getOrderInfo($incrementId);
        $storeId = $order->getStoreId();
        $orderTotal = round($order->getSubtotalInclTax() + $order->getDiscountAmount() + $order->getShippingAmount());
        $invoice = $this->getInvoice($order->getEntityId());
        $mileageUsedAmount = is_null($order->getRewardPointsBalance()) ? '0' : $order->getRewardPointsBalance();

        if ($order == null) {
            throw new NoSuchEntityException(
                __("Such order does not exist. Check the data and try again")
            );
        }

        $itemsSubtotal = 0;
        $itemsDiscountAmount = 0;
        $itemsGrandTotal = 0;
        $itemsGrandTotalInclTax = 0;
        $itemsMileage = 0;

        if ($invoice != null) {

            $orderItems = $order->getAllVisibleItems();
            $orderAllItems = $order->getAllItems();
            $skuPrefix = $this->config->getSapSkuPrefix($storeId);
            $skuPrefix = $skuPrefix ?: '';

            $cnt = 1;
            /** @var Item $orderItem */
            foreach ($orderItems as $orderItem) {
                if ($orderItem->getProductType() != 'bundle') {
                    $mileagePerItem = $this->mileageSpentRateByItem(
                        $orderTotal,
                        $orderItem->getRowTotalInclTax(),
                        $orderItem->getDiscountAmount(),
                        $mileageUsedAmount);
                    $itemGrandTotal = $orderItem->getRowTotal()
                        - $orderItem->getDiscountAmount()
                        - $mileagePerItem;
                    $itemGrandTotalInclTax = $orderItem->getRowTotalInclTax()
                        - $orderItem->getDiscountAmount()
                        - $mileagePerItem;
                    $itemSubtotal = abs(round($orderItem->getOriginalPrice() * $orderItem->getQtyOrdered()));
                    $itemTotalDiscount = abs(round($orderItem->getDiscountAmount() + (($orderItem->getOriginalPrice() - $orderItem->getPrice()) * $orderItem->getQtyOrdered())));
                    $itemSaleAmount = $itemSubtotal - $itemTotalDiscount - abs(round($mileagePerItem));
                    $itemTaxAmount = abs(round($orderItem->getTaxAmount()));

                    $product = $this->productRepository->getById($orderItem->getProductId());
                    $meins = $product->getData('meins');

                    $sku = str_replace($skuPrefix, '', $orderItem->getSku());

                    $orderItemData[] = [
                        'itemVkorg' => $this->config->getSalesOrg('store', $storeId),
                        'itemKunnr' => $this->config->getClient('store', $storeId),
                        'itemOdrno' => $order->getIncrementId(),
                        'itemPosnr' => $cnt,
                        'itemMatnr' => $sku,
                        'itemMenge' => intval($orderItem->getQtyOrdered()),
                        // 아이템 단위, Default : EA
                        'itemMeins' => $this->getMeins($meins),
                        'itemNsamt' => $itemSubtotal,
                        'itemDcamt' => $itemTotalDiscount,
                        'itemSlamt' => $itemSaleAmount,
                        'itemMiamt' => abs(round($mileagePerItem)),
                        // 상품이 무상제공인 경우 Y 아니면 N
                        'itemFgflg' => $itemSaleAmount == 0 ? 'Y' : 'N',
                        'itemMilfg' => empty($mileageUsedAmount) ? 'N' : 'Y',
                        'itemAuart' => self::NORMAL_ORDER,
                        'itemAugru' => '',
                        'itemNetwr' => $itemSubtotal - $itemTotalDiscount - round($mileagePerItem) - $itemTaxAmount,
                        'itemMwsbp' => $itemTaxAmount,
                        'itemVkorgOri' => $this->config->getSalesOrg('store', $storeId),
                        'itemKunnrOri' => $this->config->getClient('store', $storeId),
                        'itemOdrnoOri' => $order->getIncrementId(),
                        'itemPosnrOri' => $cnt,
                        'itemId' => $orderItem->getItemId()
                    ];

                    $cnt++;
                    $itemsSubtotal += round($orderItem->getOriginalPrice() * $orderItem->getQtyOrdered());
                    $itemsGrandTotal += ($itemSubtotal - $itemTotalDiscount - abs(round($mileagePerItem)));
                    $itemsGrandTotalInclTax += ($itemSubtotal - $itemTotalDiscount - round($mileagePerItem) - $itemTaxAmount);
                    $itemsDiscountAmount += round($orderItem->getDiscountAmount() + (($orderItem->getOriginalPrice() - $orderItem->getPrice()) * $orderItem->getQtyOrdered()));
                    $itemsMileage += round($mileagePerItem);
                } else {
                    /** @var \Magento\Catalog\Model\Product $bundleProduct */
                    $bundleProduct = $this->productRepository->getById($orderItem->getProductId());
                    $bundleChildren = $this->getBundleChildren($orderItem->getSku(), $orderItem->getStoreId());
                    $bundlePriceType = $bundleProduct->getPriceType();

                    foreach ($bundleChildren as $bundleChild) {
                        $itemId = $orderItem->getItemId();
                        $bundleChildFromOrder = $this->getBundleChildFromOrder($itemId, $bundleChild->getSku());
                        if ((int)$bundlePriceType !== \Magento\Bundle\Model\Product\Price::PRICE_TYPE_DYNAMIC) {
                            $bundleChildPrice = $this->productRepository->get($bundleChild->getSku(), false, $order->getStoreId())->getPrice();
                        } else {
                            $bundleChildPrice = $bundleChildFromOrder->getOriginalPrice();
                        }

                        $bundleChildDiscountAmount = (int)$bundlePriceType !== \Magento\Bundle\Model\Product\Price::PRICE_TYPE_DYNAMIC ?
                            round($this->getProportionOfBundleChild($orderItem, $bundleChild, $orderItem->getDiscountAmount())) :
                            round($bundleChildFromOrder->getDiscountAmount());
                        $mileagePerItem = $this->mileageSpentRateByItem(
                            $orderTotal,
                            $this->getProportionOfBundleChild($orderItem, $bundleChild, $orderItem->getRowTotalInclTax()),
                            $bundleChildDiscountAmount,
                            $mileageUsedAmount);
                        $itemGrandTotal = $this->getProportionOfBundleChild($orderItem, $bundleChild, $orderItem->getRowTotal())
                            - $bundleChildDiscountAmount
                            - $mileagePerItem;
                        $itemGrandTotalInclTax = $this->getProportionOfBundleChild($orderItem, $bundleChild, $orderItem->getRowTotalInclTax())
                            - $bundleChildDiscountAmount
                            - $mileagePerItem;

                        $product = $this->productRepository->get($bundleChild->getSku(), false, $order->getStoreId());
                        $meins = $product->getData('meins');

                        $childPriceRatio = $this->getProportionOfBundleChild($orderItem, $bundleChild, $orderItem->getOriginalPrice()) / $bundleChild->getQty();
                        $catalogRuledPriceRatio = $this->getProportionOfBundleChild($orderItem, $bundleChild, ($orderItem->getOriginalPrice() - $orderItem->getPrice())) / $bundleChild->getQty();

                        $itemSubtotal = abs(round($bundleChildPrice * $bundleChildFromOrder->getQtyOrdered()));
                        $itemTotalDiscount = abs(round(
                            $bundleChildDiscountAmount +
                            (($product->getPrice() - $childPriceRatio) * $bundleChildFromOrder->getQtyOrdered()) +
                            $catalogRuledPriceRatio * $bundleChildFromOrder->getQtyOrdered())
                        );
                        $itemTaxAmount = abs(round($this->getProportionOfBundleChild($orderItem, $bundleChild, $orderItem->getTaxAmount())));

                        $sku = str_replace($skuPrefix, '', $bundleChild->getSku());
                        $item = $this->searchOrderItem($orderAllItems, $bundleChild->getSku(), $itemId);

                        $orderItemData[] = [
                            'itemVkorg' => $this->config->getSalesOrg('store', $storeId),
                            'itemKunnr' => $this->config->getClient('store', $storeId),
                            'itemOdrno' => $order->getIncrementId(),
                            'itemPosnr' => $cnt,
                            'itemMatnr' => $sku,
                            'itemMenge' => intval($bundleChildFromOrder->getQtyOrdered()),
                            // 아이템 단위, Default : EA
                            'itemMeins' => $this->getMeins($meins),
                            'itemNsamt' => $itemSubtotal,
                            'itemDcamt' => $itemTotalDiscount,
                            'itemSlamt' => $itemSubtotal - $itemTotalDiscount - abs(round($mileagePerItem)),
                            'itemMiamt' => abs(round($mileagePerItem)),
                            // 상품이 무상제공인 경우 Y 아니면 N
                            'itemFgflg' => $product->getPrice() == 0 ? 'Y' : 'N',
                            'itemMilfg' => empty($mileageUsedAmount) ? 'N' : 'Y',
                            'itemAuart' => self::NORMAL_ORDER,
                            'itemAugru' => '',
                            'itemNetwr' => $itemSubtotal - $itemTotalDiscount - round($mileagePerItem) - $itemTaxAmount,
                            'itemMwsbp' => $itemTaxAmount,
                            'itemVkorgOri' => $this->config->getSalesOrg('store', $storeId),
                            'itemKunnrOri' => $this->config->getClient('store', $storeId),
                            'itemOdrnoOri' => $order->getIncrementId(),
                            'itemPosnrOri' => $cnt,
                            'itemId' => $item->getItemId()
                        ];
                        $cnt++;
                        $itemsSubtotal += $itemSubtotal;
                        $itemsGrandTotalInclTax += ($itemSubtotal - $itemTotalDiscount - round($mileagePerItem) - $itemTaxAmount);
                        $itemsGrandTotal += ($itemSubtotal - $itemTotalDiscount - abs(round($mileagePerItem)));
                        $itemsDiscountAmount += $itemTotalDiscount;

                        $itemsMileage += round($mileagePerItem);
                    }
                }
            }
        }

        $orderSubtotal = round($order->getSubtotalInclTax() + $this->getBundleExtraAmount($order) + $this->getCatalogRuleDiscountAmount($order));
        $orderGrandtotal = $order->getGrandTotal() == 0 ? $order->getGrandTotal() : round($order->getGrandTotal() - $order->getShippingAmount());
        $orderDiscountAmount = $this->getOrderDiscountAmount($order, $orderSubtotal, $orderGrandtotal);

        $orderItemData = $this->priceCorrector($orderSubtotal, $itemsSubtotal, $orderItemData, 'itemNsamt');
        $orderItemData = $this->priceCorrector($orderGrandtotal, $itemsGrandTotalInclTax, $orderItemData, 'itemSlamt');
        $orderItemData = $this->priceCorrector($orderDiscountAmount, $itemsDiscountAmount, $orderItemData, 'itemDcamt');
        $orderItemData = $this->priceCorrector($mileageUsedAmount, $itemsMileage, $orderItemData, 'itemMiamt');
        $orderItemData = $this->priceCorrector($orderGrandtotal, $itemsGrandTotal, $orderItemData, 'itemNetwr');

        return $orderItemData;
    }

    public function getBundleChildFromOrder($itemId, $bundleChildSku)
    {
        $bundleChild = null;
        /** @var Item $itemOrdered */
        $itemOrdered = $this->orderItemRepository->get($itemId);
        $childrenItems = $itemOrdered->getChildrenItems();
        /** @var Item $childItem */
        foreach ($childrenItems as $childItem) {
            if ($childItem->getSku() == $bundleChildSku) {
                $bundleChild = $childItem;
                break;
            }
        }
        return $bundleChild;
    }

    /**
     * @param $order \Magento\Sales\Model\Order
     * @throws NoSuchEntityException
     * @throws \Exception
     */
    public function getBundleExtraAmount($order)
    {
        $orderItems = $order->getAllVisibleItems();
        $priceDifferences = 0;

        /** @var Item $orderItem */
        foreach ($orderItems as $orderItem) {
            if ($orderItem->getProductType() == 'bundle') {
                /** @var \Magento\Catalog\Model\Product $bundleProduct */
                $bundleProduct = $this->productRepository->getById($orderItem->getProductId(), false, $order->getStoreId());
                $bundleChildren = $this->getBundleChildren($orderItem->getSku(), $orderItem->getStoreId());
                $bundlePriceType = $bundleProduct->getPriceType();

                if ((int)$bundlePriceType !== \Magento\Bundle\Model\Product\Price::PRICE_TYPE_DYNAMIC) {
                    foreach ($bundleChildren as $bundleChild) {
                        $childPriceRatio = $this->getProportionOfBundleChild($orderItem, $bundleChild, $orderItem->getOriginalPrice()) / $bundleChild->getQty();
                        $originItemPrice = $this->productRepository->get($bundleChild->getSku(), false, $order->getStoreId())->getPrice();
                        $bundleChildByOrder = $this->getBundleChildFromOrder($orderItem->getItemId(), $bundleChild->getSku());

                        $priceDifferences += (($originItemPrice - $childPriceRatio) * $bundleChildByOrder->getQtyOrdered());
                    }
                }
            }
        }
        return $priceDifferences;
    }

    /**
     * @param $order \Magento\Sales\Model\Order
     * @throws NoSuchEntityException
     * @throws \Exception
     */
    public function getCatalogRuleDiscountAmount($order)
    {
        $catalogRuleDiscount = 0;
        $orderItems = $order->getAllVisibleItems();
        /** @var \Magento\Sales\Model\Order\Item $orderItem */
        foreach ($orderItems as $orderItem) {
           if ($orderItem->getProductType() != 'bundle') {
               $catalogRuleDiscount += ($orderItem->getOriginalPrice() - $orderItem->getPrice()) * $orderItem->getQtyOrdered();
           } else {
               /** @var \Magento\Catalog\Model\Product $bundleProduct */
               $bundleProduct = $this->productRepository->getById($orderItem->getProductId(), false, $order->getStoreId());
               $bundleChildren = $this->getBundleChildren($orderItem->getSku(), $orderItem->getStoreId());
               $bundlePriceType = $bundleProduct->getPriceType();

               if ((int)$bundlePriceType !== \Magento\Bundle\Model\Product\Price::PRICE_TYPE_DYNAMIC) {
                   foreach ($bundleChildren as $bundleChild) {
                       $bundleChildByOrder = $this->getBundleChildFromOrder($orderItem->getItemId(), $bundleChild->getSku());
                       $catalogRuledPriceRatio = $this->getProportionOfBundleChild($orderItem, $bundleChild, ($orderItem->getOriginalPrice() - $orderItem->getPrice())) / $bundleChild->getQty();

                       $catalogRuleDiscount += $catalogRuledPriceRatio * $bundleChildByOrder->getQtyOrdered();
                   }
               }
           }
        }
        return $catalogRuleDiscount;
    }

    public function priceCorrector($orderAmount, $itemsAmount, $orderItemData, $field)
    {
        if ($orderAmount != $itemsAmount) {
            $correctAmount = $orderAmount - $itemsAmount;

            foreach ($orderItemData as $key => $value) {
                if ($value['itemFgflg'] == 'Y') {
                    continue;
                }
                $orderItemData[$key][$field] = $value[$field] + $correctAmount;
                break;
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

    public function getBundleChildren($bundleDynamicSku, $storeId = 0)
    {
        $skuPrefix = $this->config->getSapSkuPrefix($storeId);
        $skuPrefix = $skuPrefix ?: '';
        $bundleSku = explode("-", $bundleDynamicSku);
        if ($skuPrefix && strpos($skuPrefix, '-') !== false) {
            $bundleSku = $skuPrefix . $bundleSku[1];
        } else {
            $bundleSku = $bundleSku[0];
        }

        try {
            return $this->productLinkManagement->getChildren($bundleSku);
        } catch (\Exception $exception) {
            throw new \Exception($exception->getMessage());
        }
    }

    /**
     * @param Item $orderItem
     * @param \Magento\Bundle\Api\Data\LinkInterface $bundleChild
     * @param float $valueToCalculate
     * @return float|int
     * @throws NoSuchEntityException
     */
    public function getProportionOfBundleChild($orderItem, $bundleChild, $valueToCalculate)
    {
        $originalPriceSum = $this->getSumOfChildrenOriginPrice($orderItem);

        $bundleChildPrice = $this->productRepository->get($bundleChild->getSku(), false, $orderItem->getStoreId())->getPrice();
        $rate = ($bundleChildPrice / $originalPriceSum) * $bundleChild->getQty();

        return $valueToCalculate * $rate;
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

    /**
     * @param Item $orderItem
     * @return float|null
     * @throws NoSuchEntityException
     * @throws \Exception
     */
    public function getSumOfChildrenOriginPrice(Item $orderItem)
    {
        $originalPriceSum = 0;

        $childrenItems = $this->getBundleChildren($orderItem->getSku(), $orderItem->getStoreId());

        /** @var \Magento\Bundle\Api\Data\LinkInterface $childItem */
        foreach ($childrenItems as $childItem) {
            $originalProductPrice = $this->productRepository->get($childItem->getSku(), false, $orderItem->getStoreId())->getPrice();
            $originalPriceSum += ($originalProductPrice * $childItem->getQty());
        }
        return $originalPriceSum;
    }

    /**
     * Return bundle child item from all items by sku and parent item id
     *
     * @param $orderItems
     * @param $childSku
     * @param $parentItemId
     * @return array|Item
     */
    private function searchOrderItem($orderItems, $childSku, $parentItemId)
    {
        /** @var Item $item */
        foreach ($orderItems as $item) {
            if ($item->getSku() == $childSku && $item->getParentItemId() == $parentItemId) {
                return $item;
            }
        }
        return [];
    }

    /**
     * Set order data for return order
     *
     * @param $orderData
     * @param Order $order
     */
    public function setReturnOrderData($orderData, $order)
    {
        try {
            $order->setData('sap_nsamt', $orderData['nsamt']);
            $order->setData('sap_dcamt', $orderData['dcamt']);
            $order->setData('sap_slamt', $orderData['slamt']);
        } catch (\Exception $exception) {
            $this->logger->info('===== Error While Setting Order Data for Return =====');
            $this->logger->info($exception->getMessage());
        }
    }

    /**
     * Set order item data for return order
     *
     * @param $itemsData
     * @param Order $order
     */
    public function setReturnItemOrderData($itemsData, $order)
    {
        try {
            $orderAllItems = $order->getAllItems();
            foreach ($orderAllItems as $item) {
                $key = array_search($item->getItemId(), array_column($itemsData, 'itemId'));
                if ($key !== false) {
                    $item->setData('sap_item_nsamt', $itemsData[$key]['itemNsamt']);
                    $item->setData('sap_item_dcamt', $itemsData[$key]['itemDcamt']);
                    $item->setData('sap_item_slamt', $itemsData[$key]['itemSlamt']);
                    $item->setData('sap_item_netwr', $itemsData[$key]['itemNetwr']);
                }
            }
            $size = count($itemsData);
            for ($i = 0; $i < $size; $i++) {
                unset($itemsData[$i]['itemId']);
            }
        } catch (\Exception $exception) {
            $this->logger->info('===== Error While Setting Order Item Data for Return =====');
            $this->logger->info($exception->getMessage());
        }
        return $itemsData;
    }
}
