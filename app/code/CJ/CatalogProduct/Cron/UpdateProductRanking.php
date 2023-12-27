<?php

namespace CJ\CatalogProduct\Cron;

use CJ\CatalogProduct\Helper\Data;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Magento\Framework\App\ResourceConnection;
use Magento\Catalog\Model\ResourceModel\Product\Action;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Framework\Locale\ResolverInterface;
use Magento\Sales\Model\ResourceModel\Report\Bestsellers\CollectionFactory as BestSellerCollectionFactory;
use CJ\CatalogProduct\Model\Entity\Attribute\Source;
use CJ\CatalogProduct\Logger\Logger;

class UpdateProductRanking
{
    /**
     * @var ResourceConnection
     */
    protected $resourceConnection;
    /**
     * @var Data
     */
    protected $data;
    /**
     * @var CollectionFactory
     */
    protected $collectionFactory;
    /**
     * @var Action
     */
    protected $action;
    /**
     * @var TimezoneInterface
     */
    protected $localeDate;
    /**
     * @var ResolverInterface
     */
    protected $localeResolver;
    /**
     * @var BestSellerCollectionFactory
     */
    protected $bestSellerCollectionFactory;
    /**
     * @var Logger
     */
    protected $logger;

    /**
     * @param ResourceConnection $resourceConnection
     * @param Data $data
     * @param CollectionFactory $collectionFactory
     * @param Action $action
     * @param TimezoneInterface $timezone
     * @param ResolverInterface $localeResolver
     * @param BestSellerCollectionFactory $bestSellerCollectionFactory
     * @param Logger $logger
     */
    public function __construct(
        ResourceConnection $resourceConnection,
        Data $data,
        CollectionFactory $collectionFactory,
        Action $action,
        TimezoneInterface $timezone,
        ResolverInterface $localeResolver,
        BestSellerCollectionFactory $bestSellerCollectionFactory,
        Logger $logger
    )
    {
        $this->resourceConnection = $resourceConnection;
        $this->data = $data;
        $this->collectionFactory = $collectionFactory;
        $this->action = $action;
        $this->localeDate = $timezone;
        $this->localeResolver = $localeResolver;
        $this->bestSellerCollectionFactory = $bestSellerCollectionFactory;
        $this->logger = $logger;
    }

    /**
     * @return void
     * @throws \Exception
     */
    public function execute()
    {
        $allowStores = $this->data->getAllowStores();

        foreach ($allowStores as $storeId) {
            try {
                $this->localeResolver->emulate($storeId);
                $currentDate = $this->localeDate->date();
                $yesterday = $currentDate->sub(new \DateInterval('PT25H'))->format('Y-m-d');
                $beforeYesterDay = strftime("%Y-%m-%d", strtotime($yesterday .  " -1 day"));
                $this->localeResolver->revert();

                $connection = $this->resourceConnection->getConnection();
                $table = $connection->getTableName('sales_bestsellers_aggregated_daily');
                $query = "SELECT product_id,sum(qty_ordered) as qty_ordered FROM {$table} as sbar WHERE sbar.store_id = {$storeId} AND sbar.period >= (NOW() - INTERVAL 90 DAY) GROUP BY sbar.product_id ORDER BY qty_ordered DESC";
                $productIds = $connection->fetchCol($query, 'product_id');

                if (count($productIds)) {
                    $collection = $this->createProductCollection($storeId);
                    foreach ($collection as $item){
                        if (in_array($item->getId(), $productIds)){
                            $ranking = array_search($item->getId(), $productIds);
                            $ranking++;
                        }else{
                            $ranking = 9999999;
                        }
                        /**
                         * get rank status for product (compare the qty ordered of yesterday and before yesterday)
                         */
                        $rankingStatus = $this->getRankingStatus($yesterday, $beforeYesterDay, $item->getId(), $storeId);

                        $this->action->updateAttributes([$item->getId()], ['ranking' => $ranking, 'ranking_status' => $rankingStatus], $storeId);
                    }
                }
            }catch (\Exception $exception){
                $this->logger->info(__('Cron Update Ranking: %1',$exception->getMessage()));
            }
        }
    }

    /**
     * @param $storeId
     * @return \Magento\Catalog\Model\ResourceModel\Product\Collection
     */
    protected function createProductCollection($storeId)
    {
        return $this->collectionFactory->create()->addStoreFilter($storeId);
    }

    /**
     * @param $yesterday
     * @param $beforeYesterDay
     * @param $productId
     * @param $storeId
     * @return int
     */
    public function getRankingStatus($yesterday, $beforeYesterDay, $productId, $storeId)
    {
        $qtyYesterday = $this->getQtyOrdered($yesterday, $storeId, $productId);
        $qtyBeforeYesterDay = $this->getQtyOrdered($beforeYesterDay, $storeId, $productId);

        if ($qtyYesterday && $qtyBeforeYesterDay){
            if ($qtyYesterday > $qtyBeforeYesterDay){
                return Source::VALUE_UP;
            }elseif ($qtyYesterday < $qtyBeforeYesterDay){
                return Source::VALUE_DOWN;
            }
        }elseif (!$qtyYesterday && $qtyBeforeYesterDay){
            return Source::VALUE_DOWN;
        }elseif ($qtyYesterday && !$qtyBeforeYesterDay){
            return Source::VALUE_UP;
        }

        return Source::VALUE_STABLE;
    }

    /**
     * @param $day
     * @param $storeId
     * @param $productId
     * @return array|mixed|null
     */
    public function getQtyOrdered($day, $storeId, $productId)
    {
        return $this->bestSellerCollectionFactory->create()
                    ->setPeriod('daily')
                    ->addFieldToFilter('period', ['eq' => $day])
                    ->addStoreFilter($storeId)->addFieldToFilter('product_id', ['eq' => $productId])
                    ->getFirstItem()->getData('qty_ordered');
    }

}
