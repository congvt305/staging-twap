<?php
/**
 * @author Eguana Team
 * @copyriht Copyright (c) 2020 Eguana {http://eguanacommerce.com}
 * Created by PhpStorm
 * User: raheel
 * Date: 28/10/20
 * Time: 8:31 PM
 */
declare(strict_types=1);

namespace Eguana\EventReservation\Model\UserReservation\Source;

use Eguana\StoreLocator\Api\StoreInfoRepositoryInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Option\ArrayInterface;

/**
 * This class is used to get the available stores for event from store locator module
 *
 * Class AvailableStores
 */
class AvailableStores implements ArrayInterface
{
    /**
     * @var SearchCriteriaBuilder
     */
    private $criteriaBuilder;

    /**
     * @var StoreInfoRepositoryInterface
     */
    private $storeInfoRepositoryInterface;

    /**
     * @param SearchCriteriaBuilder $criteriaBuilder
     * @param StoreInfoRepositoryInterface $storeInfoRepositoryInterface
     */
    public function __construct(
        SearchCriteriaBuilder $criteriaBuilder,
        StoreInfoRepositoryInterface $storeInfoRepositoryInterface
    ) {
        $this->criteriaBuilder = $criteriaBuilder;
        $this->storeInfoRepositoryInterface = $storeInfoRepositoryInterface;
    }

    /**
     * Retrieve Available store options array.
     *
     * @return array
     */
    public function toOptionArray(): array
    {
        $this->criteriaBuilder->addFilter('available_for_events', ['eq' => 1]);
        $item = $this->storeInfoRepositoryInterface
            ->getList($this->criteriaBuilder->create())
            ->getItems();

        $result = [
            ['value' => '', 'label' => 'Select Store']
        ];

        $i = 1;
        foreach ($item as $storeName) {
            $result[$i] = ['value' => $storeName->getEntityId(), 'label' => $storeName->getTitle()];
            $i++;
        }
        return $result;
    }
}
