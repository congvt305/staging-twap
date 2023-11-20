<?php


namespace Sapt\Catalog\Block;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Block\Product\Context;
use Magento\Framework\Api\FilterBuilder;
use Magento\Framework\Api\Search\FilterGroupBuilder;
use Magento\Framework\Api\SearchCriteriaInterface;

class Freebies extends \Magento\Catalog\Block\Product\AbstractProduct
{
    /**
     * @var SearchCriteriaInterface
     */
    protected $searchCriteria;
    /**
     * @var FilterGroupBuilder
     */
    protected $filterGroupBuilder;
    /**
     * @var FilterBuilder
     */
    protected $filterBuilder;
    /**
     * @var ProductRepositoryInterface
     */
    protected $productRepository;

    public function __construct(
        Context $context,
        ProductRepositoryInterface $productRepository,
        SearchCriteriaInterface $criteria,
        FilterGroupBuilder $filterGroupBuilder,
        FilterBuilder $filterBuilder,
        array $data = []
    ) {
        $this->searchCriteria = $criteria;
        $this->filterGroupBuilder = $filterGroupBuilder;
        $this->filterBuilder = $filterBuilder;
        $this->productRepository = $productRepository;
        parent::__construct($context, $data);
    }

    /**
     * @param $skus
     * @return ProductInterface[]
     */
    public function getFreebies($skus)
    {
        $filterGroup = $this->filterGroupBuilder->setFilters([
            $this->filterBuilder->setField('sku')
                ->setConditionType('in')
                ->setValue(explode(',', $skus))
                ->create()
        ])->create();
        $this->searchCriteria->setFilterGroups([$filterGroup]);
        $collection = $this->productRepository->getList($this->searchCriteria);
        return $collection->getItems();
    }
}
