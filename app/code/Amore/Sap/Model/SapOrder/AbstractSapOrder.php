<?php
/**
 * Created by Eguana.
 * User: Brian
 * Date: 2020-06-25
 * Time: 오후 8:18
 */

namespace Amore\Sap\Model\SapOrder;

use Eguana\GWLogistics\Model\QuoteCvsLocationRepository;
use Magento\Eav\Api\AttributeRepositoryInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Model\Order;
use Magento\Store\Api\StoreRepositoryInterface;
use Amore\Sap\Model\Source\Config;

abstract class AbstractSapOrder
{
    // 정상 주문
    const NORMAL_ORDER = 'ZA01';
    // 반품
    const RETURN_ORDER = 'ZR01';
    // 잡출 주문
    const SAMPLE_ORDER = 'ZFA1';
    // 잡출 반품
    const SAMPLE_RETURN = 'ZFR1';

    protected $cnt = 1;

    protected $itemsSubtotal = 0;

    protected $itemsGrandTotalInclTax = 0;

    protected $itemsGrandTotal = 0;

    protected $itemsDiscountAmount = 0;

    protected $itemsMileage = 0;

    /**
     * @var SearchCriteriaBuilder
     */
    protected $searchCriteriaBuilder;
    /**
     * @var OrderRepositoryInterface
     */
    protected $orderRepository;
    /**
     * @var StoreRepositoryInterface
     */
    protected $storeRepository;
    /**
     * @var Config
     */
    protected $config;

    /**
     * @var QuoteCvsLocationRepository
     */
    protected $quoteCvsLocationRepository;

    /**
     * @var AttributeRepositoryInterface
     */
    protected $eavAttributeRepositoryInterface;

    /**
     * @var \Amore\Sap\Logger\Logger
     */
    protected $logger;

    /**
     * @var \CJ\Middleware\Model\Data
     */
    protected $orderData;

    /**
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param OrderRepositoryInterface $orderRepository
     * @param StoreRepositoryInterface $storeRepository
     * @param Config $config
     * @param QuoteCvsLocationRepository $quoteCvsLocationRepository
     * @param AttributeRepositoryInterface $eavAttributeRepositoryInterface
     */
    public function __construct(
        SearchCriteriaBuilder $searchCriteriaBuilder,
        OrderRepositoryInterface $orderRepository,
        StoreRepositoryInterface $storeRepository,
        Config $config,
        QuoteCvsLocationRepository $quoteCvsLocationRepository,
        AttributeRepositoryInterface $eavAttributeRepositoryInterface,
        \Amore\Sap\Logger\Logger $logger,
        \CJ\Middleware\Model\Data $orderData
    ) {
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->orderRepository = $orderRepository;
        $this->storeRepository = $storeRepository;
        $this->config = $config;
        $this->quoteCvsLocationRepository = $quoteCvsLocationRepository;
        $this->eavAttributeRepositoryInterface = $eavAttributeRepositoryInterface;
        $this->logger = $logger;
        $this->orderData = $orderData;
    }

    /**
     * Get store
     *
     * @param $storeId
     * @return \Magento\Store\Api\Data\StoreInterface|string
     */
    public function getStore($storeId)
    {
        try {
            return $this->storeRepository->get($storeId);
        } catch (\Magento\Framework\Exception\NoSuchEntityException $e) {
            return $e->getMessage();
        }
    }

    /**
     * Get order info
     *
     * @param $incrementId
     * @return false|mixed|null
     */
    public function getOrderInfo($incrementId)
    {
        $searchCriteria = $this->searchCriteriaBuilder
            ->addFilter('increment_id', $incrementId, 'eq')
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
     * Get telephone
     *
     * @param $phone
     * @return array|string|string[]|null
     */
    public function getTelephone($phone)
    {
        $phone = preg_replace("/[^0-9]/", "", $phone);

        if (substr($phone, 0, 1) != '0') {
            $phone = '0' . $phone;
        }

        $length = strlen($phone);

        switch ($length) {
            case 11 :
                return preg_replace("/([0-9]{3})([0-9]{4})([0-9]{4})/", "$1-$2-$3", $phone);
                break;
            default:
                return preg_replace("/([0-9]{2})([0-9]{4})([0-9]{4})/", "$1-$2-$3", $phone);
                break;
        }
    }

    /**
     * get kunwe
     *
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
     * Check cvs shipping
     *
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
                    $item->setData('sap_item_miamt', $itemsData[$key]['itemMiamt']);
                    $item->setData('sap_item_reward_point', $itemsData[$key]['PointAccount']);
                    $item->setData('sap_item_mwsbp',  $itemsData[$key]['itemMwsbp']);
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

    /**
     * reset data before send order
     *
     *
     * @return void
     */
    protected function resetData() {
        $this->itemsSubtotal = 0;
        $this->itemsDiscountAmount = 0;
        $this->itemsGrandTotal = 0;
        $this->itemsGrandTotalInclTax = 0;
        $this->itemsMileage = 0;
        $this->cnt = 1;
    }


    /**
     * Correct price order for order item data
     *
     * @param $orderItemData
     * @param $orderSubtotal
     * @param $orderDiscountAmount
     * @param $mileageUsedAmount
     * @param $orderGrandTotal
     * @param $isDecimalFormat
     * @return array
     */
    protected function correctPriceOrderItemData($orderItemData, $orderSubtotal, $orderDiscountAmount, $mileageUsedAmount, $orderGrandTotal, $isDecimalFormat)
    {
        $orderItemData = $this->orderData->priceCorrector($orderSubtotal, $this->itemsSubtotal, $orderItemData, 'itemNsamt', $isDecimalFormat);
        $orderItemData = $this->orderData->priceCorrector($orderDiscountAmount, $this->itemsDiscountAmount, $orderItemData, 'itemDcamt', $isDecimalFormat);
        $orderItemData = $this->orderData->priceCorrector($mileageUsedAmount, $this->itemsMileage, $orderItemData, 'itemMiamt', $isDecimalFormat);
        $orderItemData = $this->orderData->priceCorrector($orderGrandTotal, $this->itemsGrandTotal, $orderItemData, 'itemNetwr', $isDecimalFormat);
        $orderItemData = $this->orderData->priceCorrector($orderGrandTotal, $this->itemsGrandTotalInclTax, $orderItemData, 'itemSlamt', $isDecimalFormat);

        if ($isDecimalFormat) {
            $listToFormat = ['itemNsamt', 'itemSlamt', 'itemDcamt', 'itemMiamt', 'itemNetwr'];

            foreach ($listToFormat as $field) {
                foreach ($orderItemData as $key => $value) {
                    if (isset($value[$field])) {
                        $orderItemData[$key][$field] = $this->orderData->formatPrice($value[$field], $isDecimalFormat);
                    }
                }
            }
        }

        return $orderItemData;
    }

    /**
     * @param $value
     * @param $key
     * @return void
     */
    protected function convertNumberToString(&$value, $key)
    {
        if (is_float($value) || is_int($value)) {
            $value = "$value";
        }
    }
}
