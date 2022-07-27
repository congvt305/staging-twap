<?php
declare(strict_types=1);

namespace CJ\SFLocker\Plugin\Checkout\Model;

use Magento\Checkout\Model\DefaultConfigProvider as DefaultConfigProviderAlias;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\InventoryApi\Api\SourceRepositoryInterface;
use Magento\InventoryInStorePickupApi\Model\Mapper;
use CJ\SFLocker\Model\Config\Source\StoreType;
use Magento\Store\Model\StoreManagerInterface;

/**
 * This class is used to get all pickup stores
 * Class DefaultConfigProvider
 */
class DefaultConfigProvider
{
    const IS_PICKUP_LOCATION_ACTIVE_FIELD = 'is_pickup_location_active';
    const STORE_TYPE_FIELD = 'store_type';
    const ACTIVE = '1';
    const MY_SWS_STORE_CODE = 'my_sulwhasoo';
    /**
     * @var StoreManagerInterface
     */
    private $storeManager;
    /**
     * @var SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    /**
     * @var SourceRepositoryInterface
     */
    private $sourceRepository;


    /**
     * @param SourceRepositoryInterface $sourceRepository
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param StoreManagerInterface     $storeManager
     */
    public function __construct(
        SourceRepositoryInterface $sourceRepository,
        SearchCriteriaBuilder     $searchCriteriaBuilder,
        StoreManagerInterface     $storeManager
    )
    {
        $this->sourceRepository = $sourceRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->storeManager = $storeManager;
    }

    /**
     * @param DefaultConfigProviderAlias $subject
     * @param array $result
     * @return array
     */
    public function afterGetConfig(DefaultConfigProviderAlias $subject, array $result): array
    {
        if ($this->storeManager->getStore()->getCode() == self::MY_SWS_STORE_CODE) {
            $apStores = $this->getStores([StoreType::AP_STORE]);
            $apAreas = $apStores['areas'];
            $apPickupLocations = $apStores['pickup_location'];

            $result['ap_areas'] = $apAreas;
            $apCities = array_keys($apAreas);
            $result['ap_cities'] = [];
            $result['ap_cities'][] = [
                'value' => '',
                'label' => '地區'
            ];
            if (count($apCities) > 0) {
                foreach ($apCities as $city) {
                    $result['ap_cities'][] = [
                        'value' => $city,
                        'label' => $city
                    ];
                }
            }
            $result['ap_stores'] = $apPickupLocations;


            $sfLockers = $this->getStores([StoreType::SF_LOCKER, StoreType::SF_STORE]);
            $sfAreas = $sfLockers['areas'];
            $sfPickupLocations = $sfLockers['pickup_location'];
            $result['sf_areas'] = $sfAreas;
            $result['sf_cities'] = [];
            $result['sf_cities'][] = [
                'value' => '',
                'label' => '地區'
            ];
            $sfCities = array_keys($sfAreas);
            if (count($sfCities) > 0) {
                foreach ($sfCities as $city) {
                    $result['sf_cities'][] = [
                        'value' => $city,
                        'label' => $city
                    ];
                }
            }
            $result['sf_lockers'] = $sfPickupLocations;
        }
        return $result;
    }


    /**
     * @param $type
     * @return array
     */
    private function getStores($type): array
    {
        $searchCriteria = $this->searchCriteriaBuilder->addFilter(
            self::IS_PICKUP_LOCATION_ACTIVE_FIELD,
            self::ACTIVE
        )->addFilter(
            self::STORE_TYPE_FIELD,
            $type,
            'in'
        )->addFilter('source_code', 'default', 'neq')->create();
        $sources = $this->sourceRepository->getList($searchCriteria)->getItems();
        $pickupLocations = [];
        $areas = [];
        foreach ($sources as $source) {
            $region = trim($source->getRegion());
            $area = trim($source->getPostcode());
            if (!isset($areas[$region])) {
                $areas[$region] = [];
            }
            $areaArr = ['label' => $area, 'value' => $area];
            if (!in_array($areaArr, $areas[$region])) {
                $areas[$region][] = $areaArr;
            }
            if (!isset($pickupLocations[$area])) {
                $pickupLocations[$area] = [];
            }
            $pickupLocations[$area][] = [
                'value' => $source->getSourceCode(),
                'label' => $source->getName(),
                'type' => $source->getExtensionAttributes()->getStoreType(),
                'area' => $area,
                'region' => $region
            ];
        }
        return ['pickup_location' => $pickupLocations, 'areas' => $areas];
    }
}
