<?php
declare(strict_types=1);

namespace CJ\SFLocker\Model;

use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\InventoryApi\Api\SourceRepositoryInterface;
use Magento\InventoryInStorePickupApi\Api\Data\SearchRequestInterface;
use Magento\InventoryInStorePickupApi\Api\Data\SearchResultInterface;
use Magento\InventoryInStorePickupApi\Api\Data\SearchResultInterfaceFactory;
use Magento\InventoryInStorePickupApi\Api\GetPickupLocationsInterface;
use Magento\InventoryInStorePickupApi\Model\Mapper;

/**
 * @inheritdoc
 */
class GetPickupLocations implements GetPickupLocationsInterface
{
    /**
     * @var Mapper
     */
    private $mapper;

    /**
     * @var SourceRepositoryInterface
     */
    private $sourceRepository;

    /**
     * @var SearchResultInterfaceFactory
     */
    private $searchResultFactory;

    /**
     * @var SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;


    /**
     * @param Mapper $mapper
     * @param SourceRepositoryInterface $sourceRepository
     * @param SearchResultInterfaceFactory $searchResultFactory
     */
    public function __construct(
        Mapper                          $mapper,
        SourceRepositoryInterface       $sourceRepository,
        SearchResultInterfaceFactory    $searchResultFactory,
        SearchCriteriaBuilder           $searchCriteriaBuilder
    )
    {
        $this->mapper = $mapper;
        $this->sourceRepository = $sourceRepository;
        $this->searchResultFactory = $searchResultFactory;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
    }

    /**
     * @inheritdoc
     */
    public function execute(SearchRequestInterface $searchRequest): SearchResultInterface
    {
        $selectedSourceCode = $searchRequest->getFilters()->getPickupLocationCode()->getValue();
        $searchCriteria = $this->searchCriteriaBuilder->addFilter(
            'source_code',
            $selectedSourceCode
        )->setPageSize(1)->create();
        $searchResult = $this->sourceRepository->getList($searchCriteria);

        $sources = $searchResult->getItems();

        $pickupLocations = [];

        foreach ($sources as $source) {
            $source->setFax($source->getExtensionAttributes()->getStoreType());
            $pickupLocation = $this->mapper->map($source);
            $pickupLocations[] = $pickupLocation;
        }

        return $this->searchResultFactory->create(
            [
                'items' => $pickupLocations,
                 'totalCount' => $searchResult->getTotalCount(),
                'searchRequest' => $searchRequest
            ]
        );
    }
}
