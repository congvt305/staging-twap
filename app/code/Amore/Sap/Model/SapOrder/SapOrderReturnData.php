<?php
/**
 * Created by Eguana.
 * User: Brian
 * Date: 2020-07-22
 * Time: 오후 8:14
 */

namespace Amore\Sap\Model\SapOrder;

use Amore\Sap\Exception\RmaTrackNoException;
use Amore\Sap\Model\SapOrder\Product\Bundle\CalculatePrice;
use Amore\Sap\Model\Source\Config;
use Eguana\GWLogistics\Model\QuoteCvsLocationRepository;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Eav\Api\AttributeRepositoryInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Model\Order\Item;
use Magento\Store\Api\StoreRepositoryInterface;
use Magento\Store\Model\StoreManagerInterface;
use Eguana\Directory\Helper\Data;

class SapOrderReturnData extends AbstractSapOrder
{
    const AUGRU_RETURN_CODE = 'R05';

    const ABRVW_RETURN_CODE = 'R51';

    const AUGRU_MILEAGE_ALL_RETURN_CODE = 'F07';

    const ABRVW_MILEAGE_ALL_RETURN_CODE = 'FZ1';

    private $_cnt = 1;

    private $_rmaItemData = [];

    private $_itemsSubtotal = 0;

    private $_itemsGrandTotalInclTax = 0;

    private $_itemsGrandTotal = 0;

    private $_itemsDiscountAmount = 0;

    private $_itemsMileage = 0;

    /**
     * @var CustomerRepositoryInterface
     */
    private $customerRepository;

    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;

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
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param OrderRepositoryInterface $orderRepository
     * @param StoreRepositoryInterface $storeRepository
     * @param Config $config
     * @param CustomerRepositoryInterface $customerRepository
     * @param QuoteCvsLocationRepository $quoteCvsLocationRepository
     * @param ProductRepositoryInterface $productRepository
     * @param AttributeRepositoryInterface $eavAttributeRepositoryInterface
     * @param StoreManagerInterface $storeManager
     * @param Data $helper
     * @param \CJ\Middleware\Helper\Data $middlewareHelper
     * @param \Amasty\Rewards\Model\Config $amConfig
     * @param \CJ\Rewards\Model\Data $rewardData
     * @param CalculatePrice $bundleCalculatePrice
     * @param Product\CalculatePrice $productCalculatePrice
     * @param \CJ\Middleware\Model\Data $orderData
     */
    public function __construct(
        SearchCriteriaBuilder $searchCriteriaBuilder,
        OrderRepositoryInterface $orderRepository,
        StoreRepositoryInterface $storeRepository,
        Config $config,
        CustomerRepositoryInterface $customerRepository,
        QuoteCvsLocationRepository $quoteCvsLocationRepository,
        ProductRepositoryInterface $productRepository,
        AttributeRepositoryInterface $eavAttributeRepositoryInterface,
        StoreManagerInterface $storeManager,
        Data $helper,
        \CJ\Middleware\Helper\Data $middlewareHelper,
        \Amasty\Rewards\Model\Config $amConfig,
        \CJ\Rewards\Model\Data $rewardData,
        CalculatePrice $bundleCalculatePrice,
        \Amore\Sap\Model\SapOrder\Product\CalculatePrice $productCalculatePrice,
        \CJ\Middleware\Model\Data $orderData,
        \Amore\Sap\Logger\Logger $logger
    ) {
        $this->customerRepository = $customerRepository;
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
     * @param \Magento\Rma\Model\Rma $rma
     * @throws RmaTrackNoException
     * @throws NoSuchEntityException
     * @throws LocalizedException
     */
    public function singleOrderData($rma)
    {
        $source = $this->config->getSourceByStore('store', $rma->getStoreId());
        $rmaData = $this->getRmaData($rma);
        $rmaItemData = $this->getRmaItemData($rma);

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
     * Get rma data
     *
     * @param \Magento\Rma\Model\Rma $rma
     * @throws RmaTrackNoException
     * @throws NoSuchEntityException
     * @throws LocalizedException
     */
    public function getRmaData($rma)
    {
        $storeId = $rma->getStoreId();
        $order = $rma->getOrder();
        $customer = $this->getCustomer($rma->getCustomerId());
        $shippingAddress = $order->getShippingAddress();
        $pointUsed = 0;
        $isDecimalFormat = $this->middlewareHelper->getIsDecimalFormat('store', $storeId);
        if ($order->getGrandTotal() == 0) {
            $orderGrandTotal = $order->getGrandTotal();
        } else {
            $orderGrandTotal = abs($this->orderData->roundingPrice($order->getGrandTotal() - $order->getShippingAmount(), $isDecimalFormat));
        }
        $trackData = $this->getTracks($rma);
        $ztrackId = $trackData['track_number'] ?? '';
        $shippingMethod = $order->getShippingMethod();
        $rewardPoints = 0;
        $redemptionFlag = 'N';
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
                $pointUsed = $listOptions[$rewardPoints] ?? 0;
            } else {
                $pointUsed = $rewardPoints / $spendingRate;
            }
            if ($pointUsed == $order->getBaseSubtotal()) {
                $redemptionFlag = 'Y';
            }
            $miamt = abs($this->orderData->roundingPrice($pointUsed, $isDecimalFormat));
        } else {
            $miamt = 0;
        }

        $websiteId = (int)$this->storeManager->getStore($storeId)->getWebsiteId();
        $websiteCode = $this->storeManager->getWebsite($websiteId)->getCode();
        if ($websiteCode == 'vn_laneige_website') {
            $paymtd = 10;
            $nsamt  = $order->getData('sap_nsamt');
            $dcamt  = $order->getData('sap_dcamt');
            $slamt  = $order->getData('sap_slamt');
        } else {
            $paymtd = $order->getPayment()->getMethod() == 'ecpay_ecpaypayment' ? 'P' : 'S';
            $nsamt  = $this->orderData->roundingPrice($order->getSubtotalInclTax(), $isDecimalFormat);;
            $dcamt  = abs($this->orderData->roundingPrice($order->getDiscountAmount(), $isDecimalFormat)) - $miamt;
            $slamt = $nsamt - $dcamt;
        }

        $isMileageOrder = ($slamt == $miamt && $slamt > 0);
        $cvsShippingCheck = $this->cvsShippingCheck($order);
        $telephone = $this->getTelephone($shippingAddress->getTelephone());
        $salesOrg = $this->config->getSalesOrg('store', $storeId);
        $client = $this->config->getClient('store', $storeId);

        $bindData[] = [
            'vkorg' => $salesOrg,
            'kunnr' => $client,
            'odrno' => "R" . $rma->getIncrementId(),
            'odrdt' => $this->orderData->dateFormatting($rma->getDateRequested(), 'Ymd'),
            'odrtm' => $this->orderData->dateFormatting($rma->getDateRequested(), 'His'),
            'paymtd' => $paymtd,
            'paydt' => '',
            'paytm' => '',
            'payMode' => $order->getPayment()->getMethod() === 'cashondelivery' ? 'COD' : '',
            'dhlId' => $shippingMethod === 'eguanadhl_tablerate' ? 'TBD' : '',
            'shpSvccd' => $shippingMethod === 'eguanadhl_tablerate' ? 'PDE' : '',
            'ordWgt' => $shippingMethod === 'eguanadhl_tablerate' ? '1000' : '',
            'insurance' => $shippingMethod === 'eguanadhl_tablerate' ? 'Y' : '',
            'insurnaceValue' => $shippingMethod === 'eguanadhl_tablerate' ? $orderGrandTotal : null,
            'auart' => $isMileageOrder ? self::SAMPLE_RETURN : self::RETURN_ORDER,
            'augru' => $isMileageOrder ? self::AUGRU_MILEAGE_ALL_RETURN_CODE : self::AUGRU_RETURN_CODE,
            'augruText' => '',
            'abrvw' => $isMileageOrder ? self::ABRVW_MILEAGE_ALL_RETURN_CODE : self::ABRVW_RETURN_CODE,
            // 주문자회원코드-직영몰자체코드
            'custid' => $customer != '' ? $rma->getCustomerId() : '',
            'custnm' => $rma->getData('rma_customer_name') ?: $order->getCustomerLastname() . $order->getCustomerFirstname(),
            //배송지 id - 직영몰 자체코드, 없으면 공백
            'recvid' => '',
            'recvnm' => $shippingAddress->getLastname() . ' ' . $shippingAddress->getFirstname(),
            'postCode' => $cvsShippingCheck ? '00000' : $shippingAddress->getPostcode(),
            'addr1' => $cvsShippingCheck ? '.' : $shippingAddress->getRegion(),
            'addr2' => $cvsShippingCheck ? '.' : $shippingAddress->getCity(),
            'addr3' => $cvsShippingCheck ? '.' : preg_replace('/\r\n|\r|\n/', ' ', implode(PHP_EOL, $shippingAddress->getStreet())),
            'distrid' => $cvsShippingCheck ? '.' : $this->dataHelper->getDistrictCode($shippingAddress->getCityId()),
            'wardid' => $cvsShippingCheck ? '.' : $this->dataHelper->getWardCode($shippingAddress->getWardId()),
            'wardname' => $cvsShippingCheck ? '.' : $shippingAddress->getWard(),
            'land1' => $shippingAddress->getCountryId(),
            'telno' => $telephone,
            'hpno' => $telephone,
            'waerk' => $order->getOrderCurrencyCode(),
            'nsamt' => $nsamt,
            'dcamt' => $dcamt,
            'slamt' => $slamt,
            'miamt' => $miamt,
            'shpwr' => '',
            'mwsbp' => $this->orderData->roundingPrice($order->getTaxAmount(), $isDecimalFormat),
            'spitn1' => '',
            'vkorgOri' => $salesOrg,
            'kunnrOri' => $client,
            'odrnoOri' => $order->getIncrementId(),
            // 이건 물건 종류 갯수(물건 전체 수량은 아님)
            'itemCnt' => $order->getTotalItemCount(),
            // 영업 플랜트 : 알수 없을 경우 공백
            'werks' => '',
            // 영업저장위치 : 알수 없을 경우 공백
            'lgort' => '',
            'rmano' => '',
            // 납품처
            'kunwe' => $this->kunweCheck($order),
            // trackNo 가져와야 함
            'ztrackId' => $ztrackId,
            'redemptionFlag' => $redemptionFlag,
            'PointAccount' => $rewardPoints
        ];

        if ($isDecimalFormat) {
            $listToFormat = ['nsamt', 'dcamt', 'dcamt', 'miamt', 'shpwr', 'mwsbp'];
            foreach ($bindData[0] as $k => $value) {
                if (in_array($k, $listToFormat) && (is_float($value) || is_int($value))) {
                    $bindData[0][$k] = $this->orderData->formatPrice($value, $isDecimalFormat);
                }
            }
        }

        return $bindData;
    }

    /**
     * Get rma item data
     *
     * @param \Magento\Rma\Model\Rma $rma
     * @throws NoSuchEntityException
     * @throws \Exception
     */
    public function getRmaItemData($rma)
    {
        $storeId = $rma->getStoreId();
        $rmaItems = $rma->getItems();
        $order = $rma->getOrder();
        $isDecimalFormat = $this->middlewareHelper->getIsDecimalFormat('store', $order->getStoreId());
        $orderSubtotal = abs($this->orderData->roundingPrice($order->getSubtotalInclTax(), $isDecimalFormat));
        $mileageUsedAmount = 0;
        $originPosnr = $this->getOrderItemPosnr($rma);
        $mileageUsedAmountExisted = 0;
        $spendingRate = $this->amConfig->getPointsRate($storeId);
        if (!$spendingRate) {
            $spendingRate = 1;
        }
        if($isEnableRewardsPoint = $this->amConfig->isEnabled($storeId)) {
            $rewardPoints = 0;
            if ($order->getData('am_spent_reward_points')) {
                $rewardPoints = $this->orderData->roundingPrice($order->getData('am_spent_reward_points'), $isDecimalFormat);
            }
            if ($this->rewardData->isEnableShowListOptionRewardPoint($storeId)) {
                $listOptions = $this->rewardData->getListOptionRewardPoint($storeId);
                $mileageUsedAmount = $listOptions[$rewardPoints] ?? 0;
            } else {
                $mileageUsedAmount = $rewardPoints / $spendingRate;
            }
            $mileageUsedAmountExisted = $mileageUsedAmount;
        }

        $orderAllItems = $order->getAllItems();
        $websiteId = (int)$this->storeManager->getStore($storeId)->getWebsiteId();
        $websiteCode = $this->storeManager->getWebsite($websiteId)->getCode();

        /** @var \Magento\Rma\Model\Item $rmaItem */
        foreach ($rmaItems as $rmaItem) {
            /** @var \Magento\Sales\Model\Order\Item $orderItem */
            $orderItem = $order->getItemById($rmaItem->getOrderItemId());
            if ($orderItem->getProductType() != 'bundle') {
                $orderItem = $this->productCalculatePrice->calculate($orderItem, $spendingRate, $isEnableRewardsPoint, $isDecimalFormat);
                if ($orderItem->getParentItem() && $orderItem->getParentItem()->getProductType() == 'bundle') {
                    continue;
                }

                $itemDcamt = $orderItem->getDiscountAmount();
                $itemNsamt = $this->orderData->roundingPrice($orderItem->getPrice() * $orderItem->getQtyOrdered(), $isDecimalFormat);
                $itemSlamt = $itemNsamt - $itemDcamt;
                $itemMiamt = $orderItem->getData('mileage_amount');
                $itemTaxAmount = $orderItem->getData('tax_amount');
                $itemNetwr = $itemSlamt - $itemMiamt - $itemTaxAmount;

                if($isEnableRewardsPoint) {
                    if ($mileageUsedAmountExisted > $itemMiamt) {
                        $mileageUsedAmountExisted -= $itemMiamt;
                    } else {
                        $itemMiamt = $mileageUsedAmountExisted;
                        $mileageUsedAmountExisted = 0;
                    }
                }

                if ($websiteCode == 'vn_laneige_website') {
                    $itemNsamt = $orderItem->getData('sap_item_nsamt');
                    $itemDcamt = $orderItem->getData('sap_item_dcamt');
                    $itemSlamt = $orderItem->getData('sap_item_slamt');
                    $itemNetwr = $orderItem->getData('sap_item_netwr');
                }
                $this->addReturnOrderItemData(
                    $rma, $rmaItem, $itemNsamt,
                    $itemDcamt, $itemSlamt, $itemMiamt,
                    $itemNetwr, $itemTaxAmount, $originPosnr
                );
            } else {
                $orderItem = $this->bundleCalculatePrice->calculate($orderItem, $spendingRate, $isEnableRewardsPoint, $isDecimalFormat);
                foreach ($orderItem->getChildrenItems() as $bundleChildrenItem) {
                    $itemId = $rmaItem->getOrderItemId();
                    $itemDcamt = $bundleChildrenItem->getDiscountAmount();
                    $itemNsamt = $bundleChildrenItem->getData('normal_sales_amount');
                    $itemSlamt = $itemNsamt - $itemDcamt;
                    $itemMiamt = $bundleChildrenItem->getData('mileage_amount');
                    $itemTaxAmount = $bundleChildrenItem->getData('tax_amount');
                    $itemNetwr = $itemSlamt - $itemMiamt - $itemTaxAmount;

                    if($isEnableRewardsPoint) {
                        if ($mileageUsedAmountExisted > $itemMiamt) {
                            $mileageUsedAmountExisted -= $itemMiamt;
                        } else {
                            $itemMiamt = $mileageUsedAmountExisted;
                            $mileageUsedAmountExisted = 0;
                        }
                    }

                    if ($websiteCode == 'vn_laneige_website') {
                        $item = $this->searchOrderItem($orderAllItems, $bundleChildrenItem->getSku(), $itemId);
                        $itemNsamt = $item->getData('sap_item_nsamt');
                        $itemDcamt = $item->getData('sap_item_dcamt');
                        $itemSlamt = $item->getData('sap_item_slamt');
                        $itemNetwr = $item->getData('sap_item_netwr');
                    }

                    $this->addReturnOrderItemData($rma, $rmaItem, $itemNsamt,
                        $itemDcamt, $itemSlamt, $itemMiamt, $itemNetwr,
                        $itemTaxAmount, $originPosnr, $bundleChildrenItem
                    );

                }
            }
        }
        $orderGrandTotal = $order->getGrandTotal() == 0 ? $order->getGrandTotal() : $this->orderData->roundingPrice($order->getGrandTotal() - $order->getShippingAmount(), $isDecimalFormat);
        $orderDiscountAmount = abs($this->orderData->roundingPrice($order->getDiscountAmount(), $isDecimalFormat)) - $mileageUsedAmount;
        if ($isEnableRewardsPoint && $mileageUsedAmountExisted) {
            $this->_itemsGrandTotalInclTax -= $mileageUsedAmountExisted;
        }
        if ($websiteCode != 'vn_laneige_website') {
            $this->_rmaItemData = $this->orderData->priceCorrector($orderSubtotal, $this->_itemsSubtotal, $this->_rmaItemData, 'itemNsamt', $isDecimalFormat);
            $this->_rmaItemData = $this->orderData->priceCorrector($orderGrandTotal, $this->_itemsGrandTotal, $this->_rmaItemData, 'itemNetwr', $isDecimalFormat);
            $this->_rmaItemData = $this->orderData->priceCorrector($orderDiscountAmount, $this->_itemsDiscountAmount, $this->_rmaItemData, 'itemDcamt', $isDecimalFormat);
            $this->_rmaItemData = $this->orderData->priceCorrector($orderGrandTotal, $this->_itemsGrandTotalInclTax, $this->_rmaItemData, 'itemSlamt', $isDecimalFormat);
        }
        $this->_rmaItemData = $this->orderData->priceCorrector($mileageUsedAmount, $this->_itemsMileage, $this->_rmaItemData, 'itemMiamt', $isDecimalFormat);

        if ($isDecimalFormat) {
            $listToFormat = ['itemNsamt', 'itemSlamt', 'itemNetwr', 'itemDcamt', 'itemMiamt'];

            foreach ($listToFormat as $field) {
                foreach ($this->_rmaItemData as $key => $value) {
                    if (isset($value[$field]) && (is_float($value[$field]) || is_int($value[$field]))) {
                        $this->_rmaItemData[$key][$field] = $this->orderData->formatPrice($value[$field], $isDecimalFormat);
                    }
                }
            }
        }

        return $this->_rmaItemData;
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
        $storeData = $this->storeRepository->getById($rma->getStoreId());
        $storeCode = (string)$storeData->getCode();

        $trackData = [];
        if ($storeCode != "vn_laneige") {
            $tracks = $rma->getTracks();
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
                $storeData = $this->storeRepository->getById($rma->getStoreId());
                $storeCode = (string)$storeData->getCode();
                if ($storeCode == "vn_laneige") {
                    return $trackData[0];
                } else {
                    throw new RmaTrackNoException(__("Tracking No Does Not Exist."));
                }
            } else {
                throw new RmaTrackNoException(__("Tracking No Exist more than 1."));
            }
        } else {
            return $trackData;
        }
    }

    /**
     * @param \Magento\Rma\Model\Rma $rma
     */
    public function getOrderItemPosnr($rma)
    {
        $order = $rma->getOrder();
        $orderItems = $order->getAllVisibleItems();
        $originPosnrData = [];

        $cnt = 1;

        /** @var \Magento\Sales\Model\Order\Item $orderItem */
        foreach ($orderItems as $orderItem) {
            if ($orderItem->getProductType() != 'bundle') {
                $originPosnrData[$orderItem->getItemId()] = $cnt;
                $cnt++;
            } else {
                $bundleChildren = $orderItem->getChildrenItems();
                foreach ($bundleChildren as $bundleChild) {
                    $originPosnrData[$bundleChild->getItemId()] = $cnt;
                    $cnt++;
                }
            }
        }

        return $originPosnrData;
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
     * Add rma item
     *
     * @param $rma
     * @param $rmaItem
     * @param $itemNsamt
     * @param $itemDcamt
     * @param $itemSlamt
     * @param $itemMiamt
     * @param $itemNetwr
     * @param $itemTaxAmount
     * @param $originPosnr
     * @param $bundleChild
     * @return void
     * @throws NoSuchEntityException
     */
    private function addReturnOrderItemData(
        $rma, $rmaItem, $itemNsamt,
        $itemDcamt, $itemSlamt, $itemMiamt,
        $itemNetwr, $itemTaxAmount,
        $originPosnr, $bundleChild = null
    ) {
        if ($bundleChild) {
            $sku = $bundleChild->getSku();
            $itemId = $bundleChild->getItemId();
        } else {
            $sku = $rmaItem->getProductSku();
            $itemId = $rmaItem->getOrderItemId();
        }

        $storeId = $rma->getStoreId();
        $order = $rma->getOrder();
        $product = $this->productRepository->get($sku, false, $rma->getStoreId());
        $meins = $product->getData('meins');
        $skuPrefix = $this->config->getSapSkuPrefix($storeId);
        $skuPrefix = $skuPrefix ?: '';
        $sku = str_replace($skuPrefix, '', $sku);
        $isMileageOrderItem = $itemSlamt > 0 && $itemSlamt == $itemMiamt;
        $salesOrg = $this->config->getSalesOrg('store', $storeId);
        $client = $this->config->getClient('store', $storeId);

        $this->_rmaItemData[] = [
            'itemVkorg' => $salesOrg,
            'itemKunnr' => $client,
            'itemOdrno' => "R" . $rma->getIncrementId(),
            'itemPosnr' => $this->_cnt,
            'itemMatnr' => $sku,
            'itemMenge' => intval($rmaItem->getQtyRequested()),
            // 아이템 단위, Default : EA
            'itemMeins' => $this->getMeins($meins),
            'itemNsamt' => $itemNsamt,
            'itemDcamt' => $itemDcamt,
            'itemSlamt' => $itemSlamt,
            'itemMiamt' => $itemMiamt,
            // 상품이 무상제공인 경우 Y 아니면 N
            'itemFgflg' => $itemSlamt == 0 ? 'Y' : 'N',
            'itemMilfg' => (($itemSlamt == $itemMiamt) && $itemSlamt > 0) ? 'Y' : 'N',
            'itemAuart' => $isMileageOrderItem ? self::SAMPLE_RETURN : self::RETURN_ORDER,
            'itemAugru' => $isMileageOrderItem ? self::AUGRU_MILEAGE_ALL_RETURN_CODE : self::AUGRU_RETURN_CODE,
            'itemAbrvw' => $isMileageOrderItem ? self::ABRVW_MILEAGE_ALL_RETURN_CODE : self::ABRVW_RETURN_CODE,
            'itemNetwr' => $itemNetwr,
            'itemMwsbp' => $itemTaxAmount,
            'itemVkorgOri' => $salesOrg,
            'itemKunnrOri' => $client,
            'itemOdrnoOri' => $order->getIncrementId(),
            'itemPosnrOri' => $originPosnr[$itemId]
        ];

        $this->_cnt++;
        $this->_itemsSubtotal += $itemNsamt;
        $this->_itemsGrandTotal += ($itemNsamt - $itemDcamt - $itemMiamt);
        $this->_itemsGrandTotalInclTax += ($itemNsamt - $itemDcamt - $itemMiamt - $itemTaxAmount);
        $this->_itemsDiscountAmount += $itemDcamt;
        $this->_itemsMileage += $itemMiamt;
    }
}
