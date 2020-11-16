<?php
/**
 * Created by Eguana.
 * User: Brian
 * Date: 2020-06-12
 * Time: 오후 6:01
 */

namespace Amore\Sap\Model\SapProduct;

use Amore\Sap\Api\SapProductManagementInterface;
use Amore\Sap\Logger\Logger;
use Amore\Sap\Model\Source\Config;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\ResourceModel\Product\Action;
use Magento\Eav\Api\AttributeRepositoryInterface;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\InputException;
use Magento\Framework\MessageQueue\PublisherInterface;
use Magento\Framework\Validation\ValidationException;
use Magento\Framework\Webapi\Rest\Request;
use Magento\InventoryApi\Api\Data\SourceItemInterface;
use Magento\InventoryApi\Api\Data\SourceItemInterfaceFactory;
use Magento\InventoryApi\Api\SourceItemsSaveInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Store\Api\StoreRepositoryInterface;
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
    // Laneige Mall ID
    const MALL_ID_LANEIGE = 'LANEIGE_TW';

    // Sulwhasoo Mall ID
    const MALL_ID_SULWHASOO = 'SULWHASOO_TW';

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;
    /**
     * @var ProductRepositoryInterface
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
     * @var StoreRepositoryInterface
     */
    private $storeRepository;
    /**
     * @var Action
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
     * @var Request
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
     * @var Logger
     */
    private $logger;
    /**
     * @var Config
     */
    private $config;
    /**
     * @var AttributeRepositoryInterface
     */
    private $eavAttributeRepositoryInterface;
    /**
     * @var ManagerInterface
     */
    private $eventManager;


    /**
     * SapProductManagement constructor.
     * @param ScopeConfigInterface $scopeConfig
     * @param ProductRepositoryInterface $productRepository
     * @param SourceItemInterface $sourceItemInterface
     * @param SourceItemInterfaceFactory $sourceItemInterfaceFactory
     * @param SourceItemsSaveInterface $sourceItemsSaveInterface
     * @param StoreRepositoryInterface $storeRepository
     * @param Action $productAction
     * @param ResourceConnection $resourceConnection
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param SortOrderBuilder $sortOrderBuilder
     * @param GetStockSourceLinksInterface $getStockSourceLinks
     * @param SourceRepositoryInterface $sourceRepository
     * @param StoreManagerInterface $storeManagerInterface
     * @param Request $request
     * @param Json $json
     * @param PublisherInterface $publisher
     * @param Logger $logger
     * @param Config $config
     * @param AttributeRepositoryInterface $eavAttributeRepositoryInterface
     * @param ManagerInterface $eventManager
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        ProductRepositoryInterface $productRepository,
        SourceItemInterface $sourceItemInterface,
        SourceItemInterfaceFactory $sourceItemInterfaceFactory,
        SourceItemsSaveInterface $sourceItemsSaveInterface,
        StoreRepositoryInterface $storeRepository,
        Action $productAction,
        ResourceConnection $resourceConnection,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        SortOrderBuilder $sortOrderBuilder,
        GetStockSourceLinksInterface $getStockSourceLinks,
        SourceRepositoryInterface $sourceRepository,
        StoreManagerInterface $storeManagerInterface,
        Request $request,
        Json $json,
        PublisherInterface $publisher,
        Logger $logger,
        Config $config,
        AttributeRepositoryInterface $eavAttributeRepositoryInterface,
        ManagerInterface $eventManager
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
        $this->logger = $logger;
        $this->config = $config;
        $this->eavAttributeRepositoryInterface = $eavAttributeRepositoryInterface;
        $this->eventManager = $eventManager;
    }

    public function inventoryStockUpdate(\Amore\Sap\Api\Data\SapInventoryStockInterface $stockData)
    {
        $result = [];
        $parameters = [
            'source' => $stockData['source'],
            'mallId' => $stockData['mallId'],
            'matnr' => $stockData['matnr'],
            'labst' => $stockData['labst']
        ];

        if ($this->config->getLoggingCheck()) {
            $this->logger->info('STOCK DATA');
            $this->logger->info($this->json->serialize($parameters));
        }

        $store = $this->getStore($stockData['mallId']);

        if (empty($store)) {
            $result[$stockData['matnr']] = ['code' => "0001", 'message' => "Mall Id " . $stockData['mallId'] ." is not specified. Please check configuration."];
            return $result;
        }

        $storeId = $store->getId();

        $sapStockSaveActiveCheck = $this->config->getProductStockActiveCheck('store', $storeId);

        if ($sapStockSaveActiveCheck) {
            /**
             * @var $product \Magento\Catalog\Model\Product
             */
            $product = $this->getProductBySku($stockData['matnr'], $storeId);
            if (gettype($product) == 'string') {
                $result[$stockData['matnr']] = ['code' => "0002", 'message' => $product];
            } else {
                if (!$this->sapIntegrationCheck($product)) {
                    $websiteId = $this->getStore($stockData['mallId'])->getWebsiteId();
                    $websiteCode = $this->storeManagerInterface->getWebsite($websiteId)->getCode();

                    $sourceCode = $this->getSourceCodeByWebsiteCode($websiteCode);
                    $itemExistInSource = $this->sourceItemExistingCheck($stockData['matnr'], $sourceCode);
                    $itemExistInDefault = $this->sourceItemExistingCheck($stockData['matnr'], 'default');

                    if (!empty($itemExistInSource)) {
                        $this->logger->info('PRODUCT SOURCE IS NOT DEFAULT');
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
                    } elseif (!empty($itemExistInDefault)) {
                        $this->logger->info('PRODUCT SOURCE IS DEFAULT');
                        $sourceItems[] = $this->saveProductQtyIntoSource('default', $stockData);
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
                    } else {
                        $result[$stockData['matnr']] = ['code' => "0001", 'message' => $stockData['matnr'] . ' does not exist in the source.'];
                    }
                } else {
                    $result[$stockData['matnr']] = ['code' => "0001", 'message' => 'SAP Integration option is disabled. Check product option and try again.'];
                }
            }

            $this->eventManager->dispatch(
                "eguana_bizconnect_operation_processed",
                [
                    'topic_name' => 'amore.sap.product.inventory.stock',
                    'direction' => 'incoming',
                    'to' => "Magento",
                    'serialized_data' => $this->json->serialize($parameters),
                    'status' => $this->setOperationLogStatus($result[$stockData['matnr']]['code']),
                    'result_message' => $this->json->serialize($result)
                ]
            );
        } else {
            $result[$stockData['matnr']] = ['code' => "0001", 'message' => "Configuration is not enabled"];
        }

        return $result;
    }

    public function setOperationLogStatus($code)
    {
        switch ($code) {
            case "0001":
                $result = 0;
                break;
            case "0000":
                $result = 1;
                break;
            case "0002":
                $result = 2;
                break;
            default:
                $result = 0;
        }
        return $result;
    }

    public function productDetailUpdate($productsDetail)
    {
        $result = [];

        $parameters = [
            'source' => $productsDetail['source'],
            'matnr' => $productsDetail['matnr'],
            'vkorg' => $productsDetail['vkorg'],
            'bismt' => $productsDetail['bismt'],
            'brgew' => $productsDetail['brgew'],
            'gewei' => $productsDetail['gewei'],
            'brand' => $productsDetail['brand'],
            'bctxtKo' => $productsDetail['bctxtKo'],
            'meins' => $productsDetail['meins'],
            'mstav' => $productsDetail['mstav'],
            'spart' => $productsDetail['spart'],
            'maxlz' => $productsDetail['maxlz'],
            'breit' => $productsDetail['breit'],
            'hoehe' => $productsDetail['hoehe'],
            'laeng' => $productsDetail['laeng'],
            'kondm' => $productsDetail['kondm'],
            'mvgr1' => $productsDetail['mvgr1'],
            'mvgr2' => $productsDetail['mvgr2'],
            'prodh' => $productsDetail['prodh'],
            'vmsta' => $productsDetail['vmsta'],
            'matnr2' => $productsDetail['matnr2'],
            'setid' => $productsDetail['setid'],
            'bline' => $productsDetail['bline'],
            'csmtp' => $productsDetail['csmtp'],
            'setdi' => $productsDetail['setdi'],
            'matshinsun' => $productsDetail['matshinsun'],
            'matvessel' => $productsDetail['matvessel'],
            'prdvl' => $productsDetail['prdvl'],
            'vlunt' => $productsDetail['vlunt'],
            'cpiap' => $productsDetail['cpiap'],
            'prdtp' => $productsDetail['prdtp'],
            'rpfut' => $productsDetail['rpfut'],
            'maktxEn' => $productsDetail['maktxEn'],
            'maktxZh' => $productsDetail['maktxZh'],
            'bctxtEn' => $productsDetail['bctxtEn'],
            'bctxtZh' => $productsDetail['bctxtZh'],
            'refill' => $productsDetail['refill'],
            'matcol' => $productsDetail['matcol']
        ];

        if ($this->config->getLoggingCheck()) {
            $this->logger->info('PRODUCT INFO DATA');
            $this->logger->info($this->json->serialize($parameters));
        }

        /**
         * @var $product \Magento\Catalog\Model\Product
         */
        $product = $this->getProductBySku($productsDetail['matnr'], null);
        $storeList = $this->getStoresByVkorg($productsDetail['vkorg']);

        if (gettype($product) == 'string') {
            $result[$productsDetail['matnr']] = ['code' => "0001", 'message' => $product];
        } else {
            try {
                if (gettype($storeList) == 'array') {
                    foreach ($storeList as $store) {
                        if ($this->config->getProductInfoActiveCheck('store', $store)) {

                            $product->setWeight((float)$productsDetail['brgew']);
                            // 이전 상품 코드
                            // $product->addAttributeUpdate('bismt', $productsDetail['bismt'], 0);
                            // 중량단위
                            // $product->addAttributeUpdate('gewei', $productsDetail['gewei'], 0);
                            $product->addAttributeUpdate('brand', $this->getProductAttribute('brand', $productsDetail['brand']), $store);
                            $product->addAttributeUpdate('bctxtko', $this->getProductAttribute('bctxtKo', $productsDetail['bctxtKo']), $store);
                            $product->addAttributeUpdate('meins', $this->getProductAttribute('meins', $productsDetail['meins']), $store);
                            $product->addAttributeUpdate('mstav', $this->getProductAttribute('mstav',$productsDetail['mstav']), $store);
                            $product->addAttributeUpdate('spart', $this->getProductAttribute('spart', $productsDetail['spart']), $store);
                            $product->addAttributeUpdate('maxlz', $productsDetail['maxlz'], $store);
                            $product->addAttributeUpdate('breit', $productsDetail['breit'], $store);
                            $product->addAttributeUpdate('hoehe', $productsDetail['hoehe'], $store);
                            $product->addAttributeUpdate('laeng', $productsDetail['laeng'], $store);
                            // 세액구분코드
                            // $product->addAttributeUpdate('kondm', $productsDetail['kondm'], 0);
                            $product->addAttributeUpdate('mvgr1', $this->getProductAttribute('mvgr1', $productsDetail['mvgr1']), $store);
                            $product->addAttributeUpdate('mvgr2', $this->getProductAttribute('mvgr2', $productsDetail['mvgr2']), $store);
                            $product->addAttributeUpdate('prodh', $productsDetail['prodh'], $store);
                            $product->addAttributeUpdate('vmsta', $this->getProductAttribute('vmsta', $productsDetail['vmsta']), $store);
                            $product->addAttributeUpdate('matnr2', $productsDetail['matnr2'], $store);
                            $product->addAttributeUpdate('setid', $this->getProductAttribute('setid', $productsDetail['setid']), $store);
                            $product->addAttributeUpdate('bline', $this->getProductAttribute('bline', $productsDetail['bline']), $store);
                            $product->addAttributeUpdate('csmtp', $this->getProductAttribute('csmtp', $productsDetail['csmtp']), $store);
                            $product->addAttributeUpdate('setdi', $this->getProductAttribute('setdi', $productsDetail['setdi']), $store);
                            $product->addAttributeUpdate('matshinsun', $this->getProductAttribute('matshinsun', $productsDetail['matshinsun']), $store);
                            $product->addAttributeUpdate('matvessel', $this->getProductAttribute('matvessel', $productsDetail['matvessel']), $store);
                            // 용량
                            $product->addAttributeUpdate('prdvl', intval($productsDetail['prdvl']), $store);
                            $product->addAttributeUpdate('vlunt', $this->getProductAttribute('vlunt', $productsDetail['vlunt']), $store);
                            $product->addAttributeUpdate('cpiap', $this->getProductAttribute('cpiap', $productsDetail['cpiap']), $store);
                            $product->addAttributeUpdate('prdtp', $this->getProductAttribute('prdtp', $productsDetail['prdtp']), $store);
                            $product->addAttributeUpdate('rpfut', $this->getProductAttribute('rpfut',$productsDetail['rpfut']), $store);
                            $product->addAttributeUpdate('maktxen', $productsDetail['maktxEn'], $store);
                            $product->addAttributeUpdate('maktxzh', $productsDetail['maktxZh'], $store);
                            $product->addAttributeUpdate('bctxten', $productsDetail['bctxtEn'], $store);
                            $product->addAttributeUpdate('bctxtzh', $productsDetail['bctxtZh'], $store);
                            $product->addAttributeUpdate('refill', $this->getProductAttribute('refill', $productsDetail['refill']), $store);
                            $product->setStoreId($store);
                            $this->productRepository->save($product);
                        }
                    }
                    $result[$productsDetail['matnr']] = ['code' => "0000", 'message' => 'SUCCESS'];
                } else {
                    if ($this->config->getProductInfoActiveCheck('default', null)) {
                        $product->setWeight((float)$productsDetail['brgew']);
                        // 이전 상품 코드
                        //            $product->addAttributeUpdate('bismt', $productsDetail['bismt'], 0);
                        // 중량단위
                        //            $product->addAttributeUpdate('gewei', $productsDetail['gewei'], 0);
                        $product->addAttributeUpdate('brand', $this->getProductAttribute('brand', $productsDetail['brand']), $storeList);
                        $product->addAttributeUpdate('bctxtko', $this->getProductAttribute('bctxtKo', $productsDetail['bctxtKo']), $storeList);
                        $product->addAttributeUpdate('meins', $this->getProductAttribute('meins', $productsDetail['meins']), $storeList);
                        $product->addAttributeUpdate('mstav', $this->getProductAttribute('mstav',$productsDetail['mstav']), $storeList);
                        $product->addAttributeUpdate('spart', $this->getProductAttribute('spart', $productsDetail['spart']), $storeList);
                        $product->addAttributeUpdate('maxlz', $productsDetail['maxlz'], $storeList);
                        $product->addAttributeUpdate('breit', $productsDetail['breit'], $storeList);
                        $product->addAttributeUpdate('hoehe', $productsDetail['hoehe'], $storeList);
                        $product->addAttributeUpdate('laeng', $productsDetail['laeng'], $storeList);
                        // 세액구분코드
                        //            $product->addAttributeUpdate('kondm', $productsDetail['kondm'], 0);
                        $product->addAttributeUpdate('mvgr1', $this->getProductAttribute('mvgr1', $productsDetail['mvgr1']), $storeList);
                        $product->addAttributeUpdate('mvgr2', $this->getProductAttribute('mvgr2', $productsDetail['mvgr2']), $storeList);
                        $product->addAttributeUpdate('prodh', $productsDetail['prodh'], $storeList);
                        $product->addAttributeUpdate('vmsta', $this->getProductAttribute('vmsta', $productsDetail['vmsta']), $storeList);
                        $product->addAttributeUpdate('matnr2', $productsDetail['matnr2'], $storeList);
                        $product->addAttributeUpdate('setid', $this->getProductAttribute('setid', $productsDetail['setid']), $storeList);
                        $product->addAttributeUpdate('bline', $this->getProductAttribute('bline', $productsDetail['bline']), $storeList);
                        $product->addAttributeUpdate('csmtp', $this->getProductAttribute('csmtp', $productsDetail['csmtp']), $storeList);
                        $product->addAttributeUpdate('setdi', $this->getProductAttribute('setdi', $productsDetail['setdi']), $storeList);
                        $product->addAttributeUpdate('matshinsun', $this->getProductAttribute('matshinsun', $productsDetail['matshinsun']), $storeList);
                        $product->addAttributeUpdate('matvessel', $this->getProductAttribute('matvessel', $productsDetail['matvessel']), $storeList);
                        // 용량
                        $product->addAttributeUpdate('prdvl', intval($productsDetail['prdvl']), $storeList);
                        $product->addAttributeUpdate('vlunt', $this->getProductAttribute('vlunt', $productsDetail['vlunt']), $storeList);
                        $product->addAttributeUpdate('cpiap', $this->getProductAttribute('cpiap', $productsDetail['cpiap']), $storeList);
                        $product->addAttributeUpdate('prdtp', $this->getProductAttribute('prdtp', $productsDetail['prdtp']), $storeList);
                        $product->addAttributeUpdate('rpfut', $this->getProductAttribute('rpfut',$productsDetail['rpfut']), $storeList);
                        $product->addAttributeUpdate('maktxen', $productsDetail['maktxEn'], $storeList);
                        $product->addAttributeUpdate('maktxzh', $productsDetail['maktxZh'], $storeList);
                        $product->addAttributeUpdate('bctxten', $productsDetail['bctxtEn'], $storeList);
                        $product->addAttributeUpdate('bctxtzh', $productsDetail['bctxtZh'], $storeList);
                        $product->addAttributeUpdate('refill', $this->getProductAttribute('refill', $productsDetail['refill']), $storeList);
                        $product->setStoreId($storeList);
                        $this->productRepository->save($product);

                        $result[$productsDetail['matnr']] = ['code' => "0000", 'message' => 'SUCCESS'];
                    }
                }
            } catch (NoSuchEntityException $e) {
                $result[$productsDetail['matnr']] = ['code' => "0001", 'message' => $e->getMessage()];
            } catch (\Exception $e) {
                $result[$productsDetail['matnr']] = ['code' => "0001", 'message' => $e->getMessage()];
            }
        }

        $this->eventManager->dispatch(
            "eguana_bizconnect_operation_processed",
            [
                'topic_name' => 'amore.sap.product.detail.info',
                'direction' => 'incoming',
                'to' => "Magento",
                'serialized_data' => $this->json->serialize($parameters),
                'status' => $result[$productsDetail['matnr']]['code'] == "0000" ? 1 : 0,
                'result_message' => $this->json->serialize($result)
            ]
        );

        return $result;
    }

    /**
     * @param $attributeCode string
     * @param $requestValue
     * @return int
     * @throws NoSuchEntityException
     */
    public function getProductAttribute($attributeCode, $requestValue)
    {
        try {
            $attribute = $this->eavAttributeRepositoryInterface->get('catalog_product', $attributeCode);
            $inputType = $attribute->getFrontendInput();
            $value = '';

            if ($inputType == 'select') {
                $options = $attribute->getOptions();
                foreach ($options as $option) {
                    if ($option->getLabel() == $requestValue ||
                        $option->getLabel() == strtolower($requestValue) ||
                        $option->getLabel() == strtoupper($requestValue)) {
                        $value = $option->getValue();
                        break;
                    }
                }
            } elseif ($inputType == 'boolean') {
                switch ($requestValue) {
                    case 'Y':
                        $value = 1;
                        break;
                    case 'N':
                        $value = 0;
                        break;
                    default:
                        $value = 0;
                }
            } elseif ($inputType == 'multiselect') {
                $options = $attribute->getOptions();
                $arrayValue = explode(",", $requestValue);
                foreach ($options as $option) {
                    if (in_array($option->getLabel(), $arrayValue)) {
                        if ($value == '') {
                            $value = $option->getValue();
                        } else {
                            $value = $value . "," . $option->getValue();
                        }
                    }
                }
            } elseif ($inputType == "text") {
                $value = $requestValue;
            }
            return $value;
        } catch (NoSuchEntityException $exception) {
            return null;
        }
    }

    public function sourceItemExistingCheck($sku, $sourceCode)
    {
        $connection = $this->resourceConnection->getConnection();
        $table = $connection->getTableName('inventory_source_item');
        $query = "SELECT * FROM $table WHERE sku = '$sku' AND source_code = '$sourceCode'";

        try {
            return $connection->fetchAll($query);
        } catch (\Exception $exception) {
            return null;
        }
    }

    public function productPriceUpdate($priceData)
    {
        $result = [];

        $parameters = [
            'source' => $priceData['source'],
            'matnr' => $priceData['matnr'],
            'pltyp' => $priceData['pltyp'],
            'kbetrInv' => $priceData['kbetrInv'],
            'waerk' => $priceData['waerk'],
        ];

        if ($this->config->getLoggingCheck()) {
            $this->logger->info('PRICE DATA');
            $this->logger->info($this->json->serialize($parameters));
        }

//        /**
//         * @var $product \Magento\Catalog\Model\Product
//         */
//        $product = $this->getProductBySku($priceData['matnr'], null);
//
//        if (gettype($product) == 'string') {
//            $result[$priceData['matnr']] = ['code' => "0001", 'message' => $product];
//        } else {
//            try {
//                $product->setPrice(floatval($priceData['kbetrInv']));
//                $this->productRepository->save($product);
//
//                $result[$priceData['matnr']] = ['code' => "0000", 'message' => 'SUCCESS'];
//            } catch (\Exception $e) {
//                $result[$priceData['matnr']] = ['code' => "0001", 'message' => $e->getMessage()];
//            }
//
//        }
        $result[$priceData['matnr']] = ['code' => "0001", 'message' => "Price API is off."];

        $this->eventManager->dispatch(
            "eguana_bizconnect_operation_processed",
            [
                'topic_name' => 'amore.sap.product.price.update',
                'direction' => 'incoming',
                'to' => "Magento",
                'serialized_data' => $this->json->serialize($parameters),
                'status' => $result[$priceData['matnr']]['code'] == "0000" ? 1 : 0,
                'result_message' => $this->json->serialize($result)
            ]
        );

        return $result;
    }

    /** @param $product \Magento\Catalog\Model\Product */
    public function sapIntegrationCheck($product)
    {
        if (is_null($product->getCustomAttribute('disable_sap_integration'))) {
            return false;
        } else {
            return $product->getCustomAttribute('disable_sap_integration')->getValue();
        }
    }

    public function getProductBySku($sku, $storeId = null)
    {
        try {
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
        if ($stockData['labst'] > 0) {
            $sourceItem->setStatus(1);
        } else {
            $sourceItem->setStatus(0);
        }

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
        $exactStore = 0;
        $stores = $this->storeManagerInterface->getStores();
        foreach ($stores as $store) {
            $configMallId = $this->config->getMallId('store', $store->getId());
            if ($mallId == $configMallId) {
                $exactStore = $store;
                break;
            }
        }
        return $exactStore;
    }

    public function getStoresByVkorg($vkorg)
    {
        $stores = $this->storeManagerInterface->getStores();
        $allStoreViewId = 0;

        $storeIdList = [];
        foreach ($stores as $store) {
            $vkorgByStore = $this->config->getSalesOrg('store', $store->getId());
            if ($vkorg == $vkorgByStore) {
                $storeIdList[] = $store->getId();
            }
        }

        if (empty($storeIdList)) {
            return $allStoreViewId;
        } else {
            return $storeIdList;
        }
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
}
