<?php declare(strict_types=1);

namespace CJ\NinjaVanShipping\Model\Request;

use CJ\NinjaVanShipping\Helper\Data as NinjaVanHelper;
use CJ\NinjaVanShipping\Model\Request\AuthToken as NinjaVanToken;
use CJ\NinjaVanShipping\Model\TokenDataFactory;
use GuzzleHttp\RequestOptions;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use CJ\NinjaVanShipping\Logger\Logger as NinjaVanShippingLogger;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Sales\Model\Order\Shipment;
use Magento\Directory\Model\ResourceModel\Region\CollectionFactory as RegionCollectionFactory;

class CreateShipment
{
    const INVALID_TOKEN_CODE = 2001;
    const DUPLICATE_TRACKING_CODE = 109201;

    /**
     * @var NinjaVanToken
     */
    private NinjaVanToken $authToken;
    /**
     * @var NinjaVanHelper
     */
    private NinjaVanHelper $ninjavanHelper;

    /**
     * @var ScopeConfigInterface
     */
    protected ScopeConfigInterface $scopeConfig;

    /**
     * @var Json
     */
    private Json $json;

    private $retryCount = 0;

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
     * @var NinjaVanShippingLogger
     */
    protected $logger;

    /**
     * @var RegionCollectionFactory
     */
    private RegionCollectionFactory $regionCollectionFactory;

    /**
     * @param TokenDataFactory $tokenDataFactory
     * @param AuthToken $authToken
     * @param NinjaVanHelper $ninjavanHelper
     * @param Json $json
     * @param DateTime $dateTime
     * @param TimezoneInterface $timezone
     */
    public function __construct(
        NinjaVanShippingLogger $logger,
        TokenDataFactory $tokenDataFactory,
        NinjaVanToken $authToken,
        NinjaVanHelper $ninjavanHelper,
        ScopeConfigInterface $scopeConfig,
        Json $json,
        DateTime $dateTime,
        TimezoneInterface $timezone,
        RegionCollectionFactory $regionCollectionFactory
    ) {
        $this->logger = $logger;
        $this->tokenDataFactory = $tokenDataFactory;
        $this->authToken = $authToken;
        $this->ninjavanHelper = $ninjavanHelper;
        $this->scopeConfig = $scopeConfig;
        $this->json = $json;
        $this->dateTime = $dateTime;
        $this->timezone = $timezone;
        $this->regionCollectionFactory = $regionCollectionFactory;
    }

    /**
     * @param string $trackingId
     * @param \Magento\Sales\Model\Order $order
     * @return array|bool|float|int|mixed|string|null
     * @throws \Exception
     */
    public function requestCreateOrder($data, $order)
    {
        $tokenData = $this->authToken->getToken($order->getStoreId());
        if (!$tokenData || !$tokenData->getToken()) {
            $auth = $this->authToken->requestAuthToken('array', $order->getStoreId());
            $this->logger->addInfo('auth: ', $auth);
            if (isset($auth['access_token']) && $auth['access_token']) {
                $token = $auth['access_token'];
            } else {
                throw new \Exception('Cannot get access token from NinjaVan.');
            }
        } else {
            $token = $tokenData->getToken();
        }

        $host = $this->ninjavanHelper->getNinjaVanHost();
        $hostLive = $this->ninjavanHelper->getNinjaVanHostLive();
        $countryCode = $this->ninjavanHelper->getNinjaVanCountryCode();
        $uri = $this->ninjavanHelper->getNinjaVanUriCreateOrder();

        $contents = [];
        if ($token) {
            $sandbox = (bool)$this->ninjavanHelper->isNinjaVanSandboxModeEnabled();
            if ($sandbox === false) {
                $url = $hostLive . strtoupper($countryCode) . $uri;
            } else {
                $url = $host . 'SG' . $uri;
            }
            $headers = [
                RequestOptions::HEADERS => [
                    'Authorization' => 'Bearer ' . $token,
                ],
                'verify' => false,
                'timeout' => 60,
                'http_errors' => false,
            ];
            $this->logger->info('ninjavan | request header: ' . $this->json->serialize($headers));
            $client = new \GuzzleHttp\Client($headers);
            $response = $client->post($url, ['json' => $data]);
            $contents = $this->json->unserialize($response->getBody()->getContents());
            if (isset($contents['error'])
                && ($response->getStatusCode() == 401 || isset($contents['error']['code']))
            ) {
                if ($contents['error']['code'] == self::INVALID_TOKEN_CODE) {
                    try {
                        if ($tokenData && $tokenData->getDataByKey('token_id')) {
                            $tokenDataFactory = $this->tokenDataFactory->create()->load($tokenData->getDataByKey('token_id'));
                            $tokenDataFactory->setStatus(0);
                            $tokenDataFactory->save();
                        }
                    } catch (\Exception $exception) {
                        $this->logger->addError('Error when disable access token: ' . $exception->getMessage());
                    }

                    $numOfRetry = $this->ninjavanHelper->getNinjaVanNumberRetry() ?? 4;
                    while ($this->retryCount < $numOfRetry) {
                        $this->retryCount++;
                        $this->logger->info(__("Retry to make create Order request {$this->retryCount} time(s)"));

                        $auth = $this->authToken->requestAuthToken('array', $order->getStoreId());
                        $this->logger->addInfo('retry to get request token: ', $auth);
                        if (empty($auth['access_token'])) {
                            throw new \Exception('Cannot get access token from NinjaVan.');
                        }

                        $headers[RequestOptions::HEADERS] = ['Authorization' => 'Bearer ' . $auth['access_token']];
                        $this->logger->info('ninjavan | request header: ' . $this->json->serialize($headers));

                        $client = new \GuzzleHttp\Client($headers);
                        $response = $client->post($url, ['json' => $data]);
                        $contentsRetry = $this->json->unserialize($response->getBody()->getContents());

                        if (isset($contentsRetry['error']) && $contentsRetry['error']['code'] == self::INVALID_TOKEN_CODE) {
                            $this->logger->addError('Error when retrying to create NV Order: ' . $this->json->serialize($contentsRetry['error']));
                            continue;
                        }
                        $contents = $contentsRetry;
                        break;
                    }
                }
            }
            if (isset($contents['messages'])) {
                throw new \Exception(implode (' | ', $contents['messages']));
            }
        }

        if (!$contents) {
            throw new \Exception('Cannot get access token from NinjaVan.');
        }

        return $contents;
    }

    /**
     * @param \Magento\Sales\Model\Order $order
     * @return array
     */
    public function payloadSendToNinjaVan($order): array
    {
        $merchantOrderNumber = $order->getIncrementId();

        $nameFrom = $this->ninjavanHelper->getNinjaVanSendFrom();
        $phoneFrom = $this->ninjavanHelper->getNinjaVanPhoneFrom();
        $mailFrom = $this->ninjavanHelper->getNinjaVanMailFrom();
        $addressFrom = $this->ninjavanHelper->getNinjaVanAddressFrom();
        $postCodeFrom = $this->ninjavanHelper->getNinjaVanPostcodeFrom();
        $cityFrom = $this->scopeConfig->getValue(Shipment::XML_PATH_STORE_CITY, ScopeInterface::SCOPE_WEBSITE) ?? '';
        $stateIdFrom = $this->scopeConfig->getValue(Shipment::XML_PATH_STORE_REGION_ID, ScopeInterface::SCOPE_WEBSITE);
        $stateFrom = $stateIdFrom ? $this->getRegionById($stateIdFrom) : '';

        $nameTo = $order->getCustomerFirstname() .' '. $order->getCustomerLastname();
        $phoneTo = $order->getShippingAddress()->getTelephone();
        $mailTo = $order->getCustomerEmail();
        $addressTo = implode(" ",$order->getShippingAddress()->getStreet());
        $cityTo = $order->getShippingAddress()->getCity() ?? '';
        $stateIdTo = $order->getShippingAddress()->getRegionId();
        $stateTo = $stateIdTo ? $this->getRegionById($stateIdTo) : '';
        $postCode = $order->getShippingAddress()->getPostcode();
        if (strlen($postCode) < 5) {
            $postCode = str_pad($postCode, 5, "0", STR_PAD_LEFT);
        }

        $currentDate = $this->timezone->date()->format('Y-m-d');
        $deliveryStartDate = $this->timezone->date($order->getCreatedAt())->format('Y-m-d');

        if (strtotime($currentDate) > strtotime($deliveryStartDate)) {
            $deliveryStartDate = $currentDate;
        }
        $weight = [];
        $dataItem = [];
        foreach ($order->getAllVisibleItems() as $item) {
            $weight[] = $item->getWeight();
            $dataItem[] = [
                'item_description' => $item->getName(),
                'quantity' => (int)$item->getQtyOrdered(),
                'is_dangerous_good' => false,
            ];
        }

        $data = [
            'service_type' => 'Parcel',
            'service_level' => 'Standard',
            'reference' => [
                'merchant_order_number' => $merchantOrderNumber,
            ],
            'from' => [
                'name' => $nameFrom,
                'phone_number' => $phoneFrom,
                'email' => $mailFrom,
                'address' => [
                    'address1' => $addressFrom,
                    'address2' => '',
                    'area' => '',
                    'city' => $cityFrom,
                    'state' => $stateFrom,
                    'address_type' => 'office',
                    'country' => 'MY',
                    'postcode' => $postCodeFrom,
                ],
            ],
            'to' => [
                'name' => $nameTo,
                'phone_number' => $phoneTo,
                'email' => $mailTo,
                'address' => [
                    'address1' => $addressTo,
                    'address2' => '',
                    'area' => '',
                    'city' => $cityTo,
                    'state' => $stateTo,
                    'address_type' => 'home',
                    'country' => 'MY',
                    'postcode' => $postCode,
                ],
            ],
            'parcel_job' => [
                'is_pickup_required' => false,
                'pickup_service_type' => 'Scheduled',
                'pickup_service_level' => 'Standard',
                'pickup_address_id' => '98989012',
                'pickup_date' => '2021-12-15',
                'pickup_timeslot' => [
                    'start_time' => '09:00',
                    'end_time' => '12:00',
                    'timezone' => 'Asia/Kuala_Lumpur',
                ],
                'pickup_instructions' => '',
                'delivery_instructions' => '',
                'delivery_start_date' => $deliveryStartDate,
                'delivery_timeslot' => [
                    'start_time' => '09:00',
                    'end_time' => '12:00',
                    'timezone' => 'Asia/Kuala_Lumpur',
                ],
                'dimensions' => [
                    'weight' => array_sum($weight),
                ],
                'items' => $dataItem,
            ],
        ];
        return $data;
    }

    /**
     * @param $regionId
     * @return string
     */
    private function getRegionById($regionId): string
    {
        $regionCollection = $this->regionCollectionFactory->create();
        $region = $regionCollection->addFieldToFilter('main_table.region_id', $regionId)->getFirstItem();
        return $region->getDefaultName() ?? '';
    }
}
