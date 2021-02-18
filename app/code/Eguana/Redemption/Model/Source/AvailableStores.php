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
use Magento\Framework\App\Request\DataPersistorInterface;
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
     * @var DataPersistorInterface
     */
    private $dataPersistor;

    /**
     * AvailableStores constructor.
     *
     * @param StoreInfoRepositoryInterface $storeInfoRepositoryInterface
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param CollectionFactory $storeInfoCollectionFactory
     * @param RequestInterface $request
     * @param RedemptionRepositoryInterface $redemptionRepository
     * @param DataPersistorInterface $dataPersistor
     */
    public function __construct(
        StoreInfoRepositoryInterface $storeInfoRepositoryInterface,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        CollectionFactory $storeInfoCollectionFactory,
        RequestInterface $request,
        RedemptionRepositoryInterface $redemptionRepository,
        DataPersistorInterface $dataPersistor
    ) {
        $this->storeInfoRepositoryInterface = $storeInfoRepositoryInterface;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->storeInfoCollectionFactory = $storeInfoCollectionFactory;
        $this->request = $request;
        $this->redemptionRepository = $redemptionRepository;
        $this->dataPersistor = $dataPersistor;
    }

    /**
     * Retrieve Available store options array.
     *
     * @return array
     */
    public function toOptionArray() : array
    {
        $storeId = 0;
        $redemptionId = $this->request->getParam('redemption_id');
        $formData = $this->dataPersistor->get('eguana_redemption');
        if (isset($formData['store_id_name'])) {
            $storeId = $formData['store_id_name'];
        }
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
        $storeCollection = $this->storeInfoCollectionFactory->create();
        $storeCollection->addFieldToFilter(
            "available_for_redemption",
            ["eq" => 1]
        );
        if ($storeId) {
            $storeCollection->addStoreFilter($storeId);
        }
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
