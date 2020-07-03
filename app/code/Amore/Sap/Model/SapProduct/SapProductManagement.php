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
        AttributeRepositoryInterface $eavAttributeRepositoryInterface
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
    }

    public function inventoryStockUpdate(\Amore\Sap\Api\Data\SapInventoryStockInterface $stockData)
    {
        $result = [];
        $parameters = [
            $stockData['source'],
            $stockData['mallId'],
            $stockData['matnr'],
            $stockData['labst']
        ];

        if ($this->config->getLoggingCheck()) {
            $this->logger->info('STOCK DATA');
            $this->logger->info($this->json->serialize($parameters));
        }

        $storeId = $this->getStore($stockData['mallId'])->getId();

        /**
         * @var $product \Magento\Catalog\Model\Product
         */
        $product = $this->getProductBySku($stockData['matnr'], $storeId);
        if (gettype($product) == 'string') {
            $result[$stockData['matnr']] = ['code' => "0001", 'message' => $product];
        } else {
            if ($this->sapIntegrationCheck($product)) {
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
            } else {
                $result[$stockData['matnr']] = ['code' => "0001", 'message' => 'SAP Integration option is disabled. Check product option and try again.'];
            }
        }

        return $result;
    }

    public function productDetailUpdate($productsDetail)
    {
        $result = [];

        $parameters = [
            $productsDetail['source'],
            $productsDetail['matnr'],
            $productsDetail['vkorg'],
            $productsDetail['bismt'],
            $productsDetail['brgew'],
            $productsDetail['gewei'],
            $productsDetail['brand'],
            $productsDetail['bctxtKo'],
            $productsDetail['meins'],
            $productsDetail['mstav'],
            $productsDetail['spart'],
            $productsDetail['maxlz'],
            $productsDetail['breit'],
            $productsDetail['hoehe'],
            $productsDetail['laeng'],
            $productsDetail['kondm'],
            $productsDetail['mvgr1'],
            $productsDetail['mvgr2'],
            $productsDetail['prodh'],
            $productsDetail['vmsta'],
            $productsDetail['matnr2'],
            $productsDetail['setid'],
            $productsDetail['bline'],
            $productsDetail['csmtp'],
            $productsDetail['setdi'],
            $productsDetail['matshinsun'],
            $productsDetail['matvessel'],
            $productsDetail['prdvl'],
            $productsDetail['vlunt'],
            $productsDetail['cpiap'],
            $productsDetail['prdtp'],
            $productsDetail['rpfut'],
            $productsDetail['maktxEn'],
            $productsDetail['maktxZh'],
            $productsDetail['bctxtEn'],
            $productsDetail['bctxtZh'],
            $productsDetail['refill']
        ];

        if ($this->config->getLoggingCheck()) {
            $this->logger->info('PRODUCT INFO DATA');
            $this->logger->info($this->json->serialize($parameters));
        }

        /**
         * @var $product \Magento\Catalog\Model\Product
         */
        $product = $this->getProductBySku($productsDetail['matnr'], null);

        if (gettype($product) == 'string') {
            $result[$productsDetail['matnr']] = ['code' => "0001", 'message' => $product];
        } else {
            $product->setWeight((float)$productsDetail['brgew']);
            // 이전 상품 코드
//            $product->addAttributeUpdate('bismt', $productsDetail['bismt'], 0);
            // 중량단위
//            $product->addAttributeUpdate('gewei', $productsDetail['gewei'], 0);
            $product->addAttributeUpdate('brand', $this->getProductAttribute('brand', $productsDetail['brand']), 0);
            $product->addAttributeUpdate('bctxtKo', $this->getProductAttribute('bctxtKo', $productsDetail['bctxtKo']), 0);
            $product->addAttributeUpdate('meins', $this->getProductAttribute('meins', $productsDetail['meins']), 0);
            $product->addAttributeUpdate('mstav', $this->getProductAttribute('mstav',$productsDetail['mstav']), 0);
            $product->addAttributeUpdate('spart', $this->getProductAttribute('spart', $productsDetail['spart']), 0);
            $product->addAttributeUpdate('maxlz', $productsDetail['maxlz'], 0);
            $product->addAttributeUpdate('breit', $productsDetail['breit'], 0);
            $product->addAttributeUpdate('hoehe', $productsDetail['hoehe'], 0);
            $product->addAttributeUpdate('laeng', $productsDetail['laeng'], 0);
            // 세액구분코드
//            $product->addAttributeUpdate('kondm', $productsDetail['kondm'], 0);
            $product->addAttributeUpdate('mvgr1', $this->getProductAttribute('mvgr1', $productsDetail['mvgr1']), 0);
            $product->addAttributeUpdate('mvgr2', $this->getProductAttribute('mvgr2', $productsDetail['mvgr2']), 0);
            $product->addAttributeUpdate('prodh', $productsDetail['prodh'], 0);
            $product->addAttributeUpdate('vmsta', $this->getProductAttribute('vmsta', $productsDetail['vmsta']), 0);
            $product->addAttributeUpdate('matnr2', $productsDetail['matnr2'], 0);
            $product->addAttributeUpdate('setid', $this->getProductAttribute('setid', $productsDetail['setid']), 0);
            $product->addAttributeUpdate('bline', $this->getProductAttribute('bline', $productsDetail['bline']), 0);
            $product->addAttributeUpdate('csmtp', $this->getProductAttribute('csmtp', $productsDetail['csmtp']), 0);
            $product->addAttributeUpdate('setdi', $this->getProductAttribute('setdi', $productsDetail['setdi']), 0);
            $product->addAttributeUpdate('matshinsun', $this->getProductAttribute('matshinsun', $productsDetail['matshinsun']), 0);
            $product->addAttributeUpdate('matvessel', $this->getProductAttribute('matvessel', $productsDetail['matvessel']), 0);
            // 용량
            $product->addAttributeUpdate('prdvl', intval($productsDetail['prdvl']), 0);
            $product->addAttributeUpdate('vlunt', $this->getProductAttribute('vlunt', $productsDetail['vlunt']), 0);
            $product->addAttributeUpdate('cpiap', $this->getProductAttribute('cpiap', $productsDetail['cpiap']), 0);
            $product->addAttributeUpdate('prdtp', $this->getProductAttribute('prdtp', $productsDetail['prdtp']), 0);
            $product->addAttributeUpdate('rpfut', $this->getProductAttribute('rpfut',$productsDetail['rpfut']), 0);
            $product->addAttributeUpdate('maktxEn', $productsDetail['maktxEn'], 0);
            $product->addAttributeUpdate('maktxZh', $productsDetail['maktxZh'], 0);
            $product->addAttributeUpdate('bctxtEn', $productsDetail['bctxtEn'], 0);
            $product->addAttributeUpdate('bctxtZh', $productsDetail['bctxtZh'], 0);
            $product->addAttributeUpdate('refill', $this->getProductAttribute('refill', $productsDetail['refill']), 0);

            try {
                $this->productRepository->save($product);

                $result[$productsDetail['matnr']] = ['code' => "0000", 'message' => 'SUCCESS'];
            } catch (\Exception $e) {
                $result[$productsDetail['matnr']] = ['code' => "0001", 'message' => $e->getMessage()];
            }
        }

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
        $attribute = $this->eavAttributeRepositoryInterface->get('catalog_product', $attributeCode);
        $inputType = $attribute->getFrontendInput();
        $value = '';

        if ($inputType == 'select') {
            $options = $attribute->getOptions();
            foreach ($options as $option) {
                if ($option->getLabel() == $requestValue) {
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
        }
        return $value;
    }

    public function productPriceUpdate($priceData)
    {
        $result = [];

        $parameters = [
            $priceData['source'],
            $priceData['matnr'],
            $priceData['pltyp'],
            $priceData['kbetrInv'],
            $priceData['waerk'],
        ];

        if ($this->config->getLoggingCheck()) {
            $this->logger->info('PRICE DATA');
            $this->logger->info($this->json->serialize($parameters));
        }

        /**
         * @var $product \Magento\Catalog\Model\Product
         */
        $product = $this->getProductBySku($priceData['matnr'], null);

        if (gettype($product) == 'string') {
            $result[$priceData['matnr']] = ['code' => "0001", 'message' => $product];
        } else {
            try {
                $product->setPrice(floatval($priceData['kbetrInv']));
                $this->productRepository->save($product);

                $result[$priceData['matnr']] = ['code' => "0000", 'message' => 'SUCCESS'];
            } catch (\Exception $e) {
                $result[$priceData['matnr']] = ['code' => "0001", 'message' => $e->getMessage()];
            }

        }
        return $result;
    }

    /** @param $product \Magento\Catalog\Model\Product */
    public function sapIntegrationCheck($product)
    {
        if (is_null($product->getCustomAttribute('disable_sap_integration'))) {
            return null;
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
                $storeCode = 'tw_laneige';
        }
        return $storeCode;
    }
}
