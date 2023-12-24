<?php
namespace Sapt\SearchTerms\Controller\Index;

use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Sapt\SearchTerms\Model\ResourceModel\SearchQueryRank\CollectionFactory as SearchQueryRankCollectionFactory;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\Controller\Result\JsonFactory;

class Search extends \Magento\Framework\App\Action\Action implements HttpGetActionInterface
{
    /**
     * @var ScopeConfigInterface
     */
    private $searchQueryRankCollectionFactory;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var JsonFactory
     */
    private $resultJsonFactory;

    public function __construct(
        Context $context,
        SearchQueryRankCollectionFactory $searchQueryRankCollectionFactory,
        StoreManagerInterface $storeManager,
        JsonFactory $resultJsonFactory

    ) {
        parent::__construct($context);
        $this->searchQueryRankCollectionFactory = $searchQueryRankCollectionFactory;
        $this->storeManager = $storeManager;
        $this->resultJsonFactory = $resultJsonFactory;
    }

    public function execute()
    {
        $store_id = (int)$this->storeManager->getStore()->getId();

        $searchQueryRankCollection = $this->searchQueryRankCollectionFactory->create();
        $connection = $searchQueryRankCollection->getConnection();
        $select = $searchQueryRankCollection->getSelect();
        try {
            $select->joinLeft(
                ['sub_table' => 'sapt_search_query_rank'],
                'main_table.query_text = sub_table.query_text
                AND sub_table.created_at < CURDATE()
                AND sub_table.created_at >= DATE_ADD(CURDATE(), INTERVAL -1 DAY)
                AND sub_table.store_id = '.$store_id
            )->where(
                "main_table.created_at >= CURDATE()"
            )->where(
                "main_table.store_id = ?", $store_id
            )->reset(\Zend_Db_Select::COLUMNS)
            ->columns(['main_table.query_text'])
            ->columns(['main_table.query_text'])
            ->columns(['main_table.rank'])
            ->columns(['sub_table.rank as brank'])
            ->order('main_table.rank ASC');
        } catch (\Throwable $e) {
            return $this->resultJsonFactory->create()->setData([]);
        }

        $searchTerms = $connection->fetchAll($select);
        $searchTerms = empty($searchTerms) ? [] : $searchTerms;

        try {
            $searchTerms = array_map(function ($data) {
                $data['brank'] = (int)$data['brank'];

                if ($data['brank'] === 0) {
                    $data['up_and_down'] = 'up';
                } else if ($data['brank'] == $data['rank']) {
                    $data['up_and_down'] = 'normal';
                } else if ($data['brank'] > $data['rank']) {
                    $data['up_and_down'] = 'up';
                } else {
                    $data['up_and_down'] = 'down';
                }

                return [
                    'query_text' => (strlen($data['query_text']) > 20)?mb_substr($data['query_text'] ,0, 13, 'UTF-8').'...' : $data['query_text'],
                    '_query_text' => $data['query_text'],
                    'rank' => $data['rank'],
                    'up_and_down' => $data['up_and_down']
                ];
            }, $searchTerms);
        } catch (\Throwable $e) {
            var_dump($e);
            return $this->resultJsonFactory->create()->setData([]);
        }

        return $this->resultJsonFactory->create()->setData($searchTerms);
    }
}
