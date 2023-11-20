<?php
namespace Sapt\SearchTerms\Cron;

use Magento\Store\Model\StoreManagerInterface;
use Magento\Search\Model\ResourceModel\Query\CollectionFactory as SearchTermsCollcectionFactory;
use Magento\Framework\App\ResourceConnection;
use Psr\Log\LoggerInterface;
use Sapt\SearchTerms\Model\ResourceModel\SearchQueryRank;

class SearchTermsRank
{
    const TERMS_RANK_PAGE = 10;

    /**
     * @var LoggerInterface
     */
    private $logger;


    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var SearchTermsCollcectionFactory
     */
    private $searchTermsCollcectionFactory;

    /**
     * @var \Magento\Framework\DB\Adapter\AdapterInterface
     */
    protected $connection;

    /**
     * @var SearchQueryRank
     */
    protected $searchQueryRank;


    public function __construct(
        LoggerInterface $logger,
        StoreManagerInterface $storeManager,
        SearchTermsCollcectionFactory $searchTermsCollcectionFactory,
        ResourceConnection $resource,
        SearchQueryRank $searchQueryRank

    ) {
        $this->logger = $logger;
        $this->storeManager = $storeManager;
        $this->searchTermsCollcectionFactory = $searchTermsCollcectionFactory;
        $this->connection = $resource->getConnection();
        $this->resource = $resource;
        $this->searchQueryRank = $searchQueryRank;
    }

    /**
     * @return void
     */
    public function execute()
    {
        $stores = $this->storeManager->getStores();
        foreach ($stores as $_store) {
            try {
                $searchTermData = $this->searchTermsCollcectionFactory->create()
                    ->addFieldToSelect([
                        'query_text',
                        'store_id'
                    ])
                    ->addFieldToFilter('store_id', $_store->getId())
                    ->setOrder('popularity','DESC')
                    ->setOrder('query_text','DESC')
                    ->setPageSize(self::TERMS_RANK_PAGE)
                    ->setCurPage(1)
                    ->getData();
            } catch (\Throwable $e) {
                $this->logger->log($e);
                return $this;
            }

            if (empty($searchTermData)) {
                continue;
            }

            try {
                $datas = [];
                foreach ($searchTermData as $key=>$_searchTerm) {
                    $datas[] = [
                        'query_text' => $_searchTerm['query_text'],
                        'store_id' => $_searchTerm['store_id'],
                        'rank' => ++$key
                    ];
                }

                $this->connection->insertMultiple(
                    $this->resource->getTableName($this->searchQueryRank->getMainTable()),
                    $datas
                );
            } catch (\Throwable $e) {
                $this->logger->log($e);
                return $this;
            }
        }

        return $this;
    }
}
