<?php
/**
 * @author Eguana Team
 * @copyriht Copyright (c) 2020 Eguana {http://eguanacommerce.com}
 * Created by PhpStorm
 * User: arslan
 * Date: 16/10/20
 * Time: 4:38 PM
 */
declare(strict_types=1);

namespace Eguana\Redemption\Model\Source;

use Eguana\StoreLocator\Api\StoreInfoRepositoryInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Option\ArrayInterface;
use Eguana\StoreLocator\Model\ResourceModel\StoreInfo\Collection;
use Eguana\StoreLocator\Model\ResourceModel\StoreInfo\CollectionFactory;
use Magento\Framework\App\RequestInterface;
use Eguana\Redemption\Api\RedemptionRepositoryInterface;

/**
 * This class is used to get the available stores for redemption from store locator
 *
 * Class AvailableStores
 */
class AvailableStores implements ArrayInterface
{
    /**
     * @var StoreInfoRepositoryInterface
     */
    private $storeInfoRepositoryInterface;

    /**
     * @var SearchCriteriaBuilder $searchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    /**
     * @var CollectionFactory
     */
    private $storeInfoCollectionFactory;

    /**
     * @var RequestInterface
     */
    private $request;

    /**
     * @var RedemptionRepositoryInterface
     */
    private $redemptionRepository;

    /**
     * AvailableStores constructor.
     *
     * @param StoreInfoRepositoryInterface $storeInfoRepositoryInterface
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param CollectionFactory $storeInfoCollectionFactory
     * @param RequestInterface $request
     * @param RedemptionRepositoryInterface $redemptionRepository
     */
    public function __construct(
        StoreInfoRepositoryInterface $storeInfoRepositoryInterface,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        CollectionFactory $storeInfoCollectionFactory,
        RequestInterface $request,
        RedemptionRepositoryInterface $redemptionRepository
    ) {
        $this->storeInfoRepositoryInterface = $storeInfoRepositoryInterface;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->storeInfoCollectionFactory = $storeInfoCollectionFactory;
        $this->request = $request;
        $this->redemptionRepository = $redemptionRepository;
    }

    /**
     * Retrieve Available store options array.
     *
     * @return array
     */
    public function toOptionArray() : array
    {
        $redemptionId = $this->request->getParam('redemption_id');
        if ($redemptionId) {
            $redemptionData = $this->redemptionRepository->getById($redemptionId);
            $storeId = $redemptionData->getData('store_id');
            $storeCollection = $this->storeInfoCollectionFactory->create();
            $storeId = [$storeId];
            $storeCollection->addFieldToFilter(
                "available_for_redemption",
                ["eq" => 1]
            );
            if (count($storeId) == 1) {
                $storeId = $storeId[0];
            }
            if ($storeId != 0) {
                $storeCollection->addStoreFilter($storeId);
            }

            $availableStore = $storeCollection->getData();
            foreach ($availableStore as $storeName) {
                $result[] = ['value' => $storeName['entity_id'], 'label' => $storeName['title']];
            }
            return $result;
        }
        $redemptionData = $this->redemptionRepository->getById($redemptionId);
        $storeId = $redemptionData->getData('store_id');
        $storeCollection = $this->storeInfoCollectionFactory->create();
        $storeCollection->addFieldToFilter(
            "available_for_redemption",
            ["eq" => 1]
        );
        $result = [
            ['value' => '', 'label' => 'Select Store']
        ];
        foreach ($storeCollection as $storeName) {
            $result[] = ['value' => $storeName->getEntityId(), 'label' => $storeName->getTitle()];
        }
        return $result;
    }

    /**
     * Get store locators details
     *
     * @param $storeId
     * @param array $counterIds
     * @return Collection
     */
    public function getCountersByStoreId($storeId, $counterIds = []) : Collection
    {
        $storeId = [$storeId];
        $result = '';
        $storeCollection = $this->storeInfoCollectionFactory->create();
        $storeCollection->addFieldToFilter(
            "available_for_redemption",
            ["eq" => 1]
        );
        if ($counterIds) {
            if (!is_array($counterIds)) {
                $counterIds = [$counterIds];
            }
            $storeCollection->addFieldToFilter(
                "entity_id",
                ["in" => $counterIds]
            );
        }
        if (count($storeId) == 1) {
            $storeId = $storeId[0];
        }
        if ($storeId != 0) {
            $storeCollection->addStoreFilter($storeId);
        }
        return $storeCollection;
    }

    /**
     * This method is used to get the store list from store locator
     * with respect to the selected store
     *
     * @param $storeId
     * @return string
     */
    public function getStoreListByStoreId($storeId) : string
    {
        $result = '';
        $storeCollection = $this->getCountersByStoreId($storeId);
        foreach ($storeCollection as $storeName) {
            $result .= '<option data-title="' . $storeName->getTitle() . '" value="' . $storeName->getId() . '">'
                . $storeName->getTitle() . '</option>';
        }
        return $result;
    }
}
