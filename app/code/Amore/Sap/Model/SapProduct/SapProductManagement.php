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
    )
    {
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
        $result = [];

//        $source = $this->request->getParam('source');
        $storeId = $this->getStore($stockData['mallId'])->getId();

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
        } else {
            $product->setWeight((float)$productsDetail['brgew']);
//            $product->addAttributeUpdate('bismt', $productsDetail['bismt'], 0);
            // 중량단위
//            $product->addAttributeUpdate('gewei', $productsDetail['gewei'], 0);
//            $product->addAttributeUpdate('brand', $productsDetail['brand'], 0);
            $product->addAttributeUpdate('bctxtKo', $productsDetail['bctxtKo'], 0);
//            $product->addAttributeUpdate('meins', $productsDetail['meins'], 0);
            $product->addAttributeUpdate('mstav', $productsDetail['mstav'], 0);
//            $product->addAttributeUpdate('spart', $productsDetail['spart'], 0);
            $product->addAttributeUpdate('maxlz', $productsDetail['maxlz'], 0);
            $product->addAttributeUpdate('breit', $productsDetail['breit'], 0);
            $product->addAttributeUpdate('hoehe', $productsDetail['hoehe'], 0);
            $product->addAttributeUpdate('laeng', $productsDetail['laeng'], 0);
            // 세액구분코드
//            $product->addAttributeUpdate('kondm', $productsDetail['kondm'], 0);
//            $product->addAttributeUpdate('mvgr1', $productsDetail['mvgr1'], 0);
//            $product->addAttributeUpdate('mvgr2', $productsDetail['mvgr2'], 0);
            $product->addAttributeUpdate('prodh', $productsDetail['prodh'], 0);
//            $product->addAttributeUpdate('vmsta', $productsDetail['vmsta'], 0);
//            $product->addAttributeUpdate('matnr2', $productsDetail['matnr2'], 0);
//            $product->addAttributeUpdate('setid', $productsDetail['setid'], 0);
//            $product->addAttributeUpdate('bline', $productsDetail['bline'], 0);
//            $product->addAttributeUpdate('csmtp', $productsDetail['csmtp'], 0);
            $product->addAttributeUpdate('setdi', $productsDetail['setdi'], 0);
            $product->addAttributeUpdate('matshinsun', $productsDetail['matshinsun'], 0);
//            $product->addAttributeUpdate('matvessel', $productsDetail['matvessel'], 0);
            // 용량
//            $product->addAttributeUpdate('prdvl', $productsDetail['prdvl'], 0);
//            $product->addAttributeUpdate('vlunt', $productsDetail['vlunt'], 0);
//            $product->addAttributeUpdate('cpiap', $productsDetail['cpiap'], 0);
//            $product->addAttributeUpdate('prdtp', $productsDetail['prdtp'], 0);
//            $product->addAttributeUpdate('rpfut', $productsDetail['rpfut'], 0);
            $product->addAttributeUpdate('maktxEn', $productsDetail['maktxEn'], 0);
            $product->addAttributeUpdate('maktxZh', $productsDetail['maktxZh'], 0);
            $product->addAttributeUpdate('bctxtEn', $productsDetail['bctxtEn'], 0);
            $product->addAttributeUpdate('bctxtZh', $productsDetail['bctxtZh'], 0);
            $product->addAttributeUpdate('refill', $productsDetail['refill'], 0);

            try {
                $this->productRepository->save($product);

                $result[$productsDetail['matnr']] = ['code' => "0000", 'message' => 'SUCCESS'];
            } catch (\Exception $e) {
                $result[$productsDetail['matnr']] = ['code' => "0001", 'message' => $e->getMessage()];
            }
        }

        return $result;
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
                $storeCode = 'default';
        }
        return $storeCode;
    }

    public function productDetailUpdateTest(\Amore\Sap\Api\Data\SapProductsDetailTest $productsDetail)
    {
        $result = [];

        /**
         * @var $product \Magento\Catalog\Model\Product
         */
        $product = $this->getProductBySku($productsDetail['matnr'], null);

        if (gettype($product) == 'string') {
            $result[$productsDetail['matnr']] = ['code' => "0001", 'message' => $product];
        } else {
            $product->setWeight((float)$productsDetail['brgew']);
//            $product->addAttributeUpdate('bismt', $productsDetail['bismt'], 0);
            // 중량단위
//            $product->addAttributeUpdate('gewei', $productsDetail['gewei'], 0);
//            $product->addAttributeUpdate('brand', $productsDetail['brand'], 0);
            $product->addAttributeUpdate('bctxtKo', $productsDetail['bctxtKo'], 0);
//            $product->addAttributeUpdate('meins', $productsDetail['meins'], 0);
            $product->addAttributeUpdate('mstav', $productsDetail['mstav'], 0);
//            $product->addAttributeUpdate('spart', $productsDetail['spart'], 0);
            $product->addAttributeUpdate('maxlz', $productsDetail['maxlz'], 0);
            $product->addAttributeUpdate('breit', $productsDetail['breit'], 0);
            $product->addAttributeUpdate('hoehe', $productsDetail['hoehe'], 0);
            $product->addAttributeUpdate('laeng', $productsDetail['laeng'], 0);
            // 세액구분코드
//            $product->addAttributeUpdate('kondm', $productsDetail['kondm'], 0);
//            $product->addAttributeUpdate('mvgr1', $productsDetail['mvgr1'], 0);
//            $product->addAttributeUpdate('mvgr2', $productsDetail['mvgr2'], 0);
            $product->addAttributeUpdate('prodh', $productsDetail['prodh'], 0);
//            $product->addAttributeUpdate('vmsta', $productsDetail['vmsta'], 0);
//            $product->addAttributeUpdate('matnr2', $productsDetail['matnr2'], 0);
//            $product->addAttributeUpdate('setid', $productsDetail['setid'], 0);
//            $product->addAttributeUpdate('bline', $productsDetail['bline'], 0);
//            $product->addAttributeUpdate('csmtp', $productsDetail['csmtp'], 0);
            $product->addAttributeUpdate('setdi', $productsDetail['setdi'], 0);
            $product->addAttributeUpdate('matshinsun', $productsDetail['matshinsun'], 0);
//            $product->addAttributeUpdate('matvessel', $productsDetail['matvessel'], 0);
            // 용량
//            $product->addAttributeUpdate('prdvl', $productsDetail['prdvl'], 0);
//            $product->addAttributeUpdate('vlunt', $productsDetail['vlunt'], 0);
//            $product->addAttributeUpdate('cpiap', $productsDetail['cpiap'], 0);
//            $product->addAttributeUpdate('prdtp', $productsDetail['prdtp'], 0);
//            $product->addAttributeUpdate('rpfut', $productsDetail['rpfut'], 0);
            $product->addAttributeUpdate('maktxEn', $productsDetail['maktxEn'], 0);
            $product->addAttributeUpdate('maktxZh', $productsDetail['maktxZh'], 0);
            $product->addAttributeUpdate('bctxtEn', $productsDetail['bctxtEn'], 0);
            $product->addAttributeUpdate('bctxtZh', $productsDetail['bctxtZh'], 0);
            $product->addAttributeUpdate('refill', $productsDetail['refill'], 0);

            try {
                $this->productRepository->save($product);

                $result[$productsDetail['matnr']] = ['code' => "0000", 'message' => 'SUCCESS'];
            } catch (\Exception $e) {
                $result[$productsDetail['matnr']] = ['code' => "0001", 'message' => $e->getMessage()];
            }
        }

        return $result;
    }
}
