<?php
/**
 * Created by Eguana.
 * User: Brian
 * Date: 2020-06-12
 * Time: 오후 6:01
 */

namespace Amore\Sap\Model\SapProduct;

use Amore\Sap\Api\SapProductManagementInterface;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\StateException;
use Magento\Framework\MessageQueue\PublisherInterface;
use Magento\Framework\Validation\ValidationException;
use Magento\InventoryApi\Api\Data\SourceItemInterface;
use Magento\InventoryApi\Api\Data\SourceItemInterfaceFactory;
use Magento\InventoryApi\Api\SourceItemsSaveInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\App\ResourceConnection;
use Magento\InventoryApi\Api\Data\StockSourceLinkInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Api\SortOrderBuilder;
use Magento\InventoryApi\Api\GetStockSourceLinksInterface;
use Magento\InventoryApi\Api\SourceRepositoryInterface;
use Magento\Framework\Serialize\Serializer\Json;

class SapProductManagement implements SapProductManagementInterface
{
    const MALL_ID_LANEIGE = 'laneige';

    const MALL_ID_SULWHASOO = 'sulwhasoo';

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;
    /**
     * @var \Magento\Catalog\Api\ProductRepositoryInterface
     */
    private $productRepository;
    /**
     * @var SourceItemInterface
     */
    private $sourceItemInterface;
    /**
     * @var SourceItemInterfaceFactory
     */
    private $sourceItemInterfaceFactory;
    /**
     * @var SourceItemsSaveInterface
     */
    private $sourceItemsSaveInterface;
    /**
     * @var \Magento\Store\Api\StoreRepositoryInterface
     */
    private $storeRepository;
    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product\Action
     */
    private $productAction;
    /**
     * @var ResourceConnection
     */
    private $resourceConnection;
    /**
     * @var SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;
    /**
     * @var SortOrderBuilder
     */
    private $sortOrderBuilder;
    /**
     * @var GetStockSourceLinksInterface
     */
    private $getStockSourceLinks;
    /**
     * @var SourceRepositoryInterface
     */
    private $sourceRepository;
    /**
     * @var StoreManagerInterface
     */
    private $storeManagerInterface;
    /**
     * @var \Magento\Framework\Webapi\Rest\Request
     */
    private $request;
    /**
     * @var Json
     */
    private $json;
    /**
     * @var PublisherInterface
     */
    private $publisher;


    /**
     * SapProductManagement constructor.
     * @param ScopeConfigInterface $scopeConfig
     * @param \Magento\Catalog\Api\ProductRepositoryInterface $productRepository
     * @param SourceItemInterface $sourceItemInterface
     * @param SourceItemInterfaceFactory $sourceItemInterfaceFactory
     * @param SourceItemsSaveInterface $sourceItemsSaveInterface
     * @param \Magento\Store\Api\StoreRepositoryInterface $storeRepository
     * @param \Magento\Catalog\Model\ResourceModel\Product\Action $productAction
     * @param ResourceConnection $resourceConnection
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param SortOrderBuilder $sortOrderBuilder
     * @param GetStockSourceLinksInterface $getStockSourceLinks
     * @param SourceRepositoryInterface $sourceRepository
     * @param StoreManagerInterface $storeManagerInterface
     * @param \Magento\Framework\Webapi\Rest\Request $request
     * @param Json $json
     * @param PublisherInterface $publisher
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
        SourceItemInterface $sourceItemInterface,
        SourceItemInterfaceFactory $sourceItemInterfaceFactory,
        SourceItemsSaveInterface $sourceItemsSaveInterface,
        \Magento\Store\Api\StoreRepositoryInterface $storeRepository,
        \Magento\Catalog\Model\ResourceModel\Product\Action $productAction,
        ResourceConnection $resourceConnection,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        SortOrderBuilder $sortOrderBuilder,
        GetStockSourceLinksInterface $getStockSourceLinks,
        SourceRepositoryInterface $sourceRepository,
        StoreManagerInterface $storeManagerInterface,
        \Magento\Framework\Webapi\Rest\Request $request,
        Json $json,
        PublisherInterface $publisher
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->productRepository = $productRepository;
        $this->sourceItemInterface = $sourceItemInterface;
        $this->sourceItemInterfaceFactory = $sourceItemInterfaceFactory;
        $this->sourceItemsSaveInterface = $sourceItemsSaveInterface;
        $this->storeRepository = $storeRepository;
        $this->productAction = $productAction;
        $this->resourceConnection = $resourceConnection;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->sortOrderBuilder = $sortOrderBuilder;
        $this->getStockSourceLinks = $getStockSourceLinks;
        $this->sourceRepository = $sourceRepository;
        $this->storeManagerInterface = $storeManagerInterface;
        $this->request = $request;
        $this->json = $json;
        $this->publisher = $publisher;
    }

    public function inventoryStockUpdate(\Amore\Sap\Api\Data\SapInventoryStockInterface $stockData)
    {
        $writer = new \Zend\Log\Writer\Stream(BP . sprintf('/var/log/%s_inventory_stock.log',date('Ymd')));
        $logger = new \Zend\Log\Logger();
        $logger->addWriter($writer);
        $logger->info(__METHOD__);

        $logger->info(print_r($stockData->getData(), true));
        $result = [];

        $source = $this->request->getParam('source');
        $storeId = $this->getStore($stockData['mallId'])->getId();

        $logger->info($storeId);

//        foreach ($stockData as $stockDatum) {
//            /**
//             * @var $product \Magento\Catalog\Model\Product
//             */
//            $product = $this->getProductBySku($stockDatum['matnr'], $storeId);
//            if (gettype($product) == 'string') {
//                $result[$stockDatum['matnr']] = ['code' => "0001", 'message' => $product];
//                continue;
//            }
//
//            $websiteId = $this->getStore($mallId)->getWebsiteId();
//            $websiteCode = $this->storeManagerInterface->getWebsite($websiteId)->getCode();
//
//            $sourceCode = $this->getSourceCodeByWebsiteCode($websiteCode);
//
//            $sourceItems[] = $this->saveProductQtyIntoSource($sourceCode, $stockDatum);
//
//            try {
//                $this->sourceItemsSaveInterface->execute($sourceItems);
//                $result[$stockDatum['matnr']] = ['code' => "0000", 'message' => 'SUCCESS'];
//            } catch (CouldNotSaveException $e) {
//                $result[$stockDatum['matnr']] = ['code' => "0001", 'message' => $e->getMessage()];
//            } catch (InputException $e) {
//                $result[$stockDatum['matnr']] = ['code' => "0001", 'message' => $e->getMessage()];
//            } catch (ValidationException $e) {
//                $result[$stockDatum['matnr']] = ['code' => "0001", 'message' => $e->getMessage()];
//            }
//        }

        /**
         * @var $product \Magento\Catalog\Model\Product
         */
        $product = $this->getProductBySku($stockData['matnr'], $storeId);
        if (gettype($product) == 'string') {
            $result[$stockData['matnr']] = ['code' => "0001", 'message' => $product];
        } else {
            $websiteId = $this->getStore($stockData['mallId'])->getWebsiteId();
            $websiteCode = $this->storeManagerInterface->getWebsite($websiteId)->getCode();

            $sourceCode = $this->getSourceCodeByWebsiteCode($websiteCode);

            $sourceItems[] = $this->saveProductQtyIntoSource($sourceCode, $stockData);

            try {
                $this->sourceItemsSaveInterface->execute($sourceItems);
                $result[$stockData['matnr']] = ['code' => "0000", 'message' => 'SUCCESS'];
            } catch (CouldNotSaveException $e) {
                $result[$stockData['matnr']] = ['code' => "0001", 'message' => $e->getMessage()];
            } catch (InputException $e) {
                $result[$stockData['matnr']] = ['code' => "0001", 'message' => $e->getMessage()];
            } catch (ValidationException $e) {
                $result[$stockData['matnr']] = ['code' => "0001", 'message' => $e->getMessage()];
            }
        }

        return $result;
//        return $stockData;
    }

    public function productDetailUpdate($productsDetail)
    {
        $result = [];

        /**
         * @var $product \Magento\Catalog\Model\Product
         */
        $product = $this->getProductBySku($productsDetail['matnr'], null);

        if (gettype($product) == 'string') {
            $result[$productsDetail['matnr']] = ['code' => "0001", 'message' => $product];
        }

//        $attributeData = [
//            'bismt' => $productsDetail['bismt'],
//            'gewei' => $productsDetail['gewei'],
//            'brand' => $productsDetail['brand'],
//            'bctxtKo' => $productsDetail['bctxtKo'],
//            'meins' => $productsDetail['meins'],
//            'mstav' => $productsDetail['mstav'],
//            'spart' => $productsDetail['spart'],
//            'maxlz' => $productsDetail['maxlz'],
//            'breit' => $productsDetail['breit'],
//            'hoehe' => $productsDetail['hoehe'],
//            'laeng' => $productsDetail['laeng'],
//            'kondm' => $productsDetail['kondm'],
//            'mvgr1' => $productsDetail['mvgr1'],
//            'mvgr2' => $productsDetail['mvgr2'],
//            'prodh' => $productsDetail['prodh'],
//            'vmsta' => $productsDetail['vmsta'],
//            'matnr2' => $productsDetail['matnr2'],
//            'setid' => $productsDetail['setid'],
//            'bline' => $productsDetail['bline'],
//            'csmtp' => $productsDetail['csmtp'],
//            'setdi' => $productsDetail['setdi'],
//            'matshinsun' => $productsDetail['matshinsun'],
//            'matvessel' => $productsDetail['matvessel'],
//            'prdvl' => $productsDetail['prdvl'],
//            'vlunt' => $productsDetail['vlunt'],
//            'cpiap' => $productsDetail['cpiap'],
//            'prdtp' => $productsDetail['prdtp'],
//            'rpfut' => $productsDetail['rpfut'],
//            'maktxEn' => $productsDetail['maktxEn'],
//            'maktxZh' => $productsDetail['maktxZh'],
//            'bctxEn' => $productsDetail['bctxEn'],
//            'bctxZh' => $productsDetail['bctxZh'],
//            'refill' => $productsDetail['refill']
//        ];

        $product->setCustomAttribute('bismt', $productsDetail['bismt']);
        $product->setCustomAttribute('gewei', $productsDetail['gewei']);
        $product->setCustomAttribute('brand', $productsDetail['brand']);
        $product->setCustomAttribute('bctxtKo', $productsDetail['bctxtKo']);
        $product->setCustomAttribute('meins', $productsDetail['meins']);
        $product->setCustomAttribute('mstav', $productsDetail['mstav']);
        $product->setCustomAttribute('spart', $productsDetail['spart']);
        $product->setCustomAttribute('maxlz', $productsDetail['maxlz']);
        $product->setCustomAttribute('breit', $productsDetail['breit']);
        $product->setCustomAttribute('hoehe', $productsDetail['hoehe']);
        $product->setCustomAttribute('laeng', $productsDetail['laeng']);
        $product->setCustomAttribute('kondm', $productsDetail['kondm']);
        $product->setCustomAttribute('mvgr1', $productsDetail['mvgr1']);
        $product->setCustomAttribute('mvgr2', $productsDetail['mvgr2']);
        $product->setCustomAttribute('prodh', $productsDetail['prodh']);
        $product->setCustomAttribute('vmsta', $productsDetail['vmsta']);
        $product->setCustomAttribute('matnr2', $productsDetail['matnr2']);
        $product->setCustomAttribute('setid', $productsDetail['setid']);
        $product->setCustomAttribute('bline', $productsDetail['bline']);
        $product->setCustomAttribute('csmtp', $productsDetail['csmtp']);
        $product->setCustomAttribute('setdi', $productsDetail['setdi']);
        $product->setCustomAttribute('matshinsun', $productsDetail['matshinsun']);
        $product->setCustomAttribute('matvessel', $productsDetail['matvessel']);
        $product->setCustomAttribute('prdvl', $productsDetail['prdvl']);
        $product->setCustomAttribute('vlunt', $productsDetail['vlunt']);
        $product->setCustomAttribute('cpiap', $productsDetail['cpiap']);
        $product->setCustomAttribute('prdtp', $productsDetail['prdtp']);
        $product->setCustomAttribute('rpfut', $productsDetail['rpfut']);
        $product->setCustomAttribute('maktxEn', $productsDetail['maktxEn']);
        $product->setCustomAttribute('maktxZh', $productsDetail['maktxZh']);
        $product->setCustomAttribute('bctxEn', $productsDetail['bctxEn']);
        $product->setCustomAttribute('bctxZh', $productsDetail['bctxZh']);
        $product->setCustomAttribute('refill', $productsDetail['refill']);
        $product->setWeight($productsDetail['brgew']);

        try {
            $this->productRepository->save($product);

            $result[$productsDetail['matnr']] = ['code' => "0000", 'message' => 'SUCCESS'];
        } catch (\Exception $e) {
            $result[$productsDetail['matnr']] = ['code' => "0001", 'message' => $e->getMessage()];
        }
    }

    public function productPriceUpdate($priceData)
    {
        $result = [];

        /**
         * @var $product \Magento\Catalog\Model\Product
         */
        $product = $this->getProductBySku($priceData['matnr'], null);

        if (gettype($product) == 'string') {
            $result[$priceData['matnr']] = ['code' => "0001", 'message' => $product];
        } else {
            $product->setPrice(floatval($priceData['kbetrInv']));

            try {
//            $this->productAction->updateAttributes($productIds, $attributeData, $storeId);
                $this->productRepository->save($product);

                $result[$priceData['matnr']] = ['code' => "0000", 'message' => 'SUCCESS'];
            } catch (\Exception $e) {
                $result[$priceData['matnr']] = ['code' => "0001", 'message' => $e->getMessage()];
            }

        }
        return $result;
    }

    public function getProductBySku($sku, $storeId = null)
    {
        $writer = new \Zend\Log\Writer\Stream(BP . sprintf('/var/log/%s_inventory_stock.log',date('Ymd')));
        $logger = new \Zend\Log\Logger();
        $logger->addWriter($writer);
        $logger->info(__METHOD__);

        try {
            $logger->info($storeId);
            $logger->info($this->productRepository->get($sku, false, $storeId)->getSku());
            return $this->productRepository->get($sku, false, $storeId);
        } catch (NoSuchEntityException $e) {
            return $e->getMessage();
        }
    }

    public function saveProduct($product)
    {
        try {
            $this->productRepository->save($product);
            $result = ['code' => "0000", 'message' => 'SUCCESS'];
        } catch (\Exception $e) {
            $result = ['code' => "0001", 'message' => $e->getMessage()];
        }

        return $result;
    }

    public function saveProductQtyIntoSource($source, $stockData)
    {
        /** @var SourceItemInterface $sourceItem */
        $sourceItem = $this->sourceItemInterfaceFactory->create();
        $sourceItem->setSourceCode($source);
        $sourceItem->setSku($stockData['matnr']);
        $sourceItem->setQuantity($stockData['labst']);
        $sourceItem->setStatus(1);

        return $sourceItem;
    }

    public function getStoreCurrencyCode($mallId)
    {
        /** @var \Magento\Store\Model\Store $store */
        $store = $this->storeRepository->get($mallId);

        return $store->getCurrentCurrencyCode();
    }

    public function getStore($mallId)
    {
        $storeCode = $this->getStoreCodeByMallCode($mallId);

        return $this->storeRepository->get($storeCode);
    }

    public function getSourceCodeByWebsiteCode($websiteCode)
    {
        $tableName = $this->resourceConnection->getTableName('inventory_stock_sales_channel');
        $connection = $this->resourceConnection->getConnection();
        $query = $connection
            ->select()
            ->distinct()
            ->from($tableName, 'stock_id')
            ->where('code = ?', $websiteCode);

        $stockId = $connection->fetchCol($query);

        $sortOrder = $this->sortOrderBuilder
            ->setField(StockSourceLinkInterface::PRIORITY)
            ->setAscendingDirection()
            ->create();
        $searchCriteria = $this->searchCriteriaBuilder
            ->addFilter(StockSourceLinkInterface::STOCK_ID, $stockId)
            ->addSortOrder($sortOrder)
            ->create();

        $searchResult = $this->getStockSourceLinks->execute($searchCriteria);

        if ($searchResult->getTotalCount() === 0) {
            return [];
        }

        $assignedSources = [];
        foreach ($searchResult->getItems() as $link) {
            $assignedSources[] = $link->getSourceCode();
        }

        return $assignedSources[0];
    }

    public function getStoreCodeByMallCode($mallId)
    {
        switch ($mallId) {
            case self::MALL_ID_SULWHASOO:
                $storeCode = 'default';
                break;
            case self::MALL_ID_LANEIGE:
                $storeCode = 'tw_laneige';
                break;
            default:
                $storeCode = 'default';
        }
        return $storeCode;
    }

    public function inventoryStockUpdateTest(\Amore\Sap\Api\Data\SapInventoryStockInterface $stockData)
    {
        $writer = new \Zend\Log\Writer\Stream(BP . sprintf('/var/log/%s_mq_test.log',date('Ymd')));
        $logger = new \Zend\Log\Logger();
        $logger->addWriter($writer);
        $logger->info(__METHOD__);

        $logger->info(print_r($stockData->getData(), true));
        $result = [];

        $source = $this->request->getParam('source');
        $storeId = $this->getStore($stockData['mallId'])->getId();

        $logger->info($storeId);

        $jsonData = $this->json->serialize($stockData->getData());

        $this->publisher->publish('sap.inventory.stock.topic', $jsonData);

    }
}
