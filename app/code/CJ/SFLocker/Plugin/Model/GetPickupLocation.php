<?php
declare(strict_types=1);

namespace CJ\SFLocker\Plugin\Model;

use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\InventoryApi\Api\SourceRepositoryInterface;
use Magento\InventoryInStorePickupApi\Api\Data\PickupLocationInterface;
use Magento\InventoryInStorePickup\Model\GetPickupLocation as GetPickupLocationCore;
use Magento\InventoryInStorePickupApi\Model\Mapper;
use Magento\Store\Model\StoreManagerInterface;

/**
 * @inheritdoc
 */
class GetPickupLocation
{
    const MY_SWS_STORE_CODE = 'my_sulwhasoo';
    /**
     * @var StoreManagerInterface
     */
    private $storeManager;
    /**
     * @var Mapper
     */
    private $mapper;

    /**
     * @var SourceRepositoryInterface
     */
    private $sourceRepository;

    /**
     * @var SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;


    /**
     * @param Mapper $mapper
     * @param SourceRepositoryInterface $sourceRepository
     */
    public function __construct(
        Mapper                          $mapper,
        SourceRepositoryInterface       $sourceRepository,
        SearchCriteriaBuilder           $searchCriteriaBuilder,
        StoreManagerInterface           $storeManager
    )
    {
        $this->mapper = $mapper;
        $this->sourceRepository = $sourceRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->storeManager = $storeManager;
    }

    /**
     * @inheritdoc
     */
    public function aroundExecute(
        GetPickupLocationCore $subject,
        callable $proceed,
        string $pickupLocationCode,
        string $salesChannelType,
        string $salesChannelCode
    ): PickupLocationInterface {
        if ($this->storeManager->getStore()->getCode() != self::MY_SWS_STORE_CODE) {
            return $proceed($pickupLocationCode, $salesChannelType, $salesChannelCode);
        }
        $searchCriteria = $this->searchCriteriaBuilder->addFilter(
            'source_code',
            $pickupLocationCode
        )->setPageSize(1)->create();
        $searchResult = $this->sourceRepository->getList($searchCriteria);

        $sources = $searchResult->getItems();

        foreach ($sources as $source) {
            $result = $this->mapper->map($source);
            break;
        }

        return $result;
    }
}
