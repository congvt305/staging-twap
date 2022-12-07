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
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Eav\Api\AttributeRepositoryInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Rma\Api\RmaRepositoryInterface;
use Magento\Sales\Api\OrderItemRepositoryInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Model\Order\Item;
use Magento\Sales\Model\ResourceModel\Order\Item\Collection;
use Magento\Sales\Model\ResourceModel\Order\Item\CollectionFactory;
use Magento\Store\Api\StoreRepositoryInterface;
use Magento\Store\Model\StoreManagerInterface;
use Eguana\Directory\Helper\Data;

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
     * @var ProductRepositoryInterface
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
     * @param ProductRepositoryInterface $productRepository
     * @param AttributeRepositoryInterface $eavAttributeRepositoryInterface
     * @param \Magento\Bundle\Api\ProductLinkManagementInterface $productLinkManagement
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
        RmaRepositoryInterface $rmaRepository,
        CustomerRepositoryInterface $customerRepository,
        TimezoneInterface $timezoneInterface,
        QuoteCvsLocationRepository $quoteCvsLocationRepository,
        OrderItemRepositoryInterface $orderItemRepository,
        CollectionFactory $itemCollectionFactory,
        ProductRepositoryInterface $productRepository,
        AttributeRepositoryInterface $eavAttributeRepositoryInterface,
        \Magento\Bundle\Api\ProductLinkManagementInterface $productLinkManagement,
        StoreManagerInterface $storeManager,
        Data $helper,
        \CJ\Middleware\Helper\Data $middlewareHelper,
        \Amasty\Rewards\Model\Config $amConfig
    ) {
        $this->rmaRepository = $rmaRepository;
        $this->customerRepository = $customerRepository;
        $this->timezoneInterface = $timezoneInterface;
        $this->quoteCvsLocationRepository = $quoteCvsLocationRepository;
        $this->orderItemRepository = $orderItemRepository;
        $this->itemCollectionFactory = $itemCollectionFactory;
        parent::__construct($searchCriteriaBuilder, $orderRepository, $storeRepository, $config);
        $this->productRepository = $productRepository;
        $this->eavAttributeRepositoryInterface = $eavAttributeRepositoryInterface;
        $this->productLinkManagement = $productLinkManagement;
        $this->storeManager = $storeManager;
        $this->dataHelper = $helper;
        $this->middlewareHelper = $middlewareHelper;
        $this->amConfig = $amConfig;
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
        $pointUsed = $order->getRewardPointsBalance();
        $isDecimalFormat = $this->middlewareHelper->getIsDecimalFormat('store', $storeId);
        $orderTotal = $this->roundingPrice($order->getSubtotalInclTax() + $order->getDiscountAmount() + $order->getShippingAmount(), $isDecimalFormat);
        $trackData = $this->getTracks($rma);
        $ztrackId = $trackData['track_number'] ?? '';
        $shippingMethod = $order->getShippingMethod();
        $rewardPoints = 0;
        $redemptionFlag = 'N';
        if($this->amConfig->isEnabled($storeId)) {
            $rewardPoints = (int)$order->getData('am_spent_reward_points');
            $spendingRate = $this->amConfig->getPointsRate($storeId);
            if (!$spendingRate) {
                $spendingRate = 1;
            }
            $pointUsed = $rewardPoints / $spendingRate;
            if ($pointUsed == $order->getBaseSubtotal()) {
                $redemptionFlag = 'Y';
            }
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
            $nsamt  = abs($this->roundingPrice($this->getRmaSubtotalInclTax($rma) + $this->getBundleExtraAmount($rma) + $this->getCatalogRuleDiscountAmount($rma), $isDecimalFormat));
            $dcamt  = abs($this->roundingPrice($this->getRmaDiscountAmount($rma, $isDecimalFormat), $isDecimalFormat));
            $slamt = $order->getGrandTotal() == 0 ? $order->getGrandTotal() :
                abs($this->roundingPrice($this->getRmaGrandTotal($rma, $orderTotal, $pointUsed), $isDecimalFormat));
        }

        $bindData[] = [
            'vkorg' => $this->config->getSalesOrg('store', $storeId),
            'kunnr' => $this->config->getClient('store', $storeId),
            'odrno' => "R" . $rma->getIncrementId(),
            'odrdt' => $this->dateFormatting($rma->getDateRequested(), 'Ymd'),
            'odrtm' => $this->dateFormatting($rma->getDateRequested(), 'His'),
            'paymtd' => $paymtd,
            'paydt' => '',
            'paytm' => '',
            'payMode' => $order->getPayment()->getMethod() === 'cashondelivery' ? 'COD' : '',
            'dhlId' => $shippingMethod === 'eguanadhl_tablerate' ? 'TBD' : '',
            'shpSvccd' => $shippingMethod === 'eguanadhl_tablerate' ? 'PDE' : '',
            'ordWgt' => $shippingMethod === 'eguanadhl_tablerate' ? '1000' : '',
            'insurance' => $shippingMethod === 'eguanadhl_tablerate' ? 'Y' : '',
            'insurnaceValue' => $shippingMethod === 'eguanadhl_tablerate' ? $orderTotal : null,
            'auart' => self::RETURN_ORDER,
            'augru' => self::AUGRU_RETURN_CODE,
            'augruText' => '',
            'abrvw' => self::ABRVW_RETURN_CODE,
            // 주문자회원코드-직영몰자체코드
            'custid' => $customer != '' ? $rma->getCustomerId() : '',
            'custnm' => $order->getCustomerLastname() . $order->getCustomerFirstname(),
            //배송지 id - 직영몰 자체코드, 없으면 공백
            'recvid' => '',
            'recvnm' => $shippingAddress->getLastname() . ' ' . $shippingAddress->getFirstname(),
            'postCode' => $this->cvsShippingCheck($order) ? '00000' : $shippingAddress->getPostcode(),
            'addr1' => $this->cvsShippingCheck($order) ? '.' : $shippingAddress->getRegion(),
            'addr2' => $this->cvsShippingCheck($order) ? '.' : $shippingAddress->getCity(),
            'addr3' => $this->cvsShippingCheck($order) ? '.' : preg_replace('/\r\n|\r|\n/', ' ', implode(PHP_EOL, $shippingAddress->getStreet())),
            'distrid' => $this->cvsShippingCheck($order) ? '.' : $this->dataHelper->getDistrictCode($shippingAddress->getCityId()),
            'wardid' => $this->cvsShippingCheck($order) ? '.' : $this->dataHelper->getWardCode($shippingAddress->getWardId()),
            'wardname' => $this->cvsShippingCheck($order) ? '.' : $shippingAddress->getWard(),
            'land1' => $shippingAddress->getCountryId(),
            'telno' => $this->getTelephone($shippingAddress->getTelephone()),
            'hpno' => $this->getTelephone($shippingAddress->getTelephone()),
            'waerk' => $order->getOrderCurrencyCode(),
            'nsamt' => $nsamt,
            'dcamt' => $dcamt,
            'slamt' => $slamt,
            'miamt' => abs($this->roundingPrice($this->getRmaPointsUsed($rma, $pointUsed, $orderTotal), $isDecimalFormat)),
            'shpwr' => '',
            'mwsbp' => $this->roundingPrice($order->getTaxAmount(), $isDecimalFormat),
            'spitn1' => '',
            'vkorgOri' => $this->config->getSalesOrg('store', $storeId),
            'kunnrOri' => $this->config->getClient('store', $storeId),
            'odrnoOri' => $order->getIncrementId(),
            // 이건 물건 종류 갯수(물건 전체 수량은 아님)
            'itemCnt' => $this->calculateItems($rma),
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
                    $bindData[0][$k] = $this->formatPrice($value, $isDecimalFormat);
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
        $rmaItemData = [];
        $storeId = $rma->getStoreId();
        $rmaItems = $rma->getItems();
        $order = $rma->getOrder();
        $isDecimalFormat = $this->middlewareHelper->getIsDecimalFormat('store', $order->getStoreId());
        $orderTotal = $this->roundingPrice($order->getSubtotalInclTax() + $order->getDiscountAmount() + $order->getShippingAmount(), $isDecimalFormat);
        $mileageUsedAmount = $order->getRewardPointsBalance();
        $originPosnr = $this->getOrderItemPosnr($rma);
        $pointUsed = $order->getRewardPointsBalance();

        if($this->amConfig->isEnabled($storeId)) {
            $rewardPoints = (int)$order->getData('am_spent_reward_points');
            $spendingRate = $this->amConfig->getPointsRate($storeId);
            if (!$spendingRate) {
                $spendingRate = 1;
            }
            $mileageUsedAmount = $rewardPoints / $spendingRate;
        }

        $itemsSubtotal = 0;
        $itemsDiscountAmount = 0;
        $itemsGrandTotal = 0;
        $itemsGrandTotalInclTax = 0;
        $itemsMileage = 0;

        $skuPrefix = $this->config->getSapSkuPrefix($storeId);
        $skuPrefix = $skuPrefix ?: '';
        $orderAllItems = $order->getAllItems();
        $websiteId = (int)$this->storeManager->getStore($storeId)->getWebsiteId();
        $websiteCode = $this->storeManager->getWebsite($websiteId)->getCode();

        $cnt = 1;
        /** @var \Magento\Rma\Model\Item $rmaItem */
        foreach ($rmaItems as $rmaItem) {
            $orderItem = $this->orderItemRepository->get($rmaItem->getOrderItemId());
            if ($orderItem->getProductType() != 'bundle') {
                $mileagePerItem = $this->mileageSpentRateByItem(
                    $orderTotal,
                    $orderItem->getRowTotalInclTax(),
                    $orderItem->getDiscountAmount(),
                    $mileageUsedAmount,
                    $isDecimalFormat
                );

                $product = $this->productRepository->get($rmaItem->getProductSku());
                $meins = $product->getData('meins');

                $itemSubtotal = abs($this->roundingPrice($orderItem->getPrice() * $rmaItem->getQtyRequested(), $isDecimalFormat));
                if ($this->roundingPrice($orderItem->getPrice(), $isDecimalFormat)) {
                    $itemTotalDiscount = abs($this->roundingPrice($this->getRateAmount($orderItem->getDiscountAmount(), $orderItem->getQtyOrdered(), $rmaItem->getQtyRequested()), $isDecimalFormat));
                } else {
                    $itemTotalDiscount = 0;
                }
                $itemMileageUsed = abs($this->roundingPrice($this->getRateAmount($mileagePerItem, $this->getNetQty($orderItem), $rmaItem->getQtyRequested()), $isDecimalFormat));
                $itemTaxAmount = abs($this->roundingPrice($this->getRateAmount($orderItem->getTaxAmount(), $this->getNetQty($orderItem), $rmaItem->getQtyRequested()), $isDecimalFormat));

                $sku = str_replace($skuPrefix, '', $this->productTypeCheck($orderItem)->getSku());
                $itemNsamt = $itemSubtotal;
                $itemDcamt = $itemTotalDiscount;
                $itemSlamt = $itemSubtotal - $itemTotalDiscount - $itemMileageUsed;
                $itemNetwr = $itemSubtotal - $itemTotalDiscount - $itemMileageUsed - $itemTaxAmount;

                if ($websiteCode == 'vn_laneige_website') {
                    $itemNsamt = $orderItem->getData('sap_item_nsamt');
                    $itemDcamt = $orderItem->getData('sap_item_dcamt');
                    $itemSlamt = $orderItem->getData('sap_item_slamt');
                    $itemNetwr = $orderItem->getData('sap_item_netwr');
                }

                $rmaItemData[] = [
                    'itemVkorg' => $this->config->getSalesOrg('store', $storeId),
                    'itemKunnr' => $this->config->getClient('store', $storeId),
                    'itemOdrno' => "R" . $rma->getIncrementId(),
                    'itemPosnr' => $cnt,
                    'itemMatnr' => $sku,
                    'itemMenge' => intval($rmaItem->getQtyRequested()),
                    // 아이템 단위, Default : EA
                    'itemMeins' => $this->getMeins($meins),
                    'itemNsamt' => $itemNsamt,
                    'itemDcamt' => $itemDcamt,
                    'itemSlamt' => $itemSlamt,
                    'itemMiamt' => $itemMileageUsed,
                    // 상품이 무상제공인 경우 Y 아니면 N
                    'itemFgflg' => $itemSlamt == 0 ? 'Y' : 'N',
                    'itemMilfg' => empty($mileageUsedAmount) ? 'N' : 'Y',
                    'itemAuart' => self::RETURN_ORDER,
                    'itemAugru' => self::AUGRU_RETURN_CODE,
                    'itemAbrvw' => self::ABRVW_RETURN_CODE,
                    'itemNetwr' => $itemNetwr,
                    'itemMwsbp' => $itemTaxAmount,
                    'itemVkorgOri' => $this->config->getSalesOrg('store', $storeId),
                    'itemKunnrOri' => $this->config->getClient('store', $storeId),
                    'itemOdrnoOri' => $order->getIncrementId(),
                    'itemPosnrOri' => $originPosnr[$rmaItem->getOrderItemId()]
                ];
                $cnt++;
                $itemsSubtotal += $itemSubtotal;
                $itemsGrandTotal += ($itemSubtotal - $itemTotalDiscount - $itemMileageUsed);
                $itemsGrandTotalInclTax += ($itemSubtotal - $itemTotalDiscount - $itemMileageUsed - $itemTaxAmount);
                $itemsDiscountAmount += $itemTotalDiscount;
                $itemsMileage += $this->roundingPrice($this->getRateAmount($mileagePerItem, $this->getNetQty($orderItem), $rmaItem->getQtyRequested()), $isDecimalFormat);
            } else {
                /** @var \Magento\Catalog\Model\Product $bundleProduct */
                $bundleProduct = $this->productRepository->getById($orderItem->getProductId());
                $bundlePriceType = $bundleProduct->getPriceType();
                foreach ($orderItem->getChildrenItems() as $bundleChildrenItem) {
                    $itemId = $rmaItem->getOrderItemId();
                    if ((int)$bundlePriceType !== \Magento\Bundle\Model\Product\Price::PRICE_TYPE_DYNAMIC) {
                        $bundleChildItemPrice = $this->productRepository->get($bundleChildrenItem->getSku(), false, $order->getStoreId())->getPrice();
                    } else {
                        $bundleChildItemPrice = $bundleChildrenItem->getOriginalPrice();
                    }
                    $bundleChildDiscountAmount = (int)$bundlePriceType !== \Magento\Bundle\Model\Product\Price::PRICE_TYPE_DYNAMIC ?
                        $this->getProportionOfBundleChild($orderItem, $bundleChildrenItem, $orderItem->getDiscountAmount()) :
                        $bundleChildrenItem->getDiscountAmount();
                    $mileagePerItem = $this->mileageSpentRateByItem(
                        $orderTotal,
                        $this->getProportionOfBundleChild(
                            $orderItem,
                            $bundleChildrenItem,
                            $orderItem->getRowTotalInclTax()
                        ),
                        $bundleChildDiscountAmount,
                        $mileageUsedAmount,
                        $isDecimalFormat
                    );
                    $product = $this->productRepository->get($bundleChildrenItem->getSku(), false, $rma->getStoreId());
                    $meins = $product->getData('meins');
                    $qtyPerBundle = $bundleChildrenItem->getQtyOrdered() / $orderItem->getQtyOrdered();
                    $childPriceRatio = $this->getProportionOfBundleChild($orderItem, $bundleChildrenItem, $orderItem->getOriginalPrice()) / $qtyPerBundle;
                    $catalogRuledPriceRatio = $this->getProportionOfBundleChild($orderItem, $bundleChildrenItem, ($orderItem->getOriginalPrice() - $orderItem->getPrice())) / $qtyPerBundle;

                    $itemDiscountAmount = abs($this->roundingPrice(
                        $bundleChildDiscountAmount +
                        (($product->getPrice() - $childPriceRatio) * $bundleChildrenItem->getQtyOrdered()) +
                        $catalogRuledPriceRatio * $bundleChildrenItem->getQtyOrdered(), $isDecimalFormat)
                    );
                    $itemSubtotal = abs($this->roundingPrice($bundleChildItemPrice * $rmaItem->getQtyRequested() * $qtyPerBundle, $isDecimalFormat));
                    $itemTaxAmount = abs($this->roundingPrice($this->getRateAmount($bundleChildrenItem->getTaxAmount(), $this->getNetQty($bundleChildrenItem), $rmaItem->getQtyRequested() * $qtyPerBundle), $isDecimalFormat));

                    $sku = str_replace($skuPrefix, '', $bundleChildrenItem->getSku());
                    $itemNsamt = $itemSubtotal;
                    $itemDcamt = $itemDiscountAmount;
                    $itemSlamt = $itemSubtotal - $itemDiscountAmount - $this->roundingPrice($mileagePerItem, $isDecimalFormat);
                    $itemNetwr = $itemSubtotal - $itemDiscountAmount - $this->roundingPrice($mileagePerItem, $isDecimalFormat) - $itemTaxAmount;

                    if ($websiteCode == 'vn_laneige_website') {
                        $item = $this->searchOrderItem($orderAllItems, $bundleChildrenItem->getSku(), $itemId);
                        $itemNsamt = $item->getData('sap_item_nsamt');
                        $itemDcamt = $item->getData('sap_item_dcamt');
                        $itemSlamt = $item->getData('sap_item_slamt');
                        $itemNetwr = $item->getData('sap_item_netwr');
                    }

                    $rmaItemData[] = [
                        'itemVkorg' => $this->config->getSalesOrg('store', $storeId),
                        'itemKunnr' => $this->config->getClient('store', $storeId),
                        'itemOdrno' => "R" . $rma->getIncrementId(),
                        'itemPosnr' => $cnt,
                        'itemMatnr' => $sku,
                        'itemMenge' => intval($rmaItem->getQtyRequested() * $qtyPerBundle),
                        // 아이템 단위, Default : EA
                        'itemMeins' => $this->getMeins($meins),
                        'itemNsamt' => $itemNsamt,
                        'itemDcamt' => $itemDcamt,
                        'itemSlamt' => $itemSlamt,
                        'itemMiamt' => $mileagePerItem,
                        // 상품이 무상제공인 경우 Y 아니면 N
                        'itemFgflg' => $itemSlamt == 0 ? 'Y' : 'N',
                        'itemMilfg' => empty($mileageUsedAmount) ? 'N' : 'Y',
                        'itemAuart' => self::RETURN_ORDER,
                        'itemAugru' => self::AUGRU_RETURN_CODE,
                        'itemAbrvw' => self::ABRVW_RETURN_CODE,
                        'itemNetwr' => $itemNetwr,
                        'itemMwsbp' => $itemTaxAmount,
                        'itemVkorgOri' => $this->config->getSalesOrg('store', $storeId),
                        'itemKunnrOri' => $this->config->getClient('store', $storeId),
                        'itemOdrnoOri' => $order->getIncrementId(),
                        'itemPosnrOri' => $originPosnr[$bundleChildrenItem->getItemId()]
                    ];

                    $cnt++;
                    $itemsSubtotal += $itemSubtotal;
                    $itemsGrandTotal += ($itemSubtotal - $itemDiscountAmount - $this->roundingPrice($mileagePerItem, $isDecimalFormat));
                    $itemsGrandTotalInclTax += ($itemSubtotal - $itemDiscountAmount - $this->roundingPrice($mileagePerItem, $isDecimalFormat) - $itemTaxAmount);
                    $itemsDiscountAmount += $itemDiscountAmount;

                    $qtyPerBundle = $bundleChildrenItem->getQtyOrdered() / $orderItem->getQtyOrdered();
                    $itemsMileage += $this->roundingPrice($this->getRateAmount($mileagePerItem, $this->getNetQty($bundleChildrenItem), $rmaItem->getQtyRequested() * $qtyPerBundle), $isDecimalFormat);
                }
            }
        }
        $orderSubtotal = $this->roundingPrice($this->getRmaSubtotalInclTax($rma) + $this->getBundleExtraAmount($rma) + $this->getCatalogRuleDiscountAmount($rma), $isDecimalFormat);
        $orderGrandTotal = $order->getGrandTotal() == 0 ? $order->getGrandTotal() : $this->roundingPrice($this->getRmaGrandTotal($rma, $orderTotal, $pointUsed), $isDecimalFormat);
        $orderDiscountAmount = $this->roundingPrice($this->getRmaDiscountAmount($rma, $isDecimalFormat), $isDecimalFormat);

        if ($websiteCode != 'vn_laneige_website') {
            $rmaItemData = $this->priceCorrector($orderSubtotal, $itemsSubtotal, $rmaItemData, 'itemNsamt', $isDecimalFormat);
            $rmaItemData = $this->priceCorrector($orderGrandTotal, $itemsGrandTotalInclTax, $rmaItemData, 'itemSlamt', $isDecimalFormat);
            $rmaItemData = $this->priceCorrector($orderGrandTotal, $itemsGrandTotal, $rmaItemData, 'itemNetwr', $isDecimalFormat);
            $rmaItemData = $this->priceCorrector($orderDiscountAmount, $itemsDiscountAmount, $rmaItemData, 'itemDcamt', $isDecimalFormat);
        }
        $rmaItemData = $this->priceCorrector($mileageUsedAmount, $itemsMileage, $rmaItemData, 'itemMiamt', $isDecimalFormat);

        if ($isDecimalFormat) {
            $listToFormat = ['itemNsamt', 'itemSlamt', 'itemNetwr', 'itemDcamt', 'itemMiamt'];

            foreach ($listToFormat as $field) {
                foreach ($rmaItemData as $key => $value) {
                    if (isset($value[$field]) && (is_float($value[$field]) || is_int($value[$field]))) {
                        $rmaItemData[$key][$field] = $this->formatPrice($value[$field], $isDecimalFormat);
                    }
                }
            }
        }

        return $rmaItemData;
    }

    /**
     * Get proportion of child in 1 bundle
     *
     * @param \Magento\Sales\Api\Data\OrderItemInterface $orderItem
     * @param \Magento\Sales\Model\Order\Item $bundleChild
     * @param float $valueToCalculate
     * @return float|int
     * @throws NoSuchEntityException
     */
    public function getProportionOfBundleChild($orderItem, $bundleChild, $valueToCalculate)
    {
        $originalPriceSum = $this->getSumOfChildrenOriginPrice($orderItem);

        $bundleChildPrice = $this->productRepository->get($bundleChild->getSku(), false, $orderItem->getStoreId())->getPrice();
        //get rate for product per bundle
        $rate = ($bundleChildPrice / $originalPriceSum) * ($bundleChild->getQtyOrdered() / $orderItem->getQtyOrdered());

        return $valueToCalculate * $rate;
    }

    /**
     * Get total price for original price
     *
     * @param \Magento\Sales\Api\Data\OrderItemInterface $orderItem
     * @return float|null
     * @throws NoSuchEntityException
     * @throws \Exception
     */
    public function getSumOfChildrenOriginPrice(Item $orderItem)
    {
        $originalPriceSum = 0;

        $childrenItems = $orderItem->getChildrenItems();
        foreach ($childrenItems as $childItem) {
            $originalProductPrice = $this->productRepository->get($childItem->getSku(), false, $orderItem->getStoreId())->getPrice();
            //total original price product per bundle
            $originalPriceSum += ($originalProductPrice * ($childItem->getQtyOrdered() / $orderItem->getQtyOrdered()));
        }
        return $originalPriceSum;
    }

    /**
     * @param $order \Magento\Sales\Model\Order
     */
    public function kunweCheck($order)
    {
        $kunwe = $this->config->getHomeDeliveryContractor('store', $order->getStoreId());
        if ($this->cvsShippingCheck($order)) {
            try {
                $shippingAddress = $order->getShippingAddress();
                $cvsLocationId = $shippingAddress->getData('cvs_location_id');
                $cvsStoreData = $this->quoteCvsLocationRepository->getById($cvsLocationId);
                $cvsType = $cvsStoreData->getLogisticsSubType();
            } catch (NoSuchEntityException $e) {
                // when cvs address data is missing, use default value.
                // It does not matter anyway because it is not real customer address and we don't know the real cvs address fo return.
                $cvsType = 'FAMI';
            }
            if ($cvsType == 'FAMI') {
                $kunwe = $this->config->getFamilyMartCode('store', $order->getStoreId());
            } else {
                $kunwe = $this->config->getSevenElevenCode('store', $order->getStoreId());
            }
        }
        return $kunwe;
    }

    /**
     * @param \Magento\Rma\Model\Rma $rma
     */
    public function calculateItems($rma)
    {
        $itemCount = 0;
        foreach ($rma->getItems() as $item) {
            $orderItem = $this->orderItemRepository->get($item->getOrderItemId());
            if ($orderItem->getProductType() == 'bundle') {
                foreach ($orderItem->getChildrenItems() as $childrenItem) {
                    $itemCount++;
                }
            } else {
                $itemCount++;
            }
        }
        return $itemCount;
    }

    public function priceCorrector($orderAmount, $itemsAmount, $orderItemData, $field, $isDecimalFormat = false)
    {
        if ($orderAmount != $itemsAmount) {
            $correctAmount = $orderAmount - $itemsAmount;

            foreach ($orderItemData as $key => $value) {
                if ($value['itemFgflg'] == 'Y') {
                    continue;
                }
                $orderItemData[$key][$field] = $this->formatPrice($value[$field] + $correctAmount, $isDecimalFormat);
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
        $cvsAddress = '.';
        try {
            $cvsStoreData = $this->quoteCvsLocationRepository->getById($cvsLocationId);
            $cvsAddress = $cvsStoreData->getCvsAddress() . ' ' . $cvsStoreData->getCvsStoreName() . ' ' . $cvsStoreData->getLogisticsSubType();
        } catch (NoSuchEntityException $e) {
            //if order is older than 30days, cvs address might not exists.
        }

        return $cvsAddress;
    }

    public function dateFormatting($date, $format)
    {
        return $this->timezoneInterface->date($date)->format($format);
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

    public function mileageSpentRateByItem($orderTotal, $itemRowTotal, $itemDiscountAmount, $mileageUsed, $isDecimalFormat = false)
    {
        $itemTotal = round($itemRowTotal - $itemDiscountAmount, 2);

        if ($mileageUsed) {
            return $this->roundingPrice(($itemTotal/$orderTotal) * $mileageUsed, $isDecimalFormat);
        }
        return is_null($mileageUsed) ? '0' : $mileageUsed;
    }

    /**
     * @param $orderItem \Magento\Sales\Api\Data\OrderItemInterface
     */
    public function productTypeCheck($orderItem)
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
     * Get rma grand total
     *
     * @param \Magento\Rma\Model\Rma $rma
     * @param $orderTotal
     * @param $pointsUsed
     * @return int
     */
    public function getRmaGrandTotal($rma, $orderTotal, $pointsUsed)
    {
        $grandTotal = 0;
        $rmaItems = $rma->getItems();
        $order = $rma->getOrder();
        $isDecimalFormat = $this->middlewareHelper->getIsDecimalFormat('store', $order->getStoreId());

        $mileageUsedAmount = $order->getRewardPointsBalance();

        $storeId = $order->getStoreId();
        if($this->amConfig->isEnabled($storeId)) {
            $rewardPoints = (int)$order->getData('am_spent_reward_points');
            $spendingRate = $this->amConfig->getPointsRate($storeId);
            if (!$spendingRate) {
                $spendingRate = 1;
            }
            $mileageUsedAmount = $rewardPoints / $spendingRate;
        }
        foreach ($rmaItems as $rmaItem) {
            $orderItem = $this->orderItemRepository->get($rmaItem->getOrderItemId());
            if ($orderItem->getProductType() == 'bundle') {
                $bundleProduct = $this->productRepository->getById($orderItem->getProductId());
                $bundlePriceType = $bundleProduct->getPriceType();

                foreach ($orderItem->getChildrenItems() as $bundleChild) {
                    $bundleChildDiscountAmount = (int)$bundlePriceType !== \Magento\Bundle\Model\Product\Price::PRICE_TYPE_DYNAMIC ?
                        $this->getProportionOfBundleChild($orderItem, $bundleChild, $orderItem->getDiscountAmount()) :
                        $bundleChild->getDiscountAmount();
                    $mileagePerItem = $this->mileageSpentRateByItem(
                        $orderTotal,
                        $this->getProportionOfBundleChild($orderItem, $bundleChild, $orderItem->getRowTotalInclTax()),
                        $bundleChildDiscountAmount,
                        $mileageUsedAmount,
                        $isDecimalFormat);
                    $itemGrandTotalInclTax = $this->getProportionOfBundleChild($orderItem, $bundleChild, $orderItem->getRowTotalInclTax())
                        - $bundleChildDiscountAmount
                        - $mileagePerItem;

                    $qtyPerBundle = $bundleChild->getQtyOrdered() / $orderItem->getQtyOrdered();
                    $grandTotal += $this->getRateAmount($itemGrandTotalInclTax, $this->getNetQty($bundleChild), $rmaItem->getQtyRequested() * $qtyPerBundle);
                }
            } else {
                $mileagePerItem = $this->mileageSpentRateByItem(
                    $orderTotal,
                    $orderItem->getRowTotalInclTax(),
                    $orderItem->getDiscountAmount(),
                    $pointsUsed,
                    $isDecimalFormat
                );
                $itemGrandTotal = $orderItem->getRowTotal()
                    - $orderItem->getDiscountAmount()
                    - $mileagePerItem;

                $itemGrandTotal = $this->getRateAmount($itemGrandTotal, $this->getNetQty($orderItem), $rmaItem->getQtyRequested());
                $grandTotal += $this->getRateAmount($itemGrandTotal, $this->getNetQty($orderItem), $rmaItem->getQtyRequested());
            }
        }
        return $grandTotal;
    }

    /**
     * Get rma discount amount
     *
     * @param \Magento\Rma\Model\Rma $rma
     * @param int $isDecimalFormat
     * @return float|int
     * @throws NoSuchEntityException
     */
    public function getRmaDiscountAmount($rma, $isDecimalFormat)
    {
        $order = $rma->getOrder();
        $orderSubTotal = abs($this->roundingPrice(
            $order->getSubtotalInclTax() + $this->getBundleExtraAmount($rma) + $this->getCatalogRuleDiscountAmount($rma),
            $isDecimalFormat));
        $orderGrandTotal = $order->getGrandTotal() == 0 ? $order->getGrandTotal() : abs($this->roundingPrice($order->getGrandTotal() - $order->getShippingAmount(), $isDecimalFormat));

        return $orderSubTotal - $orderGrandTotal;
    }

    /**
     * Get discount amount for bundle child
     *
     * @param int $bundlePriceType
     * @param \Magento\Sales\Api\Data\OrderItemInterface $orderItem
     * @param \Magento\Sales\Model\Order\Item $bundleChild
     * @return float|int|null
     * @throws NoSuchEntityException
     */
    public function getDiscountAmountForBundleChild($bundlePriceType, $orderItem, $bundleChild)
    {
        $bundleChildDiscountAmount = (int)$bundlePriceType !== \Magento\Bundle\Model\Product\Price::PRICE_TYPE_DYNAMIC ?
            $this->getProportionOfBundleChild($orderItem, $bundleChild, $orderItem->getDiscountAmount()) :
            $bundleChild->getDiscountAmount();

        return $bundleChildDiscountAmount;
    }

    /**
     * Get catalog rule discount amount
     *
     * @param \Magento\Rma\Model\Rma $rma
     * @throws NoSuchEntityException
     * @throws \Exception
     */
    public function getCatalogRuleDiscountAmount($rma, $isDecimalFormat = false)
    {
        $catalogRuleDiscount = 0;
        $order = $rma->getOrder();
        $rmaItems = $rma->getItems();
        foreach ($rmaItems as $rmaItem) {
            $orderItem = $this->orderItemRepository->get($rmaItem->getOrderItemId());
            if ($orderItem->getProductType() != 'bundle') {
                if ($this->roundingPrice($orderItem->getPrice(), $isDecimalFormat)) {
                    $catalogRuleDiscount += ($orderItem->getOriginalPrice() - $orderItem->getPrice()) *
                        $rmaItem->getQtyRequested();
                }
            } else {
                /** @var \Magento\Catalog\Model\Product $bundleProduct */
                $bundleProduct = $this->productRepository->getById($orderItem->getProductId(), false, $order->getStoreId());
                $bundlePriceType = $bundleProduct->getPriceType();

                if ((int)$bundlePriceType !== \Magento\Bundle\Model\Product\Price::PRICE_TYPE_DYNAMIC) {
                    foreach ($orderItem->getChildrenItems() as $bundleChild) {
                        $qtyPerBundle = $bundleChild->getQtyOrdered() / $orderItem->getQtyOrdered();
                        $catalogRuledPriceRatio = $this->getProportionOfBundleChild($orderItem, $bundleChild, ($orderItem->getOriginalPrice() - $orderItem->getPrice())) / $qtyPerBundle;

                        $catalogRuleDiscount += $catalogRuledPriceRatio * $rmaItem->getQtyRequested() * $qtyPerBundle;
                    }
                } else {
                    foreach ($orderItem->getChildrenItems() as $bundleChild) {
                        $catalogRuledPriceRatio = $bundleChild->getOriginalPrice() - $bundleChild->getPrice();
                        $catalogRuleDiscount += $catalogRuledPriceRatio * $bundleChild->getQtyOrdered();
                    }
                }
            }
        }
        return $catalogRuleDiscount;
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
            $subtotalInclTax += ($orderItem->getPrice() * $rmaItem->getQtyRequested());
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
        $order = $rma->getOrder();
        $isDecimalFormat = $this->middlewareHelper->getIsDecimalFormat('store', $order->getStoreId());

        foreach ($rmaItems as $rmaItem) {
            $orderItem = $this->orderItemRepository->get($rmaItem->getOrderItemId());
            $mileagePerItem = $this->mileageSpentRateByItem(
                $orderTotal,
                $orderItem->getRowTotalInclTax(),
                $orderItem->getDiscountAmount(),
                $pointsUsed,
                $isDecimalFormat
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
        $netQty = $orderItem->getQtyOrdered() - $orderItem->getQtyRefunded() - $orderItem->getQtyReturned();
        // When order item has been refunded before creating return, Net qty will be 0, it will cause an issue when calculating discount amount, ...
        // Since our sites don't allow partial refund, so we can ignore refunded qty when calculating net qty
        if (0 == $netQty) {
            $netQty = $orderItem->getQtyOrdered() - $orderItem->getQtyReturned();
        }
        return $netQty;
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
     * @param $price
     * @param $isDecimal
     * @return float|string
     */
    public function formatPrice($price, $isDecimal = false)
    {
        if ($isDecimal) {
            return number_format($price, 2, '.', '');
        }
        return $price;
    }

    /**
     * @param $price
     * @param $isDecimal
     * @return float
     */
    public function roundingPrice($price, $isDecimal = false)
    {
        $precision = $isDecimal ? 2 : 0;
        return round($price, $precision);
    }

    /**
     * get bundle extra amount
     *
     * @param $rma
     * @return float|int
     * @throws NoSuchEntityException
     */
    public function getBundleExtraAmount($rma)
    {
        $priceDifferences = 0;
        $rmaItems = $rma->getItems();
        $order = $rma->getOrder();
        foreach ($rmaItems as $rmaItem) {
            $orderItem = $this->orderItemRepository->get($rmaItem->getOrderItemId());
            if ($orderItem->getProductType() == 'bundle') {
                /** @var \Magento\Catalog\Model\Product $bundleProduct */
                $bundleProduct = $this->productRepository->getById($orderItem->getProductId(), false, $order->getStoreId());
                $bundlePriceType = $bundleProduct->getPriceType();

                if ((int)$bundlePriceType !== \Magento\Bundle\Model\Product\Price::PRICE_TYPE_DYNAMIC) {
                    foreach ($orderItem->getChildrenItems() as $bundleChild) {
                        $qtyProductPerBundle = $bundleChild->getQtyOrdered() / $orderItem->getQtyOrdered();
                        $childPriceRatio = $this->getProportionOfBundleChild($orderItem, $bundleChild, $orderItem->getOriginalPrice()) / $qtyProductPerBundle;
                        $originItemPrice = $this->productRepository->get($bundleChild->getSku(), false, $order->getStoreId())->getPrice();

                        $priceDifferences += (($originItemPrice - $childPriceRatio) * $bundleChild->getQtyOrdered());
                    }
                }
            }
        }
        return $priceDifferences;
    }
}
