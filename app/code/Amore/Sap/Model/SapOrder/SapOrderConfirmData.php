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
use Amore\Sap\Model\SapOrder\Product\Bundle\CalculatePrice;
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
use Eguana\Directory\Helper\Data;

class SapOrderConfirmData extends AbstractSapOrder
{
    const ORDER_SENT_TO_SAP_BEFORE = 0;

    const ORDER_SENT_TO_SAP_SUCCESS = 1;

    const ORDER_SENT_TO_SAP_FAIL = 2;

    const ORDER_RESENT_TO_SAP_SUCCESS = 3;

    private $cnt = 1;

    private $_orderItemData = [];

    private $_itemsSubtotal = 0;

    private $_itemsGrandTotalInclTax = 0;

    private $_itemsGrandTotal = 0;

    private $_itemsDiscountAmount = 0;

    private $_itemsMileage = 0;

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
     * @var \Magento\Catalog\Api\ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * @var \Magento\Bundle\Api\ProductLinkManagementInterface
     */
    private $productLinkManagement;
    /**
     * @var \Magento\Sales\Api\OrderItemRepositoryInterface
     */
    private $orderItemRepository;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var Data
     */
    protected $dataHelper;

    /**
     * @var \CJ\Middleware\Helper\Data
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
     * @var CalculatePrice
     */
    private $bundleCalculatePrice;

    /**
     * @var Product\CalculatePrice
     */
    private $productCalculatePrice;

    /**
     * @var \CJ\Middleware\Model\Data
     */
    private $orderData;

    /**
     * SapOrderConfirmData constructor.
     *
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
     * @param Data $helper
     * @param \CJ\Middleware\Helper\Data $middlewareHelper
     * @param \Amasty\Rewards\Model\Config $amConfig
     */
    public function __construct(
        SearchCriteriaBuilder $searchCriteriaBuilder,
        OrderRepositoryInterface $orderRepository,
        StoreRepositoryInterface $storeRepository,
        Config $config,
        InvoiceRepositoryInterface $invoiceRepository,
        CustomerRepositoryInterface $customerRepository,
        TimezoneInterface $timezoneInterface,
        QuoteCvsLocationRepository $quoteCvsLocationRepository,
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
        AttributeRepositoryInterface $eavAttributeRepositoryInterface,
        Logger $logger,
        StoreManagerInterface $storeManager,
        Data $helper,
        \CJ\Middleware\Helper\Data $middlewareHelper,
        \Amasty\Rewards\Model\Config $amConfig,
        \CJ\Rewards\Model\Data $rewardData,
        CalculatePrice $bundleCalculatePrice,
        \Amore\Sap\Model\SapOrder\Product\CalculatePrice $productCalculatePrice,
        \CJ\Middleware\Model\Data $orderData
    ) {
        $this->invoiceRepository = $invoiceRepository;
        $this->customerRepository = $customerRepository;
        $this->timezoneInterface = $timezoneInterface;
        $this->productRepository = $productRepository;
        $this->storeManager = $storeManager;
        $this->dataHelper = $helper;
        $this->middlewareHelper = $middlewareHelper;
        $this->amConfig = $amConfig;
        $this->rewardData = $rewardData;
        $this->bundleCalculatePrice = $bundleCalculatePrice;
        $this->productCalculatePrice = $productCalculatePrice;
        $this->orderData = $orderData;
        parent::__construct(
            $searchCriteriaBuilder, $orderRepository,
            $storeRepository, $config, $quoteCvsLocationRepository,
            $eavAttributeRepositoryInterface, $logger
        );
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
            if ($sampleOrder == null) {
                $exception = new NoSuchEntityException(
                    __("Such order %1 does not exist. Check the data and try again", $incrementId)
                );
                $this->logger->log('INFO', $exception->getMessage(), [
                    'order_id' => $incrementId,
                    'error_trace' => $exception->getTraceAsString()
                ]);
                throw $exception;
            }
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
            $isDecimalFormat = $this->middlewareHelper->getIsDecimalFormat('store', $storeId);

            $nsamt = $this->orderData->roundingPrice($orderData->getSubtotalInclTax(), $isDecimalFormat);
            if ($orderData->getGrandTotal() == 0) {
                $orderGrandTotal = $orderData->getGrandTotal();
            } else {
                $orderGrandTotal = abs($this->orderData->roundingPrice($orderData->getGrandTotal() - $orderData->getShippingAmount(), $isDecimalFormat));
            }
            $mileageUsedAmount = 0;
            if ($this->amConfig->isEnabled($storeId)) {
                $spendingRate = $this->amConfig->getPointsRate($storeId);
                $rewardPoints = 0;
                if ($orderData->getData('am_spent_reward_points')) {
                    $rewardPoints = $this->orderData->roundingPrice($orderData->getData('am_spent_reward_points'), $isDecimalFormat);
                }
                if (!$spendingRate) {
                    $spendingRate = 1;
                }
                if ($this->rewardData->isEnableShowListOptionRewardPoint($storeId)) {
                    $listOptions = $this->rewardData->getListOptionRewardPoint($storeId);
                    $mileageUsedAmount = $listOptions[$rewardPoints] ?? 0;
                } else {
                    $mileageUsedAmount = $rewardPoints / $spendingRate;
                }
            }

            $dcamt = abs($orderData->getDiscountAmount()) - $mileageUsedAmount;
            $slamt = $nsamt - $dcamt;
            $isMileageOrder = ($slamt == $mileageUsedAmount && $slamt > 0);
            $cvsShippingCheck = $this->cvsShippingCheck($orderData);
            $telephone = $this->getTelephone($shippingAddress->getTelephone());
            $salesOrg = $this->config->getSalesOrg('store', $storeId);
            $client = $this->config->getClient('store', $storeId);

            $bindData[] = [
                'vkorg' => $salesOrg,
                'kunnr' => $client,
                'odrno' => $orderData->getIncrementId(),
                'odrdt' => $this->orderData->dateFormatting($orderData->getCreatedAt(), 'Ymd'),
                'odrtm' => $this->orderData->dateFormatting($orderData->getCreatedAt(), 'His'),
                'paymtd' => $paymtd,
                'paydt' => $this->orderData->dateFormatting($invoice->getCreatedAt(), 'Ymd'),
                'paytm' => $this->orderData->dateFormatting($invoice->getCreatedAt(), 'His'),
                // added for VN start
                'payMode' => $orderData->getPayment()->getMethod() === 'cashondelivery' ? 'COD' : '', //todo need to create payment method cod
                'dhlId' => $orderData->getShippingMethod() === 'eguanadhl_tablerate' ? 'TBD' : '', //todo need to make configuration for this
                'shpSvccd' => $orderData->getShippingMethod() === 'eguanadhl_tablerate' ? 'PDE' : '',
                'ordWgt' => $orderData->getShippingMethod() === 'eguanadhl_tablerate' ? '1000' : '',
                'insurance' => $orderData->getShippingMethod() === 'eguanadhl_tablerate' ? 'Y' : '',
                'insurnaceValue' => $orderData->getShippingMethod() === 'eguanadhl_tablerate' ? $orderGrandTotal : null, //todo null is okay?
                // added for VN start end
                'auart' => $isMileageOrder ? self::SAMPLE_ORDER : self::NORMAL_ORDER,
                'augru' => $isMileageOrder ? 'F07' : 'A08',
                'augruText' => 'ORDER REASON TEXT',
                // 주문자회원코드-직영몰자체코드
                'custid' => $customer != '' ? $orderData->getCustomerId() : '',
                'custnm' => $orderData->getCustomerLastname() . $orderData->getCustomerFirstname(),
                //배송지 id - 직영몰 자체코드, 없으면 공백
                'recvid' => '',
                'recvnm' => $shippingAddress->getLastname() . ' ' . $shippingAddress->getFirstname(),
                'postCode' => $cvsShippingCheck ? '00000' : $shippingAddress->getPostcode(),
                'addr1' => $cvsShippingCheck ? $this->getCsvAddress($shippingAddress) : $shippingAddress->getRegion(),
                'addr2' => $cvsShippingCheck ? '.' : $shippingAddress->getCity(),
                'addr3' => $cvsShippingCheck ? '.' : preg_replace('/\r\n|\r|\n/', ' ', implode(PHP_EOL, $shippingAddress->getStreet())),
                'distrid' => $cvsShippingCheck ? '.' : $this->dataHelper->getDistrictCode($shippingAddress->getCityId()),
                'wardid' => $cvsShippingCheck ? '.' : $this->dataHelper->getWardCode($shippingAddress->getWardId()),
                'wardname' => $cvsShippingCheck ? '.' : $shippingAddress->getWard(),
                'land1' => $shippingAddress->getCountryId(),
                'telno' => $telephone,
                'hpno' => $telephone,
                'waerk' => $orderData->getOrderCurrencyCode(),
                'nsamt' => $nsamt,
                'dcamt' => $dcamt,
                'slamt' => $slamt,
                'miamt' => $mileageUsedAmount,
                'shpwr' => $this->orderData->roundingPrice($orderData->getShippingAmount(), $isDecimalFormat),
                'mwsbp' => $this->orderData->roundingPrice($orderData->getTaxAmount(), $isDecimalFormat),
                'spitn1' => $orderData->getDeliveryMessage(),
                'vkorgOri' => $salesOrg,
                'kunnrOri' => $client,
                'odrnoOri' => $orderData->getIncrementId(),
                // 이건 물건 종류 갯수(물건 전체 수량은 아님)
                'itemCnt' => $orderData->getTotalItemCount(),
                // 영업 플랜트 : 알수 없을 경우 공백
                'werks' => '',
                // 영업저장위치 : 알수 없을 경우 공백
                'lgort' => '',
                'rmano' => '',
                // 납품처
                'kunwe' => $this->kunweCheck($orderData),
                'ztrackId' => $trackingNumbers
            ];

            if ($isDecimalFormat) {
                $listToFormat = ['nsamt', 'dcamt', 'slamt', 'miamt', 'shpwr', 'mwsbp'];
                foreach ($bindData[0] as $k => $value) {
                    if (in_array($k, $listToFormat) && (is_float($value) || is_int($value))) {
                        $bindData[0][$k] = $this->orderData->formatPrice($value, $isDecimalFormat);
                    }
                }
            }
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
     * @param $shippingAddress
     * @return string
     * @throws NoSuchEntityException
     */
    public function getCsvAddress($shippingAddress)
    {
        $cvsLocationId = $shippingAddress->getData('cvs_location_id');
        $cvsAddress = '.';
        try {
            $cvsStoreData = $this->quoteCvsLocationRepository->getById($cvsLocationId);
            if ($cvsStoreData && $cvsStoreData->getId()) {
                $cvsAddress = $cvsStoreData->getCvsAddress() . ' ' . $cvsStoreData->getCvsStoreName() . ' ' . $cvsStoreData->getLogisticsSubType();
            }
        } catch (NoSuchEntityException $e) {
            //if order is older than 30days, cvs address might not exists.
        }

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

    /**
     * Get order item data
     *
     * @param string $incrementId
     * @return mixed
     * @throws NoSuchEntityException
     * @throws \Exception
     */
    public function getOrderItem($incrementId)
    {
        /** @var Order $order */
        $order = $this->getOrderInfo($incrementId);
        $storeId = $order->getStoreId();
        $isDecimalFormat = $this->middlewareHelper->getIsDecimalFormat('store', $storeId);
        $orderSubtotal = abs($this->orderData->roundingPrice($order->getSubtotalInclTax(), $isDecimalFormat));
        $invoice = $this->getInvoice($order->getEntityId());
        $mileageUsedAmount = 0;
        $spendingRate = $this->amConfig->getPointsRate($storeId);
        if (!$spendingRate) {
            $spendingRate = 1;
        }
        $mileageUsedAmountExisted = 0;
        if($isEnableRewardsPoint = $this->amConfig->isEnabled($storeId)) {
            $rewardPoints = 0;
            if ($order->getData('am_spent_reward_points')) {
                $rewardPoints = $this->orderData->roundingPrice($order->getData('am_spent_reward_points'), $isDecimalFormat);
            }
            if ($this->rewardData->isEnableShowListOptionRewardPoint($storeId)) {
                $listOptions = $this->rewardData->getListOptionRewardPoint($storeId);
                $mileageUsedAmount = $listOptions[$rewardPoints] ?? 0;
                $spendingRate = $rewardPoints / $mileageUsedAmount;
            } else {
                $mileageUsedAmount = $rewardPoints / $spendingRate;
            }
            $mileageUsedAmountExisted = $mileageUsedAmount;
        }

        if ($order == null) {
            throw new NoSuchEntityException(
                __("Such order does not exist. Check the data and try again")
            );
        }

        if ($invoice != null) {

            $orderItems = $order->getAllVisibleItems();
            /** @var Item $orderItem */
            foreach ($orderItems as $orderItem) {
                if ($orderItem->getProductType() != 'bundle') {
                    $orderItem = $this->productCalculatePrice->calculate($orderItem, $spendingRate, $isEnableRewardsPoint, $isDecimalFormat);

                    $itemMiamt = $orderItem->getData('mileage_amount');
                    $itemNsamt = $orderItem->getData('normal_sales_amount');
                    $itemDcamt = $orderItem->getData('discount_amount');
                    $itemSlamt = $orderItem->getData('sales_amount');
                    $itemNetwr = $orderItem->getData('net_amount');
                    $redemptionFlag = 'N';
                    $rewardPoints = 0;
                    if($isEnableRewardsPoint) {
                        if ($mileageUsedAmountExisted > $itemMiamt) {
                            $mileageUsedAmountExisted -= $itemMiamt;
                        } else {
                            $itemMiamt = $mileageUsedAmountExisted;
                            $mileageUsedAmountExisted = 0;
                        }

                        if ($orderItem->getData('am_spent_reward_points')) {
                            $rewardPoints = $this->orderData->roundingPrice($orderItem->getData('am_spent_reward_points'), $isDecimalFormat);
                        }

                        $discountFromPoints = $rewardPoints / $spendingRate;
                        if ($discountFromPoints == $itemNsamt) {
                            $redemptionFlag = 'Y';
                        }
                    }

                    $this->addOrderItemData(
                        $order, $orderItem, $itemNsamt, $itemDcamt,
                        $itemSlamt, $itemMiamt, $itemNetwr,
                        $orderItem->getTaxAmount(), $redemptionFlag, $rewardPoints
                    );
                } else {
                    $orderItem = $this->bundleCalculatePrice->calculate($orderItem, $spendingRate, $isEnableRewardsPoint, $isDecimalFormat);
                    foreach ($orderItem->getChildrenItems() as $bundleChild) {
                        $itemDcamt = $bundleChild->getDiscountAmount();
                        $itemNsamt = $bundleChild->getData('normal_sales_amount');
                        $itemSlamt = $itemNsamt - $itemDcamt;
                        $itemMiamt = $bundleChild->getData('mileage_amount');
                        $itemTaxAmount = $bundleChild->getData('tax_amount');
                        $rewardPointsPerChild = 0;
                        $redemptionFlag = 'N';

                        if($isEnableRewardsPoint) {
                            if ($mileageUsedAmountExisted > $itemMiamt) {
                                $mileageUsedAmountExisted -= $itemMiamt;
                            } else {
                                $itemMiamt = $mileageUsedAmountExisted;
                                $mileageUsedAmountExisted = 0;
                            }

                            if ($bundleChild->getData('am_spent_reward_points')) {
                                $rewardPointsPerChild = $this->orderData->roundingPrice($bundleChild->getData('am_spent_reward_points'), $isDecimalFormat);
                            }
                            $discountFromPoints = $rewardPointsPerChild / $spendingRate;
                            if ($discountFromPoints >= $itemNsamt) {
                                $redemptionFlag = 'Y';
                            }
                        }
                        $itemNetwr = $itemSlamt - $itemMiamt - $itemTaxAmount;

                        $this->addOrderItemData(
                            $order, $orderItem, $itemNsamt, $itemDcamt,
                            $itemSlamt, $itemMiamt, $itemNetwr, $itemTaxAmount,
                            $redemptionFlag, $rewardPointsPerChild, $bundleChild
                        );
                    }
                }
            }
        }

        $orderGrandtotal = $order->getGrandTotal() == 0 ? $order->getGrandTotal() : $this->orderData->roundingPrice($order->getGrandTotal() - $order->getShippingAmount(), $isDecimalFormat);
        $orderDiscountAmount = abs($this->orderData->roundingPrice($order->getDiscountAmount(), $isDecimalFormat)) - $mileageUsedAmount;

        if ($isEnableRewardsPoint && $mileageUsedAmountExisted) {
            $this->_itemsGrandTotalInclTax -= $mileageUsedAmountExisted;
        }

        $this->_orderItemData = $this->orderData->priceCorrector($orderSubtotal, $this->_itemsSubtotal, $this->_orderItemData, 'itemNsamt', $isDecimalFormat);
        $this->_orderItemData = $this->orderData->priceCorrector($orderDiscountAmount, $this->_itemsDiscountAmount, $this->_orderItemData, 'itemDcamt', $isDecimalFormat);
        $this->_orderItemData = $this->orderData->priceCorrector($mileageUsedAmount, $this->_itemsMileage, $this->_orderItemData, 'itemMiamt', $isDecimalFormat);
        $this->_orderItemData = $this->orderData->priceCorrector($orderGrandtotal, $this->_itemsGrandTotal, $this->_orderItemData, 'itemNetwr', $isDecimalFormat);
        $this->_orderItemData = $this->orderData->priceCorrector($orderGrandtotal, $this->_itemsGrandTotalInclTax, $this->_orderItemData, 'itemSlamt', $isDecimalFormat);

        if ($isDecimalFormat) {
            $listToFormat = ['itemNsamt', 'itemSlamt', 'itemDcamt', 'itemMiamt', 'itemNetwr'];

            foreach ($listToFormat as $field) {
                foreach ($this->_orderItemData as $key => $value) {
                    if (isset($value[$field]) && (is_float($value[$field]) || is_int($value[$field]))) {
                        $this->_orderItemData[$key][$field] = $this->orderData->formatPrice($value[$field], $isDecimalFormat);
                    }
                }
            }
        }

        return $this->_orderItemData;
    }

    /**
     * @param $orderId
     * @return false|mixed|null
     */
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

    /**
     * @param $paymentMethod
     * @return string
     */
    public function getPaymentCode($paymentMethod)
    {
        if ($paymentMethod == 'ecpay_ecpaypayment') {
            $paymentCode = "P";
        } else {
            $paymentCode = "S";
        }
        return $paymentCode;
    }

    /**
     * Add order data
     *
     * @param $order
     * @param $newOrderItem
     * @param $itemNsamt
     * @param $itemDcamt
     * @param $itemSlamt
     * @param $itemMiamt
     * @param $itemNetwr
     * @param $itemTaxAmount
     * @param $redemptionFlag
     * @param $rewardPoints
     * @param $bundleChild
     * @return void
     * @throws NoSuchEntityException
     */
    private function addOrderItemData(
        $order, $newOrderItem, $itemNsamt, $itemDcamt,
        $itemSlamt, $itemMiamt, $itemNetwr, $itemTaxAmount,
        $redemptionFlag, $rewardPoints,
        $bundleChild = null
    ) {
        $storeId = $order->getStoreId();
        $skuPrefix = $this->config->getSapSkuPrefix($storeId);
        $skuPrefix = $skuPrefix ?: '';
        $sku = $newOrderItem->getSku();
        $itemMenge = $newOrderItem->getQtyOrdered();
        $itemId = $newOrderItem->getItemId();
        if ($bundleChild) {
            $sku = $bundleChild->getSku();
            $itemMenge = $bundleChild->getQtyOrdered();
            $itemId = $bundleChild->getItemId();
        }

        $product = $this->productRepository->get($sku, false, $order->getStoreId());
        $meins = $product->getData('meins');
        $sku = str_replace($skuPrefix, '', $sku);
        $isMileageOrderItem = ($itemSlamt == $itemMiamt && $itemSlamt > 0);
        $salesOrg = $this->config->getSalesOrg('store', $storeId);
        $client = $this->config->getClient('store', $storeId);

        $this->_orderItemData[] = [
            'itemVkorg' => $salesOrg,
            'itemKunnr' => $client,
            'itemOdrno' => $order->getIncrementId(),
            'itemPosnr' => $this->cnt,
            'itemMatnr' => $sku,
            'itemMenge' => intval($itemMenge),
            // 아이템 단위, Default : EA
            'itemMeins' => $this->getMeins($meins),
            'itemNsamt' => $itemNsamt,
            'itemDcamt' => $itemDcamt,
            'itemSlamt' => $itemSlamt,
            'itemMiamt' => $itemMiamt,
            // 상품이 무상제공인 경우 Y 아니면 N
            'itemFgflg' => ($itemSlamt == 0 ? 'Y' : 'N'),
            'itemMilfg' => ($isMileageOrderItem) ? 'Y' : 'N',
            'itemAuart' => ($isMileageOrderItem) ? self::SAMPLE_ORDER : self::NORMAL_ORDER,
            'itemAugru' => ($isMileageOrderItem) ? 'F07' : 'A08',
            'itemNetwr' => $itemNetwr,
            'itemMwsbp' => $itemTaxAmount,
            'itemVkorgOri' => $salesOrg,
            'itemKunnrOri' => $client,
            'itemOdrnoOri' => $order->getIncrementId(),
            'itemPosnrOri' => $this->cnt,
            'itemId' => $itemId,
            'redemptionFlag' => $redemptionFlag,
            'PointAccount' => $rewardPoints
        ];

        $this->cnt++;
        $this->_itemsSubtotal += $itemNsamt;
        $this->_itemsGrandTotalInclTax += $itemSlamt - $itemMiamt;
        $this->_itemsGrandTotal +=  $itemNetwr;
        $this->_itemsDiscountAmount += $itemDcamt;
        $this->_itemsMileage += $itemMiamt;
    }
}
