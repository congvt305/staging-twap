<?php
/**
 * @author Eguana Team
 * @copyriht Copyright (c) 2020 Eguana {http://eguanacommerce.com}
 * Created by PhpStorm
 * User: raheel
 * Date: 18/3/21
 * Time: 6:14 PM
 */
declare(strict_types=1);

namespace Amore\Sap\Model\SapProduct;

use Amore\Sap\Logger\Logger as SapLogger;
use Amore\Sap\Model\Source\Config;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\HTTP\Client\Curl;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Store\Model\StoreManagerInterface;
use Psr\Log\LoggerInterface;

/**
 * To get currnent inventory stock data from SAP
 *
 * Class SapInventoryData
 */
class SapInventoryData
{
    /**
     * @var Config
     */
    private $sapConfig;

    /**
     * @var SapLogger
     */
    private $sapLogger;

    /**
     * @var Curl
     */
    private $curlClient;

    /**
     * @var Json
     */
    private $json;

    /**
     * @var ManagerInterface
     */
    private $eventManager;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @param Curl $curl
     * @param Json $json
     * @param Config $sapConfig
     * @param SapLogger $sapLogger
     * @param LoggerInterface $logger
     * @param ManagerInterface $eventManager
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        Curl $curl,
        Json $json,
        Config $sapConfig,
        SapLogger $sapLogger,
        LoggerInterface $logger,
        ManagerInterface $eventManager,
        StoreManagerInterface $storeManager
    ) {
        $this->json = $json;
        $this->logger = $logger;
        $this->sapConfig = $sapConfig;
        $this->sapLogger = $sapLogger;
        $this->curlClient = $curl;
        $this->eventManager = $eventManager;
        $this->storeManager = $storeManager;
    }

    /**
     * Get SAP Inventory Stock Data
     *
     * @param string $mallId
     * @param \Amore\Sap\Api\Data\SyncStockResponseStockDataInterface[] $IT_DATA
     * @return mixed
     */
    public function getStockInfo($mallId, $IT_DATA)
    {
        $status = 0;
        $websiteName = '';
        $response = ['message' => 'FAILED'];
        $url = $this->sapConfig->getSapInventoryStcokInfoUrl();
        if ($IT_DATA && $mallId && $url) {
            try {
                $parameters = [
                    'MALL_ID' => $mallId,
                    'IT_DATA' => $IT_DATA
                ];
                $websiteName = $this->storeManager->getWebsite()->getName();
                $jsonEncodedData = $this->json->serialize($parameters);

                $this->curlClient->setOptions([
                    CURLOPT_URL => $url,
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_ENCODING => "",
                    CURLOPT_MAXREDIRS => 10,
                    CURLOPT_TIMEOUT => 0,
                    CURLOPT_FOLLOWLOCATION => true,
                    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                    CURLOPT_CUSTOMREQUEST => "POST",
                    CURLOPT_POSTFIELDS => $jsonEncodedData,
                    CURLOPT_HTTPHEADER => [
                        'Content-type: application/json'
                    ],
                ]);

                if ($this->sapConfig->getSslVerification('default', 0)) {
                    $this->curlClient->setOption(CURLOPT_SSL_VERIFYHOST, false);
                    $this->curlClient->setOption(CURLOPT_SSL_VERIFYPEER, false);
                }

                if ($this->sapConfig->getLoggingCheck()) {
                    $this->sapLogger->info('***** GET SAP INVENTORY STOCK *****');
                    $this->sapLogger->info($jsonEncodedData);
                }

                $this->curlClient->post($url, $parameters);
                $apiRespone = $this->curlClient->getBody();
                $response = $this->json->unserialize($apiRespone);
                $status = 1;

                if ($this->sapConfig->getLoggingCheck()) {
                    $this->sapLogger->info('***** GET SAP INVENTORY STOCK RESPONSE *****');
                    $this->sapLogger->info($apiRespone);
                }
            } catch (\Exception $exception) {
                if ($this->sapConfig->getLoggingCheck()) {
                    $this->sapLogger->error('***** ERROR GET SAP INVENTORY STOCK RESPONSE *****');
                    $this->sapLogger->error($exception->getMessage());
                } else {
                    $this->logger->error($exception->getMessage());
                }
                $response['message'] = $exception->getMessage();
            }
        }

        $this->eventManager->dispatch(
            "eguana_bizconnect_operation_processed",
            [
                'to'                => $websiteName,
                'status'            => $status,
                'direction'         => 'outgoing',
                'topic_name'        => 'amore.sap.get.stock.data',
                'result_message'    => $this->json->serialize($response),
                'serialized_data'   => $this->json->serialize($parameters)
            ]
        );
        return $response;
    }

    /**
     * Get Allow Statuses
     *
     * @return string[]
     */
    public function getAllowedStatuses()
    {
        return [
            'preparing',
            'sap_processing',
            'processing_with_shipment',
            'sap_success',
            'sap_fail'
        ];
    }
}
