<?php
    namespace Hoolah\Hoolah\Model;
     
    use Magento\Framework\Api\SearchCriteriaInterface;
    use Magento\Framework\Api\SortOrder;
    
    class HoolahLogRepository
    {
        private $hoolahLogFactory;
        private $hoolahLogCollectionFactory;
     
        public function __construct(
            \Hoolah\Hoolah\Model\HoolahLogFactory $hoolahLogFactory,
            \Hoolah\Hoolah\Model\ResourceModel\HoolahLog\CollectionFactory $hoolahLogCollectionFactory
        ) {
            $this->hoolahLogFactory = $hoolahLogFactory;
            $this->hoolahLogCollectionFactory = $hoolahLogCollectionFactory;
        }
     
        public function getList(SearchCriteriaInterface $searchCriteria)
        {
            $collection = $this->hoolahLogCollectionFactory->create();
     
            $this->addFiltersToCollection($searchCriteria, $collection);
            $this->addSortOrdersToCollection($searchCriteria, $collection);
            
            $collection->load();
     
            return $collection;
        }
     
        private function addFiltersToCollection(SearchCriteriaInterface $searchCriteria, $collection)
        {
            foreach ($searchCriteria->getFilterGroups() as $filterGroup) {
                $fields = $conditions = [];
                foreach ($filterGroup->getFilters() as $filter) {
                    $fields[] = $filter->getField();
                    $conditions[] = [$filter->getConditionType() => $filter->getValue()];
                }
                $collection->addFieldToFilter($fields, $conditions);
            }
        }
     
        private function addSortOrdersToCollection(SearchCriteriaInterface $searchCriteria, $collection)
        {
            foreach ((array) $searchCriteria->getSortOrders() as $sortOrder) {
                $direction = $sortOrder->getDirection() == SortOrder::SORT_ASC ? 'asc' : 'desc';
                $collection->addOrder($sortOrder->getField(), $direction);
            }
        }
    }