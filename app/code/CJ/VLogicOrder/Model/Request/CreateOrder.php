<?php declare(strict_types=1);

namespace CJ\VLogicOrder\Model\Request;

use CJ\VLogicOrder\Helper\Data as VLogicHelper;
use CJ\VLogicOrder\Model\Request\AuthToken as VLogicToken;
use CJ\VLogicOrder\Model\TokenDataFactory;
use GuzzleHttp\ClientFactory;
use GuzzleHttp\RequestOptions;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use CJ\VLogicOrder\Logger\Logger as VLogicOrderLogger;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\Data\OrderInterface as OrderDataInterface;
use Magento\Sales\Api\Data\ShipmentInterface;
use Magento\Sales\Api\ShipOrderInterface;
use Magento\Sales\Model\Order\Email\Container\ShipmentIdentity;
use Magento\Sales\Api\Data\ShipmentItemCreationInterfaceFactory;
use Magento\Sales\Model\Order\Shipment\Sender\EmailSender as ShipmentEmailSender;

class CreateOrder
{
    /**
     * @var VLogicToken
     */
    private VLogicToken $authToken;

    /**
     * @var VLogicHelper
     */
    private VLogicHelper $vlogicHelper;

    /**
     * @var Json
     */
    private Json $json;

    /**
     * @var DateTime
     */
    protected $dateTime;

    /**
     * @var TimezoneInterface
     */
    private $timezone;

    /**
     * @var TokenDataFactory
     */
    protected $tokenDataFactory;

    /**
     * @var VLogicOrderLogger
     */
    protected $logger;

    /**
     * @var ClientFactory
     */
    protected $clientFactory;

    /**
     * @var ShipmentItemCreationInterfaceFactory
     */
    private $shipmentItemCreationInterfaceFactory;

    /**
     * @var ShipOrderInterface
     */
    private $shipOrderInterface;

    /**
     * @var ShipmentIdentity
     */
    private $shipmentIdentity;

    /**
     * @var ShipmentEmailSender
     */
    protected $shipmentEmailSender;

    /**
     * @param VLogicOrderLogger $logger
     * @param TokenDataFactory $tokenDataFactory
     * @param AuthToken $authToken
     * @param VLogicHelper $vlogicHelper
     * @param Json $json
     * @param DateTime $dateTime
     * @param TimezoneInterface $timezone
     * @param ClientFactory $clientFactory
     * @param ShipmentItemCreationInterfaceFactory $shipmentItemCreationInterfaceFactory
     * @param ShipOrderInterface $shipOrder
     * @param ShipmentIdentity $shipmentIdentity
     * @param ShipmentEmailSender $shipmentEmailSender
     */
    public function __construct(
        VLogicOrderLogger                    $logger,
        TokenDataFactory                     $tokenDataFactory,
        VLogicToken                          $authToken,
        VLogicHelper                         $vlogicHelper,
        Json                                 $json,
        DateTime                             $dateTime,
        TimezoneInterface                    $timezone,
        ClientFactory                        $clientFactory,
        ShipmentItemCreationInterfaceFactory $shipmentItemCreationInterfaceFactory,
        ShipOrderInterface                   $shipOrder,
        ShipmentIdentity                     $shipmentIdentity,
        ShipmentEmailSender                  $shipmentEmailSender
    )
    {
        $this->logger = $logger;
        $this->tokenDataFactory = $tokenDataFactory;
        $this->authToken = $authToken;
        $this->vlogicHelper = $vlogicHelper;
        $this->json = $json;
        $this->dateTime = $dateTime;
        $this->timezone = $timezone;
        $this->clientFactory = $clientFactory;
        $this->shipmentItemCreationInterfaceFactory = $shipmentItemCreationInterfaceFactory;
        $this->shipOrderInterface = $shipOrder;
        $this->shipmentIdentity = $shipmentIdentity;
        $this->shipmentEmailSender = $shipmentEmailSender;
    }

    /**
     * @param $order
     * @return array|bool|float|int|string|null
     */
    public function processOrderVLogic($order)
    {
        $response = null;
        try {
            if (empty($order->getData('sent_to_vlogic'))) {
                $this->logger->info('vlogic | start create order: order id ', [$order->getId()]);
                $response = $this->makeRequestCreateOrder($order);
                if (!empty($response['success'])) {
                    $this->logger->info('vlogic | start creating shipment: return state ');
                }
            }
        } catch (\Exception $e) {
            $this->logger->critical('vlogic | start create order failed: order id ', [$order->getId()]);
            $this->logger->error($e->getMessage());
        }

        return $response;
    }

    /**
     * @param $order
     * @param null $changeStatus
     * @return null
     */
    public function createShipment($order, $changeStatus = null)
    {
        $shipmentItems = $this->createShipmentItem($order);
        if ($shipmentItems == null) {
            return null;
        }
        $shipmentId = $this->shipOrderInterface
            ->execute(
                $order->getEntityId(),
                $shipmentItems,
                $this->shipmentIdentity->isEnabled(),
                false,
                null
            );
        if (empty($shipmentId)) {
            $this->logger->info("Cannot Create delivery order: {$order->getIncrementId()}");
        } else {
            $this->setQtyShipToOrderItem($order);
            if (!empty($changeStatus)) {
                $order->setData('ignore_process', 1);
                $order->setState('processing');
                $order->setStatus('processing_with_shipment');
                $order->save();
            }
        }
        return $shipmentId;
    }

    /**
     * @param $order
     * @return array|bool|float|int|mixed|string|null
     */
    public function makeRequestCreateOrder($order)
    {
        $response = [];
        try {
            $data = $this->payloadSendToVLogic($order);
            $this->logger->info('request body to create delivery order: ');
            $this->logger->info($this->json->serialize($data));
            $response = $this->requestCreateOrder($data, $order);

            $this->logger->info('response from api create delivery order: ');
            $this->logger->info('vlogic | response: ', $response);
            $order->setData('ignore_process', 1);
            if (!empty($response['success'])) {
                if (!$order->hasShipments()) {
                    $shipmentId = $this->createShipment($order);
                    if ($shipmentId) {
                        $order->setData('sent_to_vlogic', 1);
                        $order->setData('vlogic_create_order_response', 'Successful');
                    } else {
                        $response['messagesErrorShipment'] = 'You can not create an shipment';
                    }
                } else {
                    $order->setData('sent_to_vlogic', 1);
                    $order->setData('vlogic_create_order_response', 'Successful');
                }
            } else {
                $order->setState('processing');
                $order->setStatus('processing');
                $order->setData('vlogic_create_order_response', $response['messagesError']);
            }
            $order->save();
            $this->logger->info('vlogic | message: ', [$response['success'] ? 'success' : 'error']);

        } catch (\Exception $exception) {
            $order->setData('vlogic_create_order_response', $exception->getMessage());
            $order->save();
            $this->logger->info("Create delivery order failed: {$exception->getMessage()}");
        }
        return $response;
    }

    /**
     * @param $order \Magento\Sales\Model\Order
     */
    public function setQtyShipToOrderItem($order)
    {
        if ($order->hasInvoices()) {
            $orderItems = $order->getAllItems();
            foreach ($orderItems as $item) {
                if (empty($item->getQtyToShip()) || $item->getIsVirtual()) {
                    continue;
                }
                $item->setQtyShipped($item->getQtyInvoiced());
                $item->save();
            }
            $order->save();
        }
    }

    /**
     * @param OrderDataInterface $order
     * @return array
     */
    public function createShipmentItem($order): array
    {
        $shipmentItems = [];

        $orderItems = $order->getAllVisibleItems();
        /** @var \Magento\Sales\Model\Order\Item $item */
        foreach ($orderItems as $item) {
            /** @var \Magento\Sales\Model\Order\Shipment\ItemCreation $shipmentItem */
            $shipmentItem = $this->shipmentItemCreationInterfaceFactory->create();
            $shipmentItem->setOrderItemId($item->getId())
                ->setQty($item->getQtyOrdered());
            $shipmentItems[] = $shipmentItem;
        }
        return $shipmentItems;
    }

    /**
     * @param string $trackingId
     * @param \Magento\Sales\Model\Order $order
     * @return array|bool|float|int|mixed|string|null
     * @throws \Exception
     */
    public function requestCreateOrder($data, $order)
    {
        $token = $this->getToken($order->getStoreId(), $order->getStore()->getWebsiteId());
        $contents = [];
        $success = false;
        if ($token['token']) {
            $headers = [
                RequestOptions::HEADERS => [
                    'Token' => $token['token'],
                    'Content-Type' => 'application/json',
                    'Accept' => 'application/json'
                ],
                'verify' => false,
                'timeout' => 60,
                'http_errors' => false,
            ];
            $client = new \GuzzleHttp\Client($headers);
            $response = $client->post($this->vlogicHelper->getVLogicUriCreateOrder($order->getStore()->getWebsiteId()), ['json' => $data]);
            $contents = $this->json->unserialize($response->getBody()->getContents());

            $success = true;
            if (isset($contents['StorerConfirmations'])) {
                foreach ($contents['StorerConfirmations'] as $content) {
                    if (isset($content['Confirmations'])) {
                        foreach ($content['Confirmations'] as $confirmation) {
                            if ($confirmation['Result'] == 'Fail') {
                                $contents['messagesError'] = json_encode($confirmation);
                                $this->logger->error('Error when create order vlogic: ' . json_encode($confirmation));
                                $success = false;
                            }
                        }
                    }
                }
            } else {
                if (isset($contents['Message'])) {
                    $contents['messagesError'] = $contents['Message'];
                    $success = false;
                }
            }
            if (!$success) {
                $this->disableToken($token['tokenData']);
            }
            if (isset($contents['messages'])) {
                throw new \Exception(implode(' | ', $contents['messages']));
            }
        }

        if (!$contents) {
            throw new \Exception('Cannot get access token from VLogic.');
        }
        $contents['success'] = $success;
        return $contents;
    }

    /**
     * @param $data
     * @param $storeId
     * @return array|bool|float|int|mixed|string|null
     * @throws \Exception
     */
    public function receiveShipmentActivity($data, $storeId)
    {
        $token = $this->getToken($storeId);
        $contents = [];
        $success = false;
        if ($token['token']) {
            $url = $this->vlogicHelper->getVLogicUriShipmentActivity();
            $headers = [
                RequestOptions::HEADERS => [
                    'Token' => $token['token'],
                    'Content-Type' => 'application/json',
                    'Accept' => 'application/json'
                ],
                'verify' => false,
                'timeout' => 60,
                'http_errors' => false,
            ];
            $client = new \GuzzleHttp\Client($headers);
            $response = $client->post($url, ['json' => $data]);
            $contents = $this->json->unserialize($response->getBody()->getContents());

            $success = true;
            if (isset($contents['StorerConfirmations'])) {
                foreach ($contents['StorerConfirmations'] as $content) {
                    if (isset($content['Confirmations'])) {
                        foreach ($content['Confirmations'] as $confirmation) {
                            if ($confirmation['Result'] == 'Fail') {
                                $contents['messagesError'] = json_encode($confirmation);
                                $this->logger->info('Error when track shipment activity vlogic: ' . json_encode($confirmation));
                                $success = false;
                            }
                        }
                    }
                }
            }
            if (!$success) {
                $this->disableToken($token['tokenData']);
            }
            if (isset($contents['messages'])) {
                throw new \Exception(implode(' | ', $contents['messages']));
            }
        }

        if (!$contents) {
            throw new \Exception('Cannot get access token from VLogic.');
        }
        $contents['success'] = $success;
        return $contents;
    }

    /**
     * @param $storeId
     * @param $websiteId
     * @return array
     * @throws \Exception
     */
    private function getToken($storeId, $websiteId = null)
    {
        $tokenData = $this->authToken->getToken($storeId);
        if (!$tokenData || !$tokenData->getToken()) {
            $auth = $this->authToken->requestAuthToken($storeId, 'array', $websiteId);
            $this->logger->info('auth: ', $auth);
            if (isset($auth['Token']) && $auth['Token']) {
                $token = $auth['Token'];
            } else {
                $this->logger->error('Cannot get access token from VLogic.');
                throw new \Exception('Cannot get access token from VLogic.');
            }
        } else {
            $token = $tokenData->getToken();
        }
        return [
            'token' => $token,
            'tokenData' => $tokenData
        ];
    }

    /**
     * @param $tokenData
     * @return void
     */
    private function disableToken($tokenData)
    {
        try {
            if ($tokenData && $tokenData->getDataByKey('token_id')) {
                $tokenDataFactory = $this->tokenDataFactory->create()->load($tokenData->getDataByKey('token_id'));
                $tokenDataFactory->setStatus(0);
                $tokenDataFactory->save();
            }
        } catch (\Exception $exception) {
            $this->logger->info('Error when disable access token: ' . $exception->getMessage());
        }
    }

    /**
     * @param \Magento\Sales\Model\Order $order
     * @return array
     */
    public function payloadSendToVLogic($order): array
    {
        $shippingAddress = $order->getShippingAddress();

        $currentDate = $this->timezone->date()->format('Y-m-d');
        $deliveryStartDate = $this->timezone->date($order->getCreatedAt())->format('Y-m-d');
        if (strtotime($currentDate) > strtotime($deliveryStartDate)) {
            $deliveryStartDate = $currentDate;
        }

        $internationalCourier = [];
        $internationalCourier["Courier"] = "SF";
        if($order->getPayment()->getMethod() == 'cashondelivery'){
            $internationalCourier["CODAmount"] = $order->getGrandTotal();
        }

        $dataItem = [];
        foreach ($order->getAllItems() as $item) {
            if ($item->getHasChildren()) {
                continue;
            }
            $dataItem[] = [
                "ProductCode" => $item->getSku(),
//                "Description" => $item->getName(),
                "Price" => $item->getPrice(),
                "OrderedQuantity" => (int)$item->getQtyOrdered(),
//                'UDFs' => $this->getItemOptions($item)
            ];
        }
        $websiteId = $order->getStore()->getWebsiteId();
        $data["SalesOrders"][] = [
            "StorerCode" => $this->vlogicHelper->getVLogicStorerCode($websiteId),
            "Incoterms" => $this->vlogicHelper->getVLogicIncoterms($websiteId),
            "StorerSiteCode" => $this->vlogicHelper->getVLogicStorerSiteCode($websiteId),
            "CustomerOrderNumber" => $order->getIncrementId(),
            "OrderDate" => $deliveryStartDate,
            // task #135, use default value is HKB2C for now, will update when finish package options on checkout page
            "RecipientCode" => $this->vlogicHelper->getVLogicRecipientCode($websiteId),
            "FulfillmentType" => $this->vlogicHelper->getVLogicFulfillmentType($websiteId),
            "InternationalCourier" => $internationalCourier,
            "DeliveryAddress" => [
                "CompanyName" => $order->getCustomerLastname() . ' ' . $order->getCustomerFirstname(),
                "Country" => $shippingAddress->getCountryId(),
                "City" => $shippingAddress->getCity(),
                "Address" => implode(" ", $order->getShippingAddress()->getStreet())
            ],
            "DeliveryContactPerson" => [
                "FirstName" => $order->getCustomerFirstname(),
                "LastName" => $order->getCustomerLastname(),
                "Email" => $order->getCustomerEmail(),
                "Phone" => $shippingAddress->getTelephone()
            ],
            "Lines" => $dataItem
        ];

        return $data;
    }

    /**
     * @param $item
     * @return array
     */
    protected function getItemOptions($item)
    {
        try {
            $result = [];
            if ($options = $item->getProductOptions()) {
                $dataOptions = [];
                if (isset($options['options'])) {
                    $dataOptions = array_merge($result, $options['options']);
                }
                if (isset($options['additional_options'])) {
                    $dataOptions = array_merge($result, $options['additional_options']);
                }
                if (isset($options['attributes_info'])) {
                    $dataOptions = array_merge($result, $options['attributes_info']);
                }
                if (isset($options['bundle_options'])) {
                    $newBundleOption = [];
                    foreach ($options['bundle_options'] as $bundleOption) {
                        $dataBundleOption = [];
                        $dataBundleOption['value'] = $bundleOption['option_id'];
                        $dataBundleOption['label'] = '';
                        if (!empty($bundleOption['value']) && is_array($bundleOption['value'])) {
                            foreach ($bundleOption['value'] as $value) {
                                $dataBundleOption['label'] .= $dataBundleOption['label'] . (!empty($value['title']) ? $value['title'] : '') . ' ';
                            }
                        }
                        $newBundleOption[] = $dataBundleOption;
                    }
                    $dataOptions = array_merge($result, $newBundleOption);
                }
                foreach ($dataOptions as $option) {
                    $result[] = [
                        'Name' => $option['label'] ?? '',
                        'Value' => $option['value'] ?? ''
                    ];
                }
            }
            return $result;
        } catch (\Exception $e) {
            $this->logger->info('Error get item options: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * @param OrderDataInterface $order
     * @param ShipmentInterface $shipment
     * @return bool
     * @throws \Exception
     */
    public function sendShipmentEmail(OrderInterface $order, ShipmentInterface $shipment): bool
    {
        return $this->shipmentEmailSender->send($order, $shipment, null, true);
    }
}
