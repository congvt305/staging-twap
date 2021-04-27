<?php
/**
 * @author Eguana Team
 * @copyriht Copyright (c) 2021 Eguana {http://eguanacommerce.com}
 * Created by PhpStorm
 * User: raheel
 * Date: 22/4/21
 * Time: 2:35 PM
 */
namespace Amore\Sap\Model\SapProduct;

use Amore\Sap\Api\SapSyncStockManagementInterface;
use Amore\Sap\Logger\Logger;
use Amore\Sap\Model\Source\Config;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Magento\Framework\Event\ManagerInterface;
use Magento\InventoryApi\Api\SourceItemsSaveInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\InventoryApi\Api\Data\StockSourceLinkInterface;
use Magento\Framework\Serialize\Serializer\Json;

/**
 * Class SapSyncStockManagement
 *
 * Management of stock
 */
class SapSyncStockManagement implements SapSyncStockManagementInterface
{
    /**
     * @var SourceItemsSaveInterface
     */
    private $sourceItemsSaveInterface;

    /**
     * @var StoreManagerInterface
     */
    private $storeManagerInterface;

    /**
     * @var Json
     */
    private $json;

    /**
     * @var Logger
     */
    private $logger;

    /**
     * @var Config
     */
    private $config;

    /**
     * @var ManagerInterface
     */
    private $eventManager;

    /**
     * @var \Amore\Sap\Api\Data\SyncStockResponseInterface
     */
    private $syncStockResponse;

    /**
     * @var SapProductManagement
     */
    private $sapProductManagement;

    /**
     * @var CollectionFactory
     */
    private $productCollection;

    /**
     * @param SourceItemsSaveInterface $sourceItemsSaveInterface
     * @param StoreManagerInterface $storeManagerInterface
     * @param Json $json
     * @param Logger $logger
     * @param Config $config
     * @param ManagerInterface $eventManager
     * @param \Amore\Sap\Api\Data\SyncStockResponseInterface $syncStockResponse
     * @param SapProductManagement $sapProductManagement
     * @param CollectionFactory $productCollection
     */
    public function __construct(
        SourceItemsSaveInterface $sourceItemsSaveInterface,
        StoreManagerInterface $storeManagerInterface,
        Json $json,
        Logger $logger,
        Config $config,
        ManagerInterface $eventManager,
        \Amore\Sap\Api\Data\SyncStockResponseInterface $syncStockResponse,
        SapProductManagement $sapProductManagement,
        CollectionFactory $productCollection
    ) {
        $this->json = $json;
        $this->logger = $logger;
        $this->config = $config;
        $this->eventManager = $eventManager;
        $this->productCollection = $productCollection;
        $this->syncStockResponse = $syncStockResponse;
        $this->sapProductManagement = $sapProductManagement;
        $this->storeManagerInterface = $storeManagerInterface;
        $this->sourceItemsSaveInterface = $sourceItemsSaveInterface;
    }

    /**
     * Get only that products whose Sap Integration is enabled
     *
     * @param \Amore\Sap\Api\Data\SyncStockResponseStockDataInterface[] $stockData
     * @param $sourceCode
     * @param null $storeId
     * @return array
     */
    public function getSapIntegrationEnabledProducts($stockData, $sourceCode, $storeId = null)
    {
        $filteredProducts = [];
        $skus = array_map(function ($e) {
            return is_object($e) ? $e->getMatnr() : $e['matnr'];
        }, $stockData);
        $collection = $this->productCollection->create();
        $collection->getSelect()->joinInner(
            ['si' => $collection->getTable('inventory_source_item')],
            'si.sku = e.sku',
            ['source_code']
        );
        $collection->addAttributeToSelect('sku');
        $collection->addStoreFilter($storeId);
        $collection->getSelect()->where('e.sku IN (?)', $skus);
        if ($sourceCode == 'default') {
            $collection->getSelect()->where("si.source_code = '" . $sourceCode . "'");
        } else {
            $collection->getSelect()->where("si.source_code = " . $sourceCode . " si.source_code = 'default'");
        }
        $products = $collection->getItems();
        foreach ($products as $product) {
            if (!$this->sapProductManagement->sapIntegrationCheck($product)) {
                $key = array_search($product->getSku(), $skus);
                $data = [
                    'matnr' => $product->getSku(),
                    'labst' => $stockData[$key]->getLabst(),
                    'sourceCode' => $product->getData('source_code')
                ];
                $filteredProducts[] = $data;
            }
        }
        return $filteredProducts;
    }

    /**
     * To update inventory stocks synchronously (multiple stocks)
     *
     * @param string $source
     * @param string $mallId
     * @param \Amore\Sap\Api\Data\SyncStockResponseStockDataInterface[] $stockData
     * @return \Amore\Sap\Api\Data\SyncStockResponseInterface
     */
    public function inventorySyncStockUpdate($source, $mallId, $stockData)
    {
        $response = '';
        $loggingCheck = $this->config->getLoggingCheck();

        $result = [
            'code'      => '0000',
            'message'   => 'SUCCESS',
            'data'      => []
        ];
        $parameters = [
            'source'    => $source,
            'mallId'    => $mallId,
            'stockData' => $stockData
        ];

        if ($loggingCheck) {
            $this->logger->info('***** SYNC STOCK API PARAMETERS *****');
            $this->logger->info($this->json->serialize($parameters));
        }

        if (!$source || !$mallId || !$stockData || !is_array($stockData)) {
            $result['code']     = '0001';
            $result['message']  = 'Stock data missing';
            $response = $this->getSyncStockResponse(
                $result['code'],
                $result['message'],
                $result['data'],
                $loggingCheck
            );
            $this->logSyncStockUpdate($parameters, $result['code'], $result);
            return $response;
        }

        $store = $this->sapProductManagement->getStore($mallId);
        if (empty($store)) {
            $result['code']     = '0002';
            $result['message']  = 'Mall Id ' . $mallId . ' is not specified or incorrect';
            $response = $this->getSyncStockResponse(
                $result['code'],
                $result['message'],
                $result['data'],
                $loggingCheck
            );
            $this->logSyncStockUpdate($parameters, $result['code'], $result);
            return $response;
        }

        $storeId = $store->getId();
        $sapActiveCheck = $this->config->getActiveCheck('store', $storeId);
        $sapStockSaveActiveCheck = $this->config->getProductStockActiveCheck('store', $storeId);

        if (!$sapActiveCheck || !$sapStockSaveActiveCheck) {
            $result['code']     = '0003';
            $result['message']  = 'Configuration is not enabled';
            $response = $this->getSyncStockResponse(
                $result['code'],
                $result['message'],
                $result['data'],
                $loggingCheck
            );
            $this->logSyncStockUpdate($parameters, $result['code'], $result);
            return $response;
        }

        $websiteId = $this->sapProductManagement->getStore($mallId)->getWebsiteId();
        $websiteCode = $this->storeManagerInterface->getWebsite($websiteId)->getCode();
        $sourceCode = $this->sapProductManagement->getSourceCodeByWebsiteCode($websiteCode);
        $filteredProducts = $this->getSapIntegrationEnabledProducts($stockData, $sourceCode, $storeId);

        $sourceItems = [];
        foreach ($filteredProducts as $product) {
            $data = [
                'source'    => $source,
                'mallId'    => $mallId,
                'matnr'     => $product['matnr'],
                'labst'     => $product['labst']
            ];
            $sourceItems[] = $this->sapProductManagement->saveProductQtyIntoSource($product['sourceCode'], $data);
        }

        try {
            $this->sourceItemsSaveInterface->execute($sourceItems);
        } catch (\Exception $exception) {
            $result['data'][] = [
                'message'   => $exception->getMessage()
            ];
        }

        $response = $this->getSyncStockResponse(
            $result['code'],
            $result['message'],
            $result['data'],
            $loggingCheck
        );
        $this->logSyncStockUpdate($parameters, $result['code'], $result);
        return $response;
    }

    /**
     * Get Sync Stock API Response
     *
     * @param $code
     * @param $message
     * @param array $data
     * @param false $loggingCheck
     * @return \Amore\Sap\Api\Data\SyncStockResponseInterface
     */
    private function getSyncStockResponse($code, $message, $data = [], $loggingCheck = false)
    {
        $response = [
            'code'      => $code,
            'message'   => $message,
            'data'      => $data
        ];

        if ($loggingCheck) {
            $this->logger->info('***** SYNC STOCK API RESPONSE *****');
            $this->logger->info($this->json->serialize($response));
        }

        $this->syncStockResponse->setCode($code);
        $this->syncStockResponse->setMessage($message);
        /** @var $data \Amore\Sap\Model\SapProduct\SyncStockResponseStockData[] */
        $this->syncStockResponse->setData($data);

        return $this->syncStockResponse;
    }

    /**
     * Log bulk stock update result in Operation Log
     *
     * @param $parameters
     * @param $status
     * @param $result
     */
    private function logSyncStockUpdate($parameters, $status, $result)
    {
        if ($parameters && $status && $result) {
            $this->eventManager->dispatch(
                "eguana_bizconnect_operation_processed",
                [
                    'to'                => 'Magento',
                    'status'            => $this->sapProductManagement->setOperationLogStatus($status),
                    'direction'         => 'incoming',
                    'topic_name'        => 'amore.sap.product.inventory.sync.stock',
                    'result_message'    => $this->json->serialize($result),
                    'serialized_data'   => $this->json->serialize($parameters)
                ]
            );
        }
    }
}
