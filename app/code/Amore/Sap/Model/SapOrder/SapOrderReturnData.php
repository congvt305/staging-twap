<?php
/**
 * Created by Eguana.
 * User: Brian
 * Date: 2020-07-22
 * Time: 오후 8:14
 */

namespace Amore\Sap\Model\SapOrder;

use Amore\Sap\Exception\RmaTrackNoException;
use CJ\Middleware\Model\Product\Bundle\CalculatePrice;
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

    private $rmaItemData = [];


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
     * @var \CJ\Middleware\Model\Product\CalculatePrice
     */
    private $productCalculatePrice;

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
     * @param \CJ\Middleware\Model\Product\CalculatePrice $productCalculatePrice
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
        \CJ\Middleware\Model\Product\CalculatePrice $productCalculatePrice,
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
        parent::__construct(
            $searchCriteriaBuilder, $orderRepository,
            $storeRepository, $config, $quoteCvsLocationRepository,
            $eavAttributeRepositoryInterface, $logger, $orderData
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
        $this->resetData();
        $source = $this->config->getSourceByStore('store', $rma->getStoreId());
        $rmaItemData = $this->getRmaItemData($rma);
        $rmaData = $this->getRmaData($rma);

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
        $quantityRatioForPartial = $this->getQuantityRatioForPartial($rma);
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
            $orderSubTotal = $order->getSubtotalInclTax();
            if ($this->middlewareHelper->getIsIncludeShippingAmountWhenSendRequest($storeId)) {
                $orderSubTotal += $order->getShippingAmount();
            }
            $nsamt  = $this->orderData->roundingPrice($orderSubTotal, $isDecimalFormat);
            $dcamt  = abs($this->orderData->roundingPrice($order->getDiscountAmount(), $isDecimalFormat)) - $miamt;
            $slamt = $nsamt - $dcamt;
        }


        $cvsShippingCheck = $this->cvsShippingCheck($order);
        $telephone = $this->getTelephone($shippingAddress->getTelephone());
        $salesOrg = $this->middlewareHelper->getSalesOrganizationCode('store', $storeId);
        $client = $this->config->getClient('store', $storeId);

        //Case partial return force assign total price to order price
        if ($quantityRatioForPartial < 1) {
            $nsamt = $this->itemsSubtotal;
            $dcamt = $this->itemsDiscountAmount;
            $miamt = $this->itemsMileage;
            $slamt = $nsamt - $dcamt;
        }

        $isMileageOrder = ($slamt == $miamt && $slamt > 0);
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
            'itemCnt' => $this->getTotalItemReturn($rma),
            // 영업 플랜트 : 알수 없을 경우 공백
            'werks' => '',
            // 영업저장위치 : 알수 없을 경우 공백
            'lgort' => '',
            'rmano' => '',
            // 납품처
            'kunwe' => $this->kunweCheck($order),
            // trackNo 가져와야 함
            'ztrackId' => $ztrackId,
            'redemptionFlag' => $isMileageOrder ? 'Y' : 'N',
            'PointAccount' => $rewardPoints
        ];

        if ($isDecimalFormat) {
            $listToFormat = ['nsamt', 'dcamt', 'slamt', 'miamt', 'shpwr', 'mwsbp'];
            foreach ($bindData[0] as $k => $value) {
                if (in_array($k, $listToFormat) && (is_float($value) || is_int($value))) {
                    $bindData[0][$k] = $this->orderData->formatPrice($value, $isDecimalFormat);
                }
            }
        }
        array_walk_recursive($bindData, [$this, 'convertNumberToString']);
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
                if ($rewardPoints) {
                    $mileageUsedAmount = $listOptions[$rewardPoints] ?? 0;
                }
            } else {
                $mileageUsedAmount = $rewardPoints / $spendingRate;
            }
            $mileageUsedAmountExisted = $mileageUsedAmount;
        }
        $quantityRatioForPartial = $this->getQuantityRatioForPartial($rma);

        /** @var \Magento\Rma\Model\Item $rmaItem */
        foreach ($rmaItems as $rmaItem) {
            /** @var \Magento\Sales\Model\Order\Item $orderItem */
            $orderItem = $order->getItemById($rmaItem->getOrderItemId());
            $quantityRatioPerItemPartial = $rmaItem->getQtyRequested() / $orderItem->getQtyOrdered();
            if ($orderItem->getProductType() != 'bundle') {
                $itemDcamt = $this->orderData->roundingPrice($orderItem->getData('sap_item_dcamt') * $quantityRatioPerItemPartial, $isDecimalFormat);
                $itemNsamt = $this->orderData->roundingPrice($orderItem->getData('sap_item_nsamt') * $quantityRatioPerItemPartial, $isDecimalFormat);
                $itemSlamt = $itemNsamt - $itemDcamt; // have to do this to correct price among Nsamt, Dcamt and Slamt
                $itemMiamt = $this->orderData->roundingPrice($orderItem->getData('sap_item_miamt') * $quantityRatioPerItemPartial, $isDecimalFormat);
                $itemTaxAmount = $this->orderData->roundingPrice($orderItem->getData('sap_item_mwsbp') * $quantityRatioPerItemPartial, $isDecimalFormat);
                $itemNetwr = $itemSlamt - $itemMiamt; // have to do this to correct price among Nsamt, Dcamt, Slamt and Netwr


                if($isEnableRewardsPoint) {
                    if ($mileageUsedAmountExisted > $itemMiamt) {
                        $mileageUsedAmountExisted -= $itemMiamt;
                    } else {
                        $itemMiamt = $mileageUsedAmountExisted;
                        $mileageUsedAmountExisted = 0;
                    }
                }

                $this->addReturnOrderItemData(
                    $rma, $rmaItem, $itemNsamt,
                    $itemDcamt, $itemSlamt, $itemMiamt,
                    $itemNetwr, $itemTaxAmount, $originPosnr
                );
            }
        }

        if ($quantityRatioForPartial == 1) {
            // Full return
            $orderGrandTotal = $order->getGrandTotal() == 0 ? $order->getGrandTotal() : $this->orderData->roundingPrice($order->getGrandTotal(), $isDecimalFormat);
            $orderDiscountAmount = abs($this->orderData->roundingPrice($order->getDiscountAmount(), $isDecimalFormat)) - $mileageUsedAmount;
            if ($isEnableRewardsPoint && $mileageUsedAmountExisted) {
                $this->itemsGrandTotalInclTax -= $mileageUsedAmountExisted;
            }
            $orderShippingAmount = $this->orderData->roundingPrice($order->getShippingAmount() * $quantityRatioForPartial, $isDecimalFormat);

            if ($this->middlewareHelper->getIsIncludeShippingAmountWhenSendRequest($storeId)) {
                $orderSubtotal += $orderShippingAmount;
            } else {
                $orderGrandTotal -= $order->getShippingAmount();
            }
            $this->rmaItemData = $this->correctPriceOrderItemData($this->rmaItemData,
                $orderSubtotal, $orderDiscountAmount, $mileageUsedAmount, $orderGrandTotal, $isDecimalFormat
            );
        }
        array_walk_recursive($this->rmaItemData, [$this, 'convertNumberToString']);
        return $this->rmaItemData;
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
     * @return void
     * @throws NoSuchEntityException
     */
    private function addReturnOrderItemData(
        $rma, $rmaItem, $itemNsamt,
        $itemDcamt, $itemSlamt, $itemMiamt,
        $itemNetwr, $itemTaxAmount, $originPosnr
    ) {
        $sku = $rmaItem->getProductSku();
        $itemId = $rmaItem->getOrderItemId();

        $storeId = $rma->getStoreId();
        $order = $rma->getOrder();
        $product = $this->productRepository->get($sku, false, $rma->getStoreId());
        $skuPrefix = $this->config->getSapSkuPrefix($storeId);
        $skuPrefix = $skuPrefix ?: '';
        $sku = str_replace($skuPrefix, '', $sku);
        $meins = $product->getData('meins');
        $isMileageOrderItem = $itemSlamt > 0 && $itemSlamt == $itemMiamt;
        $salesOrg = $this->middlewareHelper->getSalesOrganizationCode('store', $storeId);
        $client = $this->config->getClient('store', $storeId);

        $this->rmaItemData[] = [
            'itemVkorg' => $salesOrg,
            'itemKunnr' => $client,
            'itemOdrno' => "R" . $rma->getIncrementId(),
            'itemPosnr' => $this->cnt,
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

        $this->cnt++;
        $this->itemsSubtotal += $itemNsamt;
        $this->itemsGrandTotal += ($itemNsamt - $itemDcamt - $itemMiamt);
        $this->itemsGrandTotalInclTax += ($itemNsamt - $itemDcamt - $itemMiamt - $itemTaxAmount);
        $this->itemsDiscountAmount += $itemDcamt;
        $this->itemsMileage += $itemMiamt;
    }

    /**
     * Reset rma data
     *
     * @return void
     */
    protected function resetData()
    {
        parent::resetData();
        $this->rmaItemData = [];
    }

    /**
     * Get quantity ratio for partial
     *
     * @param \Magento\Rma\Model\Rma $rma
     * @return int
     */
    private function getTotalItemReturn($rma) {
        $total = 0;
        $rmaItems = $rma->getItems();
        $order = $rma->getOrder();
        foreach ($rmaItems as $rmaItem) {
            $orderItem = $order->getItemById($rmaItem->getOrderItemId());
            if ($orderItem->getProductType() == 'bundle') {
                continue;
            }
            $total++;
        }
        return $total;
    }

    /**
     * Get quantity ratio for partial
     *
     * @param $rma
     * @return float|int
     */
    private function getQuantityRatioForPartial($rma)
    {
        $totalReturn = 0;
        $totalOrder = 0;
        $rmaItems = $rma->getItems();
        $order = $rma->getOrder();
        foreach ($rmaItems as $rmaItem) {
            $orderItem = $order->getItemById($rmaItem->getOrderItemId());
            if ($orderItem->getProductType() == 'bundle') {
                continue;
            }
            $totalReturn += $rmaItem->getQtyRequested();
        }

        foreach ($order->getAllVisibleItems() as $orderItem) {
            if ($orderItem->getProductType() != 'bundle') {
                $totalOrder += $orderItem->getQtyOrdered();
            } else {
                foreach ($orderItem->getChildrenItems() as $bundleChild) {
                    $totalOrder += $bundleChild->getQtyOrdered();
                }
            }
        }
        return $totalReturn / $totalOrder;
    }
    /**
     * @param \Magento\Rma\Model\Rma $rma
     */
    private function getOrderItemPosnr($rma)
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
}
