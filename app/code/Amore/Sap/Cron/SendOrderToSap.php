<?php

namespace Amore\Sap\Cron;

use Amore\Sap\Exception\ShipmentNotExistException;
use Amore\Sap\Logger\Logger;
use Amore\Sap\Model\Connection\Request as SapRequest;
use Amore\Sap\Model\SapOrder\SapOrderConfirmData;
use Amore\Sap\Model\Source\Config;
use Eguana\BizConnect\Model\OperationLogRepository;
use Exception;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\OrderRepository;
use Magento\Sales\Model\ResourceModel\Order\CollectionFactory as OrderCollectionFactory;
use Magento\Sales\Model\ResourceModel\Order\Collection as OrderCollection;
use Amore\Sap\Controller\Adminhtml\SapOrder\MassSend as OrderMassSend;
use CJ\NinjaVanShipping\Api\GetTrackUrlByOrderInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoresConfig;

class SendOrderToSap
{
    /**
     * @var OrderCollectionFactory
     */
    private OrderCollectionFactory $orderCollectionFactory;
    /**
     * @var Logger
     */
    private Logger $logger;
    /**
     * @var SapOrderConfirmData
     */
    private SapOrderConfirmData $sapOrderConfirmData;
    /**
     * @var OrderRepository
     */
    private OrderRepository $orderRepository;
    /**
     * @var Config
     */
    protected Config $config;
    /**
     * @var OrderMassSend
     */
    protected OrderMassSend $orderMassSend;
    /**
     * @var SapRequest
     */
    protected SapRequest $sapRequest;
    /**
     * @var OperationLogRepository
     */
    private OperationLogRepository $operationLogRepository;
    /**
     * @var Json
     */
    private Json $json;
    /**
     * @var GetTrackUrlByOrderInterface
     */
    private GetTrackUrlByOrderInterface $getTrackUrlByOrder;
    /**
     * @var StoresConfig
     */
    protected $storesConfig;

    /**
     * @param OrderCollectionFactory $orderCollectionFactory
     * @param Logger $logger
     * @param SapOrderConfirmData $sapOrderConfirmData
     * @param OrderRepository $orderRepository
     * @param Config $config
     * @param OrderMassSend $orderMassSend
     * @param SapRequest $sapRequest
     * @param OperationLogRepository $operationLogRepository
     * @param Json $json
     * @param GetTrackUrlByOrderInterface $getTrackUrlByOrder
     * @param StoresConfig $storesConfig
     */
    public function __construct(
        OrderCollectionFactory $orderCollectionFactory,
        Logger $logger,
        SapOrderConfirmData $sapOrderConfirmData,
        OrderRepository $orderRepository,
        Config $config,
        OrderMassSend $orderMassSend,
        SapRequest $sapRequest,
        OperationLogRepository $operationLogRepository,
        Json $json,
        GetTrackUrlByOrderInterface $getTrackUrlByOrder,
        StoresConfig $storesConfig
    ) {
        $this->orderCollectionFactory = $orderCollectionFactory;
        $this->logger                 = $logger;
        $this->sapOrderConfirmData    = $sapOrderConfirmData;
        $this->orderRepository        = $orderRepository;
        $this->config                 = $config;
        $this->orderMassSend          = $orderMassSend;
        $this->sapRequest             = $sapRequest;
        $this->operationLogRepository = $operationLogRepository;
        $this->json                   = $json;
        $this->getTrackUrlByOrder     = $getTrackUrlByOrder;
        $this->storesConfig           = $storesConfig;
    }

    public function execute()
    {
        $sapCronEnables = $this->storesConfig->getStoresConfigByPath(Config::SAP_CRON_ENABLE);
        foreach ($sapCronEnables as $storeId => $cronEnable) {
            if ($cronEnable) {
                $allowOrderStatuses = [
                    'processing',
                    'sap_fail',
                    'processing_with_shipment'
                ];
                $orderCollection = $this->orderCollectionFactory->create();
                $orderCollection->addFieldToFilter('store_id', $storeId)
                    ->addFieldToFilter('status', ['in' => $allowOrderStatuses]);
                $orderLimitation = $this->config->getValue(Config::SAP_CRON_LIMITATION, ScopeInterface::SCOPE_STORE, $storeId);
                if (!$orderLimitation) {
                    $orderLimitation = 10;
                }
                $orderCollection->setPageSize($orderLimitation);
                $orderCollection->setCurPage(1);
                if ($orderCollection->getSize()) {
                    $this->massSendOrderToSap($orderCollection);
                } else {
                    $this->logger->info('NO ORDERS TO SEND');
                }
            }
        }
    }

    /**
     * @param OrderCollection $orderCollection
     * @return void
     */
    private function massSendOrderToSap(OrderCollection $orderCollection): void
    {
        $orderDataList = [];
        $orderItemDataList = [];
        $orderStatusError = [];
        $storeIdList = [];

        /** @var Order $order */
        foreach ($orderCollection->getItems() as $order) {
            try {
                if ($order->getShippingMethod() == 'ninjavan_tablerate') {
                    $ninjaVanTrackUrl = $this->getTrackUrlByOrder->execute($order);
                    if (empty($ninjaVanTrackUrl)) {
                        throw new ShipmentNotExistException(__('Order %1 does not have NinjaVan Tracking number', $order->getIncrementId()));
                    }
                }

                $storeId = $order->getStoreId();
                $storeIdList[] = $storeId;
                $orderData = $this->sapOrderConfirmData->getOrderData($order->getIncrementId());
                $orderItemData = $this->sapOrderConfirmData->getOrderItem($order->getIncrementId());
                $orderItemData = $this->updateOrderAndReturnItemData($order, $orderData, $orderItemData);
                $orderDataList = array_merge($orderDataList, $orderData);
                $orderItemDataList = array_merge($orderItemDataList, $orderItemData);
            } catch (ShipmentNotExistException $e) {
                $orderStatusError[] = $order->getIncrementId() . ' : ' . $e->getMessage();
                if ($this->config->getLoggingCheck()) {
                    $this->logger->info("MASS ORDER DATA NO SHIPMENT EXCEPTION");
                    $this->logger->info($order->getIncrementId() . ' : ' . $e->getMessage());
                }
            } catch (NoSuchEntityException $e) {
                $orderStatusError[] = $order->getIncrementId() . ' : ' . $e->getMessage();
                if ($this->config->getLoggingCheck()) {
                    $this->logger->info("MASS ORDER DATA NO SUCH ENTITY EXCEPTION");
                    $this->logger->info($order->getIncrementId() . ' : ' . $e->getMessage());
                }
            } catch (Exception $e) {
                $orderStatusError[] = $order->getIncrementId() . ' : ' . $e->getMessage();
                if ($this->config->getLoggingCheck()) {
                    $this->logger->info("MASS ORDER DATA EXCEPTION");
                    $this->logger->info($order->getIncrementId() . ' : ' . $e->getMessage());
                }
            }
        }

        if ($this->config->getLoggingCheck()) {
            $this->logger->info("ORDER List Data");
            $this->logger->info('', $orderDataList);
            $this->logger->info("ORDER Item List Data");
            $this->logger->info('', $orderItemDataList);
        }

        if ($orderStatusError) {
            foreach ($orderStatusError as $error) {
                $this->logger->error("Error occurred while sending order : $error.");
            }
        }

        try {
            $orderCount = count($orderDataList);
            if ($orderCount > 0) {
                $storeIdUnique = array_unique($storeIdList);
                $sendData = $this->sapOrderConfirmData->massSendOrderData($orderDataList, $orderItemDataList);
                if ($this->config->getLoggingCheck()) {
                    $this->logger->info("ORDER MASS SEND DATA");
                    $this->logger->info('', $sendData);
                }

                $result = $this->sapRequest->postRequest($sendData, array_shift($storeIdUnique));


                if ($this->config->getLoggingCheck()) {
                    $this->logger->info("ORDER MASS SEND RESULT");
                    if (is_array($result)) {
                        $this->logger->info('', $result);
                    } else {
                        $this->logger->info($result);
                    }
                }

                $this->logOperation(
                    $this->json->serialize($sendData),
                    empty($orderStatusError) ? 1 : 0,
                    $this->json->serialize($result) . "\n" . "Error Orders : " . $this->json->serialize($orderStatusError)
                );

                if ($result && is_array($result)) {
                    if ($result['success']) {
                        if (isset($result['data']['response']['output']['outdata'])) {
                            $outdata = $result['data']['response']['output']['outdata'];
                            $ordersSucceeded = [];
                            $orderFailed = [];
                            foreach ($outdata as $data) {
                                if ($data['retcod'] == 'S') {
                                    $ordersSucceeded[] = $this->orderMassSend->getOriginOrderIncrementId($data);
                                    $succeededOrderObject = $this->sapOrderConfirmData->getOrderInfo($data['odrno']);
                                    $orderSendCheck = $succeededOrderObject->getData('sap_order_send_check');
                                    $succeededOrderObject->setStatus('sap_processing');

                                    if ($orderSendCheck == 0 || $orderSendCheck == 2) {
                                        $succeededOrderObject->setData('sap_order_send_check', SapOrderConfirmData::ORDER_RESENT_TO_SAP_SUCCESS);
                                    } else {
                                        $succeededOrderObject->setData('sap_order_send_check', SapOrderConfirmData::ORDER_SENT_TO_SAP_SUCCESS);
                                    }

                                    $this->orderRepository->save($succeededOrderObject);
                                } else {
                                    $error = ['increment_id' => $data['odrno'], 'code' => $data['ugcod'], 'error_code' => $data['ugtxt']];
                                    array_push($orderFailed, $error);
                                    $this->orderMassSend->changeOrderSendCheckValue($data, SapOrderConfirmData::ORDER_SENT_TO_SAP_FAIL, "sap_fail");
                                    $this->logger->error(
                                        __(
                                            'Error returned from SAP for order %1. Error code : %2. Message : %3',
                                            $data['odrno'],
                                            $data['ugcod'],
                                            $data['ugtxt']
                                        )
                                    );
                                }
                            }
                            $countOrderSucceeded = count($ordersSucceeded);
                            if ($countOrderSucceeded > 0) {
                                $this->logger->info(__('%1 orders sent to SAP Successfully.', $countOrderSucceeded));
                            }

                            if (count($orderFailed)) {
                                $this->logOperation(
                                    $this->json->serialize($sendData),
                                    empty($orderStatusError) ? 1 : 0,
                                    $this->json->serialize($orderFailed)
                                );
                            }
                        }
                    } else {
                        if (isset($result['data']['response']['output']['outdata'])) {
                            $outdata = $result['data']['response']['output']['outdata'];
                            foreach ($outdata as $data) {
                                $this->orderMassSend->changeOrderSendCheckValue($data, SapOrderConfirmData::ORDER_SENT_TO_SAP_FAIL, "sap_fail");
                            }
                        }

                        $this->logger->error(
                            __(
                                'Error returned from SAP for order %1. Error code : %2. Message : %3',
                                $order->getIncrementId(),
                                $result['code'],
                                $result['message']
                            )
                        );
                    }
                } else {
                    $this->logger->error(__('Something went wrong while sending order data to SAP. No response'));
                }
            } else {
                $this->logger->error(__('There is no order to send. Check order and try again.'));
            }
        } catch (LocalizedException $exception) {
            $this->logger->error($exception->getMessage());
        }
    }

    /**
     * @param Order $order
     * @param array $orderData
     * @param array $orderItemData
     * @throws NoSuchEntityException
     * @throws \Magento\Framework\Exception\AlreadyExistsException
     * @throws \Magento\Framework\Exception\InputException
     */
    private function updateOrderAndReturnItemData(
        Order $order,
        array $orderData,
        array $orderItemData
    ): array
    {
        if (isset($orderData[0])) {
            $this->sapOrderConfirmData->setReturnOrderData(
                $orderData[0],
                $order
            );
        }
        if (isset($orderItemData[0])) {
            $orderItemData = $this->sapOrderConfirmData->setReturnItemOrderData(
                $orderItemData,
                $order
            );
        }
        $this->orderRepository->save($order);

        return $orderItemData;
    }

    /**
     * @param $serializedData
     * @param $status
     * @param $resultMessage
     * @param $storeId
     */
    private function logOperation(
        $serializedData,
        $status,
        $resultMessage
    )
    {
        $loggedOperationId = $this->operationLogRepository->createOrUpdateMessage(
            'amore.sap.order.cronsend.request',
            $serializedData,
            'SAP',
            $status,
            'outgoing',
            null
        );

        $this->operationLogRepository->addLogToOperation($loggedOperationId, $resultMessage);
    }
}
