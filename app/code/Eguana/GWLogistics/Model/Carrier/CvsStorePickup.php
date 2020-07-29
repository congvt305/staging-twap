<?php
/**
 * Created by Eguana Team.
 * User: sonia
 * Date: 6/5/20
 * Time: 5:34 AM
 */

namespace Eguana\GWLogistics\Model\Carrier;

use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Api\SortOrderBuilder;
use Magento\Quote\Model\Quote\Address\RateRequest;
use Magento\Shipping\Model\Rate\Result;

class CvsStorePickup extends \Magento\Shipping\Model\Carrier\AbstractCarrier implements
    \Magento\Shipping\Model\Carrier\CarrierInterface
{
    const XML_PATH_SHIPPING_PRICE = 'carriers/gwlogistics/shipping_price';
    /**
     * @var string
     */
    protected $_code = 'gwlogistics';
    /**
     * Rate result data
     *
     * @var Result
     */
    private $result;
    /**
     * @var \Magento\Shipping\Model\Rate\ResultFactory
     */
    private $rateResultFactory;
    /**
     * @var \Magento\Quote\Model\Quote\Address\RateResult\MethodFactory
     */
    private $rateMethodFactory;
    /**
     * @var \Magento\Shipping\Model\Tracking\ResultFactory
     */
    private $trackFactory;
    /**
     * @var \Magento\Shipping\Model\Tracking\Result\ErrorFactory
     */
    private $trackErrorFactory;
    /**
     * @var \Magento\Shipping\Model\Tracking\Result\StatusFactory
     */
    private $trackStatusFactory;
    /**
     * @var SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;
    /**
     * @var \Magento\Sales\Api\ShipmentTrackRepositoryInterface
     */
    private $shipmentTrackRepository;
    /**
     * @var \Magento\Sales\Api\ShipmentRepositoryInterface
     */
    private $shipmentRepository;
    /**
     * @var SortOrderBuilder
     */
    private $sortOrderBuilder;
    /**
     * @var LogisticsInfoStatus
     */
    private $infoStatus;
    /**
     * @var \Eguana\GWLogistics\Model\Request\QueryLogisticsInfo
     */
    private $queryLogisticsInfo;
    /**
     * @var \Eguana\GWLogistics\Api\ReverseStatusNotificationRepositoryInterface
     */
    private $reverseStatusNotificationRepository;
    /**
     * @var \Magento\Rma\Api\TrackRepositoryInterface
     */
    private $rmaTrackRepository;

    public function __construct(
        \Eguana\GWLogistics\Model\Request\QueryLogisticsInfo $queryLogisticsInfo,
        \Eguana\GWLogistics\Model\Carrier\LogisticsInfoStatus $infoStatus,
        \Magento\Rma\Api\TrackRepositoryInterface $rmaTrackRepository,
        \Eguana\GWLogistics\Api\ReverseStatusNotificationRepositoryInterface $reverseStatusNotificationRepository,
        \Magento\Framework\Api\SortOrderBuilder $sortOrderBuilder,
        \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder,
        \Magento\Sales\Api\ShipmentTrackRepositoryInterface $shipmentTrackRepository,
        \Magento\Sales\Api\ShipmentRepositoryInterface $shipmentRepository,
        \Magento\Shipping\Model\Tracking\ResultFactory $trackFactory,
        \Magento\Shipping\Model\Tracking\Result\ErrorFactory $trackErrorFactory,
        \Magento\Shipping\Model\Tracking\Result\StatusFactory $trackStatusFactory,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Quote\Model\Quote\Address\RateResult\ErrorFactory $rateErrorFactory,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Shipping\Model\Rate\ResultFactory $rateResultFactory,
        \Magento\Quote\Model\Quote\Address\RateResult\MethodFactory $rateMethodFactory,
        array $data = []
    ) {
        parent::__construct($scopeConfig, $rateErrorFactory, $logger, $data);
        $this->rateResultFactory = $rateResultFactory;
        $this->rateMethodFactory = $rateMethodFactory;
        $this->trackFactory = $trackFactory;
        $this->trackErrorFactory = $trackErrorFactory;
        $this->trackStatusFactory = $trackStatusFactory;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->shipmentTrackRepository = $shipmentTrackRepository;
        $this->shipmentRepository = $shipmentRepository;
        $this->sortOrderBuilder = $sortOrderBuilder;
        $this->infoStatus = $infoStatus;
        $this->queryLogisticsInfo = $queryLogisticsInfo;
        $this->reverseStatusNotificationRepository = $reverseStatusNotificationRepository;
        $this->rmaTrackRepository = $rmaTrackRepository;
    }

    public function collectRates(RateRequest $request)
    {
        if (!$this->getConfigFlag('active')) {
            return false;
        }

        $freeBoxes = $this->getFreeBoxesCount($request);
        $this->setFreeBoxes($freeBoxes);

        /** @var Result $result */
        $result = $this->rateResultFactory->create();

        $shippingPrice = $this->getShippingPrice($request, $freeBoxes);

        if ($shippingPrice !== false) {
            $method = $this->createResultMethod($shippingPrice);
            $result->append($method);
        }

        return $result;
    }

    public function getAllowedMethods()
    {
        return [$this->_code => __('Convenience Store Pickup')];
    }

    /**
     * Creates result method
     *
     * @param int|float $shippingPrice
     * @return \Magento\Quote\Model\Quote\Address\RateResult\Method
     */
    private function createResultMethod($shippingPrice)
    {
        /** @var \Magento\Quote\Model\Quote\Address\RateResult\Method $method */
        $method = $this->rateMethodFactory->create();

        $method->setCarrier('gwlogistics');
        $method->setCarrierTitle($this->getConfigData('title'));

        $method->setMethod('CVS');
        $method->setMethodTitle($this->getConfigData('name'));

        $method->setPrice($shippingPrice);
        $method->setCost($shippingPrice);

        return $method;
    }

    private function getShippingPrice(RateRequest $request, $freeBoxes)
    {
        $shippingPrice = $this->_scopeConfig->getValue(
            self::XML_PATH_SHIPPING_PRICE,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
        if ($shippingPrice !== false && $request->getPackageQty() == $freeBoxes) {
            $shippingPrice = '0.00';
        }
        return $shippingPrice;
    }

    /**
     * Get count of free boxes
     *
     * @param RateRequest $request
     * @return int
     */
    private function getFreeBoxesCount(RateRequest $request)
    {
        $freeBoxes = 0;
        if ($request->getAllItems()) {
            foreach ($request->getAllItems() as $item) {
                if ($item->getProduct()->isVirtual() || $item->getParentItem()) {
                    continue;
                }

                if ($item->getHasChildren() && $item->isShipSeparately()) {
                    $freeBoxes += $this->getFreeBoxesCountFromChildren($item);
                } elseif ($item->getFreeShipping()) {
                    $freeBoxes += $item->getQty();
                }
            }
        }
        return $freeBoxes;
    }

    /**
     * Returns free boxes count of children
     *
     * @param mixed $item
     * @return mixed
     */
    private function getFreeBoxesCountFromChildren($item)
    {
        $freeBoxes = 0;
        foreach ($item->getChildren() as $child) {
            if ($child->getFreeShipping() && !$child->getProduct()->isVirtual()) {
                $freeBoxes += $item->getQty() * $child->getQty();
            }
        }
        return $freeBoxes;
    }

    /**
     * Get tracking information
     *
     * @param string $tracking
     * @return string|false
     * @api
     */
    public function getTrackingInfo($tracking) //$tracking is the tracking number, no need to implement, remove later
    {
        $result = $this->getTracking($tracking);

        if ($result instanceof \Magento\Shipping\Model\Tracking\Result) {
            $trackings = $result->getAllTrackings();
            if ($trackings) {
                return $trackings[0];
            }
        } elseif (is_string($result) && !empty($result)) {
            return $result;
        }

        return false;
    }

    /**
     * Get tracking
     *
     * @param string|string[] $trackings
     * @return Result
     */
    public function getTracking($trackings)
    {
//        $this->setTrackingReqeust(); //todo: set merchant ID later
        if (!$this->result) {
            $this->result = $this->trackFactory->create();
        }
        if (!is_array($trackings)) {
            $trackings = [$trackings];
        }
        foreach ($trackings as $trackingValue) {
            $responseArr = $this->getGWLTracking($trackingValue);
            if(count($responseArr) > 0) {
                $tracking = $this->trackStatusFactory->create();
                $tracking->setCarrier($this->_code);
                $tracking->setCarrierTitle($this->getConfigData('title'));
                $tracking->setTracking($trackingValue);
                $tracking->addData($responseArr);
                $this->result->append($tracking);
            } else {
                $error = $this->trackErrorFactory->create();
                $error->setCarrier($this->_code);
                $error->setCarrierTitle($this->getConfigData('title'));
                $error->setTracking($trackingValue);
                $error->setErrorMessage(__('There is not tracking info'));
                $this->result->append($error);
            }
        }
        return $this->result;
    }

    private function getGWLTracking($trackingValue)
    {
        $resultArr = [];
        $allPayLogisticsId = $this->findAllPayLogisticsId($trackingValue);
        if($allPayLogisticsId) {
            $notifications = $this->queryLogisticsInfo->sendRequest($allPayLogisticsId);
            if(isset($notifications['LogisticsStatus']) && $notifications['LogisticsStatus']) {
                $info = $this->infoStatus->getStatusInfo($notifications['LogisticsStatus']);
                $resultArr = [
                    'status' => $notifications['LogisticsStatus'] . ' | ' . $info
                ];
            }
        }
        return $resultArr;
    }
    private function getReverseGWTracking($trackingValue)
    {
        $resultArr = [];
        if (!$this->result) {
            $this->result = $this->trackFactory->create();
        }

        $rtnMerchantTradeNo = $this->findRtnMerchantTradeNo($trackingValue);
            $sortOrder = $this->sortOrderBuilder->setField('created_at')
            ->setDirection('DESC')
            ->create();

        $searchCriteria = $this->searchCriteriaBuilder
            ->addFilter('rtn_merchant_trade_no', $rtnMerchantTradeNo)
            ->addSortOrder($sortOrder)
            ->create();

        $notifications = $this->reverseStatusNotificationRepository->getList($searchCriteria)->getItems();

        if (count($notifications) > 0) {
            /** @var \Eguana\GWLogistics\Api\Data\ReverseStatusNotificationInterface $latestNotification */
            $latestNotification = reset($notifications);
            $resultArr = [
                'status' => $latestNotification->getRtnCode() . ' | ' . $latestNotification->getRtnMsg() . ' | ' . $latestNotification->getUpdateStatusDate()
            ];
        }
        return $resultArr;
    }

    private function findAllPayLogisticsId($tracking)
    {
        $searchCriteria = $this->searchCriteriaBuilder
            ->addFilter('track_number', $tracking)
            ->create();
        $track = $this->shipmentTrackRepository->getList($searchCriteria)->getItems();
        $track = reset($track);
        $shipmentId = $track->getParentId();
        $shipment = $this->shipmentRepository->get($shipmentId);
        return $shipment->getAllPayLogisticsId();
    }

    private function findRtnMerchantTradeNo($tracking)
    {
        $searchCriteria = $this->searchCriteriaBuilder
            ->addFilter('track_number', $tracking)
            ->create();
        $rmatrack = $this->rmaTrackRepository->getList($searchCriteria)->getItems();
        /** @var \Magento\Rma\Api\Data\TrackInterface $rmatrack */
        $rmatrack = reset($rmatrack);
        $rmaTrackId = $rmatrack->getEntityId(); //todo debug...
        return $rmatrack->getData('rtn_merchant_trade_no');
    }

    public function isTrackingAvailable()
    {
        return true;
    }
}
