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
     * @param ProductRepositoryInterface $productRepository
     * @param AttributeRepositoryInterface $eavAttributeRepositoryInterface
     * @param \Magento\Bundle\Api\ProductLinkManagementInterface $productLinkManagement
     * @param StoreManagerInterface $storeManager
     * @param Data $helper
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
        Data $helper
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
        $orderTotal = round($order->getSubtotalInclTax() + $order->getDiscountAmount() + $order->getShippingAmount());
        $trackData = $this->getTracks($rma);
        $ztrackId = $trackData['track_number'] ?? '';
        $shippingMethod = $order->getShippingMethod();

        $websiteId = (int)$this->storeManager->getStore($storeId)->getWebsiteId();
        $websiteCode = $this->storeManager->getWebsite($websiteId)->getCode();
        if ($websiteCode == 'vn_laneige_website') {
            $paymtd = 10;
            $nsamt  = $order->getData('sap_nsamt');
            $dcamt  = $order->getData('sap_dcamt');
            $slamt  = $order->getData('sap_slamt');
        } else {
            $paymtd = $order->getPayment()->getMethod() == 'ecpay_ecpaypayment' ? 'P' : 'S';
            $nsamt  = abs(round($this->getRmaSubtotalInclTax($rma)));
            $dcamt  = abs(round($this->getRmaDiscountAmount($rma)));
            $slamt = $order->getGrandTotal() == 0 ? $order->getGrandTotal() :
                abs(round($this->getRmaGrandTotal($rma, $orderTotal, $pointUsed)));
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
            'miamt' => abs(round($this->getRmaPointsUsed($rma, $pointUsed, $orderTotal))),
            'shpwr' => '',
            'mwsbp' => round($order->getTaxAmount()),
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
            'ztrackId' => $ztrackId
        ];

        return $bindData;
    }

    /**
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
        $orderTotal = round($order->getSubtotalInclTax() + $order->getDiscountAmount() + $order->getShippingAmount());
        $mileageUsedAmount = $order->getRewardPointsBalance();
        $originPosnr = $this->getOrderItemPosnr($rma);
        $pointUsed = $order->getRewardPointsBalance();

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
                    $mileageUsedAmount
                );
                $itemGrandTotal = $orderItem->getRowTotal()
                    - $orderItem->getDiscountAmount()
                    - $mileagePerItem;
                $itemGrandTotalInclTax = $orderItem->getRowTotalInclTax()
                    - $orderItem->getDiscountAmount()
                    - $mileagePerItem;

                $product = $this->productRepository->get($rmaItem->getProductSku());
                $meins = $product->getData('meins');

                $itemSubtotal = abs(round($orderItem->getPrice() * $rmaItem->getQtyRequested()));
                if (round($orderItem->getPrice())) {
                    $itemTotalDiscount = abs(round($this->getRateAmount($orderItem->getDiscountAmount(), $orderItem->getQtyOrdered(), $rmaItem->getQtyRequested())));
                } else {
                    $itemTotalDiscount = 0;
                }
                $itemMileageUsed = abs(round($this->getRateAmount($mileagePerItem, $this->getNetQty($orderItem), $rmaItem->getQtyRequested())));
                $itemGrandTotalValue = abs(round($this->getRateAmount($itemGrandTotal, $this->getNetQty($orderItem), $rmaItem->getQtyRequested())));
                $itemTaxAmount = abs(round($this->getRateAmount($orderItem->getTaxAmount(), $this->getNetQty($orderItem), $rmaItem->getQtyRequested())));

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
                    'itemFgflg' => $orderItem->getOriginalPrice() == 0 ? 'Y' : 'N',
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
                $itemsMileage += round($this->getRateAmount($mileagePerItem, $this->getNetQty($orderItem), $rmaItem->getQtyRequested()));
            } else {
                /** @var \Magento\Catalog\Model\Product $bundleProduct */
                $bundleProduct = $this->productRepository->getById($orderItem->getProductId());
                $bundleChildren = $this->getBundleChildren($orderItem->getSku(), $orderItem->getStoreId());
                $bundlePriceType = $bundleProduct->getPriceType();

                foreach ($bundleChildren as $bundleChildrenItem) {
                    $itemId = $rmaItem->getOrderItemId();
                    $bundleChildFromOrder = $this->getBundleChildFromOrder($itemId, $bundleChildrenItem->getSku());
                    $bundleChildItemPrice = $this->getProportionOfBundleChild($orderItem, $bundleChildrenItem, $orderItem->getPrice());

                    $bundleChildDiscountAmount = (int)$bundlePriceType !== \Magento\Bundle\Model\Product\Price::PRICE_TYPE_DYNAMIC ?
                        $this->getProportionOfBundleChild($orderItem, $bundleChildrenItem, $orderItem->getDiscountAmount()) :
                        $bundleChildFromOrder->getDiscountAmount();
                    $mileagePerItem = $this->mileageSpentRateByItem(
                        $orderTotal,
                        $this->getProportionOfBundleChild($orderItem, $bundleChildrenItem, $orderItem->getRowTotalInclTax()),
                        $bundleChildDiscountAmount,
                        $mileageUsedAmount);
                    $product = $this->productRepository->get($bundleChildrenItem->getSku(), false, $rma->getStoreId());
                    $meins = $product->getData('meins');
                    $itemDiscountAmountPerQty = $bundleChildDiscountAmount / $bundleChildrenItem->getQty();
                    $itemDiscountAmount = abs(round($itemDiscountAmountPerQty * $rmaItem->getQtyRequested()));
                    $itemSubtotal = abs(round($bundleChildItemPrice * $rmaItem->getQtyRequested() * $bundleChildrenItem->getQty()));
                    $itemTaxAmount = abs(round($this->getRateAmount($bundleChildrenItem->getTaxAmount(), $this->getNetQty($bundleChildFromOrder), $rmaItem->getQtyRequested() * $bundleChildrenItem->getQty())));

                    $sku = str_replace($skuPrefix, '', $bundleChildrenItem->getSku());
                    $itemNsamt = $itemSubtotal;
                    $itemDcamt = $itemDiscountAmount;
                    $itemSlamt = $itemSubtotal - $itemDiscountAmount - round($mileagePerItem);
                    $itemNetwr = $itemSubtotal - $itemDiscountAmount - round($mileagePerItem) - $itemTaxAmount;

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
                        'itemMenge' => intval($rmaItem->getQtyRequested() * $bundleChildrenItem->getQty()),
                        // 아이템 단위, Default : EA
                        'itemMeins' => $this->getMeins($meins),
                        'itemNsamt' => $itemNsamt,
                        'itemDcamt' => $itemDcamt,
                        'itemSlamt' => $itemSlamt,
                        'itemMiamt' => $mileagePerItem,
                        // 상품이 무상제공인 경우 Y 아니면 N
                        'itemFgflg' => $product->getPrice() == 0 ? 'Y' : 'N',
                        'itemMilfg' => empty($mileageUsedAmount) ? 'N' : 'Y',
                        'itemAuart' => self::RETURN_ORDER,
                        'itemAugru' => self::AUGRU_RETURN_CODE,
                        'itemAbrvw' => self::ABRVW_RETURN_CODE,
                        'itemNetwr' => $itemNetwr,
                        'itemMwsbp' => $itemTaxAmount,
                        'itemVkorgOri' => $this->config->getSalesOrg('store', $storeId),
                        'itemKunnrOri' => $this->config->getClient('store', $storeId),
                        'itemOdrnoOri' => $order->getIncrementId(),
                        'itemPosnrOri' => $originPosnr[$this->getBundleChildFromOrder($itemId, $bundleChildrenItem->getSku())->getItemId()]
                    ];

                    $cnt++;
                    $itemsSubtotal += $itemSubtotal;
                    $itemsGrandTotal += ($itemSubtotal - $itemDiscountAmount - round($mileagePerItem));
                    $itemsGrandTotalInclTax += ($itemSubtotal - $itemDiscountAmount - round($mileagePerItem) - $itemTaxAmount);
                    $itemsDiscountAmount += $itemDiscountAmount;

                    $itemsMileage += round($this->getRateAmount($mileagePerItem, $this->getNetQty($bundleChildFromOrder), $rmaItem->getQtyRequested() * $bundleChildrenItem->getQty()));
                }
            }
        }
        $orderSubtotal = round($this->getRmaSubtotalInclTax($rma));
        $orderGrandTotal = $order->getGrandTotal() == 0 ? $order->getGrandTotal() : round($this->getRmaGrandTotal($rma, $orderTotal, $pointUsed));
        $orderDiscountAmount = round($this->getRmaDiscountAmount($rma));

        if ($websiteCode != 'vn_laneige_website') {
            $rmaItemData = $this->priceCorrector($orderSubtotal, $itemsSubtotal, $rmaItemData, 'itemNsamt');
            $rmaItemData = $this->priceCorrector($orderGrandTotal, $itemsGrandTotalInclTax, $rmaItemData, 'itemSlamt');
            $rmaItemData = $this->priceCorrector($orderGrandTotal, $itemsGrandTotal, $rmaItemData, 'itemNetwr');
            $rmaItemData = $this->priceCorrector($orderDiscountAmount, $itemsDiscountAmount, $rmaItemData, 'itemDcamt');
        }
        $rmaItemData = $this->priceCorrector($mileageUsedAmount, $itemsMileage, $rmaItemData, 'itemMiamt');

        return $rmaItemData;
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

    public function getBundleChildFromOrder($itemId, $bundleChildSku)
    {
        $bundleChild = null;
        /** @var \Magento\Sales\Model\Order\Item $itemOrdered */
        $itemOrdered = $this->orderItemRepository->get($itemId);
        $childrenItems = $itemOrdered->getChildrenItems();
        /** @var \Magento\Sales\Model\Order\Item $childItem */
        foreach ($childrenItems as $childItem) {
            if ($childItem->getSku() == $bundleChildSku) {
                $bundleChild = $childItem;
                break;
            }
        }
        return $bundleChild;
    }


    /**
     * @param \Magento\Sales\Api\Data\OrderItemInterface $orderItem
     * @param \Magento\Bundle\Api\Data\LinkInterface $bundleChild
     * @param float $valueToCalculate
     * @return float|int
     * @throws NoSuchEntityException
     */
    public function getProportionOfBundleChild($orderItem, $bundleChild, $valueToCalculate)
    {
        $originalPriceSum = $this->getSumOfChildrenOriginPrice($orderItem);

        $bundleChildPrice = $this->productRepository->get($bundleChild->getSku(), false, $orderItem->getStoreId())->getPrice();
        $rate = ($bundleChildPrice * $bundleChild->getQty()) / $originalPriceSum;

        return $valueToCalculate * $rate;
    }

    /**
     * @param \Magento\Sales\Api\Data\OrderItemInterface $orderItem
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

        $mileageUsedAmount = $order->getRewardPointsBalance();

        foreach ($rmaItems as $rmaItem) {
            $orderItem = $this->orderItemRepository->get($rmaItem->getOrderItemId());
            if ($orderItem->getProductType() == 'bundle') {

                $bundleProduct = $this->productRepository->getById($orderItem->getProductId());
                $bundleChildren = $this->getBundleChildren($orderItem->getSku(), $orderItem->getStoreId());
                $bundlePriceType = $bundleProduct->getPriceType();

                foreach ($bundleChildren as $bundleChild) {
                    $itemId = $rmaItem->getOrderItemId();
                    $bundleChildFromOrder = $this->getBundleChildFromOrder($itemId, $bundleChild->getSku());
                    $bundleChildDiscountAmount = (int)$bundlePriceType !== \Magento\Bundle\Model\Product\Price::PRICE_TYPE_DYNAMIC ?
                        $this->getProportionOfBundleChild($orderItem, $bundleChild, $orderItem->getDiscountAmount()) :
                        $this->getBundleChildFromOrder($itemId, $bundleChild->getSku())->getDiscountAmount();
                    $mileagePerItem = $this->mileageSpentRateByItem(
                        $orderTotal,
                        $this->getProportionOfBundleChild($orderItem, $bundleChild, $orderItem->getRowTotalInclTax()),
                        $bundleChildDiscountAmount,
                        $mileageUsedAmount);
                    $itemGrandTotalInclTax = $this->getProportionOfBundleChild($orderItem, $bundleChild, $orderItem->getRowTotalInclTax())
                        - $bundleChildDiscountAmount
                        - $mileagePerItem;

                    $grandTotal += $this->getRateAmount($itemGrandTotalInclTax, $this->getNetQty($bundleChildFromOrder), $rmaItem->getQtyRequested() * $bundleChild->getQty());
                }
            } else {
                $mileagePerItem = $this->mileageSpentRateByItem(
                    $orderTotal,
                    $orderItem->getRowTotalInclTax(),
                    $orderItem->getDiscountAmount(),
                    $pointsUsed
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
     * @param \Magento\Rma\Model\Rma $rma
     */
    public function getRmaDiscountAmount($rma)
    {
        $discountAmount = 0;
        $rmaItems = $rma->getItems();
        foreach ($rmaItems as $rmaItem) {
            $orderItem = $this->orderItemRepository->get($rmaItem->getOrderItemId());
            if ($orderItem->getProductType() == 'bundle') {
                $bundleProduct = $this->productRepository->getById($orderItem->getProductId());
                $bundleChildren = $this->getBundleChildren($orderItem->getSku(), $orderItem->getStoreId());
                $bundlePriceType = $bundleProduct->getPriceType();
                foreach ($bundleChildren as $bundleChild) {
                    $bundleChildFromOrder = $this->getBundleChildFromOrder($rmaItem->getOrderItemId(), $bundleChild->getSku());
                    $bundleChildDiscountAmount = $this->getDiscountAmountForBundleChild($bundlePriceType, $orderItem, $bundleChild);
                    $discountAmount += ($bundleChildDiscountAmount * $rmaItem->getQtyRequested() * $bundleChild->getQty() / $this->getNetQty($bundleChildFromOrder));
                }
            } else {
                $discountAmount += ($orderItem->getDiscountAmount() * $rmaItem->getQtyRequested() / $this->getNetQty($orderItem));
            }
        }
        return $discountAmount;
    }

    public function getDiscountAmountForBundleChild($bundlePriceType, $orderItem, $bundleChild)
    {
        $bundleChildDiscountAmount = (int)$bundlePriceType !== \Magento\Bundle\Model\Product\Price::PRICE_TYPE_DYNAMIC ?
            $this->getProportionOfBundleChild($orderItem, $bundleChild, $orderItem->getDiscountAmount()) :
            $this->getBundleChildFromOrder($orderItem->getItemId(), $bundleChild->getSku())->getDiscountAmount();

        return $bundleChildDiscountAmount;
    }

    public function getBundleChildPrice($bundlePriceType, $orderItem, $bundleChild)
    {
        if ((int)$bundlePriceType !== \Magento\Bundle\Model\Product\Price::PRICE_TYPE_DYNAMIC) {
            $bundleChildPrice = $this->productRepository->get($bundleChild->getSku(), false, $orderItem->getStoreId())->getPrice();
        } else {
            $bundleChildPrice = $this->getBundleChildFromOrder($orderItem->getItemId(), $bundleChild->getSku())->getPrice();
        }
        return $bundleChildPrice;
    }

    /**
     * @param \Magento\Rma\Model\Rma $rma
     * @throws NoSuchEntityException
     * @throws \Exception
     */
    public function getCatalogRuleDiscountAmount($rma)
    {
        $catalogRuleDiscount = 0;
        $order = $rma->getOrder();
        $rmaItems = $rma->getItems();
        foreach ($rmaItems as $rmaItem) {
            $orderItem = $this->orderItemRepository->get($rmaItem->getOrderItemId());
            if ($orderItem->getProductType() != 'bundle') {
                if (round($orderItem->getPrice())) {
                    $catalogRuleDiscount += ($orderItem->getOriginalPrice() - $orderItem->getPrice()) *
                        $rmaItem->getQtyRequested();
                }
            } else {
                /** @var \Magento\Catalog\Model\Product $bundleProduct */
                $bundleProduct = $this->productRepository->getById($orderItem->getProductId(), false, $order->getStoreId());
                $bundleChildren = $this->getBundleChildren($orderItem->getSku(), $orderItem->getStoreId());
                $bundlePriceType = $bundleProduct->getPriceType();

                if ((int)$bundlePriceType !== \Magento\Bundle\Model\Product\Price::PRICE_TYPE_DYNAMIC) {
                    foreach ($bundleChildren as $bundleChild) {
                        $catalogRuledPriceRatio = $this->getProportionOfBundleChild($orderItem, $bundleChild, ($orderItem->getOriginalPrice() - $orderItem->getPrice())) / $bundleChild->getQty();

                        $catalogRuleDiscount += $catalogRuledPriceRatio * $rmaItem->getQtyRequested() * $bundleChild->getQty();
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
}
