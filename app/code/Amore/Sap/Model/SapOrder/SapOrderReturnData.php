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
        \Magento\Bundle\Api\ProductLinkManagementInterface $productLinkManagement
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

        $bindData[] = [
            'vkorg' => $this->config->getSalesOrg('store', $storeId),
            'kunnr' => $this->config->getClient('store', $storeId),
            'odrno' => "R" . $rma->getIncrementId(),
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
            'custid' => $customer != '' ? $rma->getCustomerId() : '',
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
            'nsamt' => round($this->getRmaSubtotalInclTax($rma)),
            'dcamt' => round($this->getRmaDiscountAmount($rma) + $this->getBundleExtraAmount($rma)),
            'slamt' => $order->getGrandTotal() == 0 ? $order->getGrandTotal() : round($this->getRmaGrandTotal($rma, $orderTotal, $pointUsed)),
            'miamt' => round($this->getRmaPointsUsed($rma, $pointUsed, $orderTotal)),
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
            'ztrackId' => $trackData['track_number']
        ];
        return $bindData;
    }

    /**
     * @param \Magento\Rma\Model\Rma $rma
     * @throws NoSuchEntityException
     */
    public function getRmaItemDataTmp($rma)
    {
        $rmaItemData = [];
        $storeId = $rma->getStoreId();
        $rmaItems = $rma->getItems();
        $order = $rma->getOrder();
        $orderTotal = round($order->getSubtotalInclTax() + $order->getDiscountAmount() + $order->getShippingAmount());
        $mileageUsedAmount = $order->getRewardPointsBalance();
        $originPosnr = $this->getOrderItemPosnr($rma);

        $itemsSubtotal = 0;
        $itemsDiscountAmount = 0;
        $itemsGrandtotal = 0;
        $itemsGrandtotalInclTax = 0;
        $itemsMileage = 0;

        $cnt = 1;
        /** @var \Magento\Rma\Model\Item $rmaItem */
        foreach ($rmaItems as $rmaItem) {
            $orderItem = $this->orderItemRepository->get($rmaItem->getOrderItemId());
            if ($orderItem->getProductType() != 'bundle') {
                $configurableCheckedItem = $this->productTypeCheck($orderItem);
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

                $rmaItemData[] = [
                    'itemVkorg' => $this->config->getSalesOrg('store', $storeId),
                    'itemKunnr' => $this->config->getClient('store', $storeId),
                    'itemOdrno' => "R" . $rma->getIncrementId(),
                    'itemPosnr' => $cnt,
                    'itemMatnr' => $this->productTypeCheck($orderItem)->getSku(),
                    'itemMenge' => intval($rmaItem->getQtyRequested()),
                    // 아이템 단위, Default : EA
                    'itemMeins' => $this->getMeins($meins),
                    'itemNsamt' => round($orderItem->getPriceInclTax() * $rmaItem->getQtyRequested()),
                    'itemDcamt' => round($this->getRateAmount($orderItem->getDiscountAmount(), $this->getNetQty($orderItem), $rmaItem->getQtyRequested())),
                    'itemSlamt' => round($this->getRateAmount($itemGrandTotalInclTax, $this->getNetQty($orderItem), $rmaItem->getQtyRequested())),
                    'itemMiamt' => round($this->getRateAmount($mileagePerItem, $this->getNetQty($orderItem), $rmaItem->getQtyRequested())),
                    // 상품이 무상제공인 경우 Y 아니면 N
                    'itemFgflg' => $orderItem->getPrice() == 0 ? 'Y' : 'N',
                    'itemMilfg' => empty($mileageUsedAmount) ? 'N' : 'Y',
                    'itemAuart' => self::RETURN_ORDER,
                    'itemAugru' => self::AUGRU_RETURN_CODE,
                    'itemAbrvw' => self::ABRVW_RETURN_CODE,
                    'itemNetwr' => round($this->getRateAmount($itemGrandTotal, $this->getNetQty($orderItem), $rmaItem->getQtyRequested())),
                    'itemMwsbp' => round($this->getRateAmount($orderItem->getTaxAmount(), $this->getNetQty($orderItem), $rmaItem->getQtyRequested())),
                    'itemVkorgOri' => $this->config->getSalesOrg('store', $storeId),
                    'itemKunnrOri' => $this->config->getClient('store', $storeId),
                    'itemOdrnoOri' => $order->getIncrementId(),
                    'itemPosnrOri' => $originPosnr[$configurableCheckedItem->getItemId()]
                ];
                $cnt++;
                $itemsSubtotal += round($orderItem->getPriceInclTax() * $rmaItem->getQtyRequested());
                $itemsGrandtotal += round($this->getRateAmount($itemGrandTotalInclTax, $this->getNetQty($orderItem), $rmaItem->getQtyRequested()));
                $itemsGrandtotalInclTax += round($this->getRateAmount($itemGrandTotal, $this->getNetQty($orderItem), $rmaItem->getQtyRequested()));
                $itemsDiscountAmount += round($this->getRateAmount($orderItem->getDiscountAmount(), $this->getNetQty($orderItem), $rmaItem->getQtyRequested()));
                $itemsMileage += round($this->getRateAmount($mileagePerItem, $this->getNetQty($orderItem), $rmaItem->getQtyRequested()));
            } else {
                $bundleChildrenItems = $orderItem->getChildrenItems();
                foreach ($bundleChildrenItems as $bundleChildrenItem) {
                    $configurableCheckedItem = $this->productTypeCheck($bundleChildrenItem);
                    $mileagePerItem = $this->mileageSpentRateByItem(
                        $orderTotal,
                        $bundleChildrenItem->getRowTotalInclTax(),
                        $bundleChildrenItem->getDiscountAmount(),
                        $mileageUsedAmount
                    );
                    $itemGrandTotal = $bundleChildrenItem->getRowTotal()
                        - $bundleChildrenItem->getDiscountAmount()
                        - $mileagePerItem;
                    $itemGrandTotalInclTax = $bundleChildrenItem->getRowTotalInclTax()
                        - $bundleChildrenItem->getDiscountAmount()
                        - $mileagePerItem;

                    $product = $this->productRepository->getById($bundleChildrenItem->getProductId());
                    $meins = $product->getData('meins');

                    $rmaItemData[] = [
                        'itemVkorg' => $this->config->getSalesOrg('store', $storeId),
                        'itemKunnr' => $this->config->getClient('store', $storeId),
                        'itemOdrno' => "R" . $rma->getIncrementId(),
                        'itemPosnr' => $cnt,
                        'itemMatnr' => $this->productTypeCheck($bundleChildrenItem)->getSku(),
                        'itemMenge' => intval($rmaItem->getQtyRequested()),
                        // 아이템 단위, Default : EA
                        'itemMeins' => $this->getMeins($meins),
                        'itemNsamt' => round($product->getPrice() * $rmaItem->getQtyRequested()),
                        'itemDcamt' => round($this->getRateAmount($bundleChildrenItem->getDiscountAmount(), $this->getNetQty($bundleChildrenItem), $rmaItem->getQtyRequested())),
                        'itemSlamt' => round($this->getRateAmount($itemGrandTotalInclTax, $this->getNetQty($bundleChildrenItem), $rmaItem->getQtyRequested())),
                        'itemMiamt' => round($this->getRateAmount($mileagePerItem, $this->getNetQty($orderItem), $rmaItem->getQtyRequested())),
                        // 상품이 무상제공인 경우 Y 아니면 N
                        'itemFgflg' => $bundleChildrenItem->getPrice() == 0 ? 'Y' : 'N',
                        'itemMilfg' => empty($mileageUsedAmount) ? 'N' : 'Y',
                        'itemAuart' => self::RETURN_ORDER,
                        'itemAugru' => self::AUGRU_RETURN_CODE,
                        'itemAbrvw' => self::ABRVW_RETURN_CODE,
                        'itemNetwr' => round($this->getRateAmount($itemGrandTotal, $this->getNetQty($bundleChildrenItem), $rmaItem->getQtyRequested())),
                        'itemMwsbp' => round($this->getRateAmount($bundleChildrenItem->getTaxAmount(), $this->getNetQty($bundleChildrenItem), $rmaItem->getQtyRequested())),
                        'itemVkorgOri' => $this->config->getSalesOrg('store', $storeId),
                        'itemKunnrOri' => $this->config->getClient('store', $storeId),
                        'itemOdrnoOri' => $order->getIncrementId(),
                        'itemPosnrOri' => $originPosnr[$configurableCheckedItem->getItemId()]
                    ];
                    $cnt++;
                    $itemsSubtotal += round($product->getPrice() * $rmaItem->getQtyRequested());
                    $itemsGrandtotal += round($this->getRateAmount($itemGrandTotalInclTax, $this->getNetQty($bundleChildrenItem), $rmaItem->getQtyRequested()));
                    $itemsGrandtotalInclTax += round($this->getRateAmount($itemGrandTotal, $this->getNetQty($bundleChildrenItem), $rmaItem->getQtyRequested()));
                    $itemsDiscountAmount += round($this->getRateAmount($bundleChildrenItem->getDiscountAmount(), $this->getNetQty($bundleChildrenItem), $rmaItem->getQtyRequested()));
                    $itemsMileage += round($this->getRateAmount($mileagePerItem, $this->getNetQty($orderItem), $rmaItem->getQtyRequested()));
                }
            }
        }
        $orderSubtotal = round($this->getRmaSubtotalInclTax($rma));
        $orderGrandtotal = $order->getGrandTotal() == 0 ? $order->getGrandTotal() : round($order->getGrandTotal() - $order->getShippingAmount());
        $orderDiscountAmount = round(abs($order->getDiscountAmount()));

        $rmaItemData = $this->priceCorrector($orderSubtotal, $itemsSubtotal, $rmaItemData, 'itemNsamt');
        $rmaItemData = $this->priceCorrector($orderGrandtotal, $itemsGrandtotalInclTax, $rmaItemData, 'itemSlamt');
        $rmaItemData = $this->priceCorrector($orderGrandtotal, $itemsGrandtotal, $rmaItemData, 'itemNetwr');
        $rmaItemData = $this->priceCorrector($orderDiscountAmount, $itemsDiscountAmount, $rmaItemData, 'itemDcamt');
        $rmaItemData = $this->priceCorrector($mileageUsedAmount, $itemsMileage, $rmaItemData, 'itemMiamt');

        return $rmaItemData;
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
        $itemsGrandtotal = 0;
        $itemsGrandtotalInclTax = 0;
        $itemsMileage = 0;

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

                $rmaItemData[] = [
                    'itemVkorg' => $this->config->getSalesOrg('store', $storeId),
                    'itemKunnr' => $this->config->getClient('store', $storeId),
                    'itemOdrno' => "R" . $rma->getIncrementId(),
                    'itemPosnr' => $cnt,
                    'itemMatnr' => $this->productTypeCheck($orderItem)->getSku(),
                    'itemMenge' => intval($rmaItem->getQtyRequested()),
                    // 아이템 단위, Default : EA
                    'itemMeins' => $this->getMeins($meins),
                    'itemNsamt' => round($orderItem->getPriceInclTax() * $rmaItem->getQtyRequested()),
                    'itemDcamt' => round($this->getRateAmount($orderItem->getDiscountAmount(), $this->getNetQty($orderItem), $rmaItem->getQtyRequested())),
                    'itemSlamt' => round($this->getRateAmount($itemGrandTotalInclTax, $this->getNetQty($orderItem), $rmaItem->getQtyRequested())),
                    'itemMiamt' => round($this->getRateAmount($mileagePerItem, $this->getNetQty($orderItem), $rmaItem->getQtyRequested())),
                    // 상품이 무상제공인 경우 Y 아니면 N
                    'itemFgflg' => $orderItem->getPrice() == 0 ? 'Y' : 'N',
                    'itemMilfg' => empty($mileageUsedAmount) ? 'N' : 'Y',
                    'itemAuart' => self::RETURN_ORDER,
                    'itemAugru' => self::AUGRU_RETURN_CODE,
                    'itemAbrvw' => self::ABRVW_RETURN_CODE,
                    'itemNetwr' => round($this->getRateAmount($itemGrandTotal, $this->getNetQty($orderItem), $rmaItem->getQtyRequested())),
                    'itemMwsbp' => round($this->getRateAmount($orderItem->getTaxAmount(), $this->getNetQty($orderItem), $rmaItem->getQtyRequested())),
                    'itemVkorgOri' => $this->config->getSalesOrg('store', $storeId),
                    'itemKunnrOri' => $this->config->getClient('store', $storeId),
                    'itemOdrnoOri' => $order->getIncrementId(),
                    'itemPosnrOri' => $originPosnr[$rmaItem->getOrderItemId()]
                ];
                $cnt++;
                $itemsSubtotal += round($orderItem->getPriceInclTax() * $rmaItem->getQtyRequested());
                $itemsGrandtotal += round($this->getRateAmount($itemGrandTotalInclTax, $this->getNetQty($orderItem), $rmaItem->getQtyRequested()));
                $itemsGrandtotalInclTax += round($this->getRateAmount($itemGrandTotal, $this->getNetQty($orderItem), $rmaItem->getQtyRequested()));
                $itemsDiscountAmount += round($this->getRateAmount($orderItem->getDiscountAmount(), $this->getNetQty($orderItem), $rmaItem->getQtyRequested()));
                $itemsMileage += round($this->getRateAmount($mileagePerItem, $this->getNetQty($orderItem), $rmaItem->getQtyRequested()));
            } else {
                /** @var \Magento\Catalog\Model\Product $bundleProduct */
                $bundleProduct = $this->productRepository->getById($orderItem->getProductId());
                $bundleChildren = $this->getBundleChildren($orderItem->getSku());
                $bundlePriceType = $bundleProduct->getPriceType();

                foreach ($bundleChildren as $bundleChildrenItem) {
                    $itemId = $rmaItem->getOrderItemId();

                    if ((int)$bundlePriceType !== \Magento\Bundle\Model\Product\Price::PRICE_TYPE_DYNAMIC) {
                        $bundleChildPrice = $bundleChildrenItem->getPrice();
                    } else {
                        $bundleChildPrice = $this->getBundleChildFromOrder($itemId, $bundleChildrenItem->getSku())->getPrice();
                    }

                    $bundleChildDiscountAmount = (int)$bundlePriceType !== \Magento\Bundle\Model\Product\Price::PRICE_TYPE_DYNAMIC ?
                        round($this->getProportionOfBundleChild($orderItem->getPrice(), $bundleChildPrice, $orderItem->getDiscountAmount())) :
                        round($this->getBundleChildFromOrder($itemId, $bundleChildrenItem->getSku())->getDiscountAmount());
                    $mileagePerItem = $this->mileageSpentRateByItem(
                        $orderTotal,
                        $this->getProportionOfBundleChild($orderItem->getPrice(), $bundleChildPrice, $orderItem->getRowTotalInclTax()),
                        $bundleChildDiscountAmount,
                        $mileageUsedAmount);
                    $itemGrandTotal = $this->getProportionOfBundleChild($orderItem->getPrice(), $bundleChildPrice, $orderItem->getRowTotal())
                        - $bundleChildDiscountAmount
                        - $mileagePerItem;
                    $itemGrandTotalInclTax = $this->getProportionOfBundleChild($orderItem->getPrice(), $bundleChildPrice, $orderItem->getRowTotalInclTax())
                        - $bundleChildDiscountAmount
                        - $mileagePerItem;

                    $product = $this->productRepository->get($bundleChildrenItem->getSku(), false, $rma->getStoreId());
                    $meins = $product->getData('meins');

                    $rmaItemData[] = [
                        'itemVkorg' => $this->config->getSalesOrg('store', $storeId),
                        'itemKunnr' => $this->config->getClient('store', $storeId),
                        'itemOdrno' => "R" . $rma->getIncrementId(),
                        'itemPosnr' => $cnt,
                        'itemMatnr' => $bundleChildrenItem->getSku(),
                        'itemMenge' => intval($rmaItem->getQtyRequested()),
                        // 아이템 단위, Default : EA
                        'itemMeins' => $this->getMeins($meins),
//                        'itemNsamt' => round($bundleChildPrice * $rmaItem->getQtyRequested()),
                        'itemNsamt' => round($product->getPrice() * $rmaItem->getQtyRequested()),
                        'itemDcamt' => round($this->getRateAmount($bundleChildDiscountAmount, $this->getNetQty($orderItem), $rmaItem->getQtyRequested()) + (($product->getPrice() - $bundleChildPrice) * $rmaItem->getQtyRequested())),
                        'itemSlamt' => round($this->getRateAmount($itemGrandTotalInclTax, $this->getNetQty($orderItem), $rmaItem->getQtyRequested())),
                        'itemMiamt' => round($this->getRateAmount($mileagePerItem, $this->getNetQty($orderItem), $rmaItem->getQtyRequested())),
                        // 상품이 무상제공인 경우 Y 아니면 N
                        'itemFgflg' => $orderItem->getPrice() == 0 ? 'Y' : 'N',
                        'itemMilfg' => empty($mileageUsedAmount) ? 'N' : 'Y',
                        'itemAuart' => self::RETURN_ORDER,
                        'itemAugru' => self::AUGRU_RETURN_CODE,
                        'itemAbrvw' => self::ABRVW_RETURN_CODE,
                        'itemNetwr' => round($this->getRateAmount($itemGrandTotal, $this->getNetQty($orderItem), $rmaItem->getQtyRequested())),
                        'itemMwsbp' => round($this->getRateAmount($bundleChildrenItem->getTaxAmount(), $this->getNetQty($orderItem), $rmaItem->getQtyRequested())),
                        'itemVkorgOri' => $this->config->getSalesOrg('store', $storeId),
                        'itemKunnrOri' => $this->config->getClient('store', $storeId),
                        'itemOdrnoOri' => $order->getIncrementId(),
                        'itemPosnrOri' => $originPosnr[$this->getBundleChildFromOrder($itemId, $bundleChildrenItem->getSku())->getItemId()]
                    ];
                    $cnt++;
                    $itemsSubtotal += round($product->getPrice() * $rmaItem->getQtyRequested());
                    $itemsGrandtotal += round($this->getRateAmount($itemGrandTotalInclTax, $this->getNetQty($orderItem), $rmaItem->getQtyRequested()));
                    $itemsGrandtotalInclTax += round($this->getRateAmount($itemGrandTotal, $this->getNetQty($orderItem), $rmaItem->getQtyRequested()));
                    $itemsDiscountAmount += round($this->getRateAmount($bundleChildDiscountAmount, $this->getNetQty($orderItem), $rmaItem->getQtyRequested()) + (($product->getPrice() - $bundleChildPrice) * $rmaItem->getQtyRequested()));

                    $itemsMileage += round($this->getRateAmount($mileagePerItem, $this->getNetQty($orderItem), $rmaItem->getQtyRequested()));
                }
            }
        }
        $orderSubtotal = round($this->getRmaSubtotalInclTax($rma));
        $orderGrandtotal = $order->getGrandTotal() == 0 ? $order->getGrandTotal() : round($this->getRmaGrandTotal($rma, $orderTotal, $pointUsed));
        $orderDiscountAmount = round($this->getRmaDiscountAmount($rma) + $this->getBundleExtraAmount($rma));

        $rmaItemData = $this->priceCorrector($orderSubtotal, $itemsSubtotal, $rmaItemData, 'itemNsamt');
        $rmaItemData = $this->priceCorrector($orderGrandtotal, $itemsGrandtotalInclTax, $rmaItemData, 'itemSlamt');
        $rmaItemData = $this->priceCorrector($orderGrandtotal, $itemsGrandtotal, $rmaItemData, 'itemNetwr');
        $rmaItemData = $this->priceCorrector($orderDiscountAmount, $itemsDiscountAmount, $rmaItemData, 'itemDcamt');
        $rmaItemData = $this->priceCorrector($mileageUsedAmount, $itemsMileage, $rmaItemData, 'itemMiamt');

        return $rmaItemData;
    }

    public function getBundleChildren($bundleDynamicSku)
    {
        $bundleSku = explode("-", $bundleDynamicSku);
        try {
            return $this->productLinkManagement->getChildren($bundleSku[0]);
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
     * @param $rma \Magento\Rma\Model\Rma
     */
    public function getBundleExtraAmount($rma)
    {
        $rmaItems = $rma->getItems();
        $priceDifferences = 0;

        foreach ($rmaItems as $rmaItem) {
            $orderItem = $this->orderItemRepository->get($rmaItem->getOrderItemId());
            if ($orderItem->getProductType() == 'bundle') {
                /** @var \Magento\Catalog\Model\Product $bundleProduct */
                $bundleProduct = $this->productRepository->getById($orderItem->getProductId());
                $bundleChildren = $this->getBundleChildren($orderItem->getSku());
                $bundlePriceType = $bundleProduct->getPriceType();

                if ((int)$bundlePriceType !== \Magento\Bundle\Model\Product\Price::PRICE_TYPE_DYNAMIC) {
                    foreach ($bundleChildren as $bundleChild) {
                        $fixedPrice = $bundleChild->getPrice();
                        $originItemPrice = $this->productRepository->get($bundleChild->getSku(), false, $rma->getStoreId())->getPrice();

                        $priceDifferences += (($originItemPrice - $fixedPrice) * $rmaItem->getQtyRequested());
                    }
                }
            }
        }
        return $priceDifferences;
    }

    public function getProportionOfBundleChild($bundleAmount, $childAmount, $valueToCalculate)
    {
        $rate = ($childAmount / $bundleAmount);

        return $valueToCalculate * $rate;
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
        $cvsStoreData = $this->quoteCvsLocationRepository->getById($cvsLocationId);
        $cvsAddress = $cvsStoreData->getCvsAddress() . ' ' . $cvsStoreData->getCvsStoreName() . ' ' . $cvsStoreData->getLogisticsSubType();

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
                $bundleChildren = $this->getBundleChildren($orderItem->getSku());
                $bundlePriceType = $bundleProduct->getPriceType();

                foreach ($bundleChildren as $bundleChild) {
                    $itemId = $rmaItem->getOrderItemId();
                    if ((int)$bundlePriceType !== \Magento\Bundle\Model\Product\Price::PRICE_TYPE_DYNAMIC) {
                        $bundleChildPrice = $bundleChild->getPrice();
                    } else {
                        $bundleChildPrice = $this->getBundleChildFromOrder($itemId, $bundleChild->getSku())->getPrice();
                    }

                    $bundleChildDiscountAmount = (int)$bundlePriceType !== \Magento\Bundle\Model\Product\Price::PRICE_TYPE_DYNAMIC ?
                        round($this->getProportionOfBundleChild($orderItem->getPrice(), $bundleChildPrice, $orderItem->getDiscountAmount())) :
                        round($this->getBundleChildFromOrder($itemId, $bundleChild->getSku())->getDiscountAmount());
                    $mileagePerItem = $this->mileageSpentRateByItem(
                        $orderTotal,
                        $this->getProportionOfBundleChild($orderItem->getPrice(), $bundleChildPrice, $orderItem->getRowTotalInclTax()),
                        $bundleChildDiscountAmount,
                        $mileageUsedAmount);
                    $itemGrandTotalInclTax = $this->getProportionOfBundleChild($orderItem->getPrice(), $bundleChildPrice, $orderItem->getRowTotalInclTax())
                        - $bundleChildDiscountAmount
                        - $mileagePerItem;

                    $grandTotal += round($this->getRateAmount($itemGrandTotalInclTax, $this->getNetQty($orderItem), $rmaItem->getQtyRequested()));
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

                $itemGrandTotal = round($this->getRateAmount($itemGrandTotal, $this->getNetQty($orderItem), $rmaItem->getQtyRequested()));
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
                $bundleChildren = $this->getBundleChildren($orderItem->getSku());
                $bundlePriceType = $bundleProduct->getPriceType();
                foreach ($bundleChildren as $bundleChild) {
                    $bundleChildDiscountAmount = $this->getDiscountAmountForBundleChild($bundlePriceType, $orderItem, $bundleChild);
                    $discountAmount +=  ($bundleChildDiscountAmount * $rmaItem->getQtyRequested() / $this->getNetQty($orderItem));
                }
            } else {
                $discountAmount +=  ($orderItem->getDiscountAmount() * $rmaItem->getQtyRequested() / $this->getNetQty($orderItem));
            }
        }
        return $discountAmount;
    }

    public function getDiscountAmountForBundleChild($bundlePriceType, $orderItem, $bundleChild)
    {
        $bundleChildPrice = $this->getBundleChildPrice($bundlePriceType, $orderItem, $bundleChild);

        $bundleChildDiscountAmount = (int)$bundlePriceType !== \Magento\Bundle\Model\Product\Price::PRICE_TYPE_DYNAMIC ?
            $this->getProportionOfBundleChild($orderItem->getPrice(), $bundleChildPrice, $orderItem->getDiscountAmount()) :
            $this->getBundleChildFromOrder($orderItem->getItemId(), $bundleChild->getSku())->getDiscountAmount();

        return $bundleChildDiscountAmount;
    }

    public function getBundleChildPrice($bundlePriceType, $orderItem, $bundleChild)
    {
        if ((int)$bundlePriceType !== \Magento\Bundle\Model\Product\Price::PRICE_TYPE_DYNAMIC) {
            $bundleChildPrice = $bundleChild->getPrice();
        } else {
            $bundleChildPrice = $this->getBundleChildFromOrder($orderItem->getItemId(), $bundleChild->getSku())->getPrice();
        }
        return $bundleChildPrice;
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
            if ($orderItem->getProductType() == 'bundle') {
                $bundleChildren = $this->getBundleChildren($orderItem->getSku());
                foreach ($bundleChildren as $bundleChild) {
                    $product = $this->productRepository->get($bundleChild->getSku(), false, $rma->getStoreId());
                    $subtotalInclTax += ($product->getPrice() * $rmaItem->getQtyRequested());
                }
            } else {
                $subtotalInclTax += ($orderItem->getPriceInclTax() * $rmaItem->getQtyRequested());
            }
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