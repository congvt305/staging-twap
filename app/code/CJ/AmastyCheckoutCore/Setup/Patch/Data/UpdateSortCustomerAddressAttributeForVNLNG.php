<?php
declare(strict_types=1);

namespace CJ\AmastyCheckoutCore\Setup\Patch\Data;

use Amasty\CheckoutCore\Model\ResourceModel\Field as FieldResource;
use Magento\Framework\App\ResourceConnection;
use Magento\Customer\Model\ResourceModel\Address\Attribute\CollectionFactory;

class UpdateSortCustomerAddressAttributeForVNLNG implements
    \Magento\Framework\Setup\Patch\DataPatchInterface,
    \Magento\Framework\Setup\Patch\PatchVersionInterface
{
    const STORE_CODE_VN_LNG = 'vn_laneige';

    const ADDRESS_ATTRIBUTE = [
        'lastname' => 15,
        'street' => 107,
        'city' => 102,
        'city_id' => 102,
        'ward' => 104,
        'ward_id' => 104,
    ];

    /**
     * @var \Magento\Framework\Setup\ModuleDataSetupInterface
     */
    protected $moduleDataSetup;

    /**
     * @var \Magento\Eav\Setup\EavSetupFactory
     */
    protected $eavSetupFactory;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $storeRepository;

    /**
     * @var ResourceConnection
     */
    private $resourceConnection;

    /**
     * @var CollectionFactory
     */
    private $attributeCollectionFactory;

    /**
     * @param \Magento\Store\Api\StoreRepositoryInterface $storeRepository
     * @param ResourceConnection $resourceConnection
     * @param \Magento\Eav\Model\Config $eavConfig
     */
    public function __construct(
        \Magento\Store\Api\StoreRepositoryInterface $storeRepository,
        ResourceConnection $resourceConnection,
        CollectionFactory $attributeCollectionFactory
    ) {
        $this->storeRepository = $storeRepository;
        $this->resourceConnection = $resourceConnection;
        $this->attributeCollectionFactory = $attributeCollectionFactory;
    }

    /**
     * @return UpdateSortCustomerAddressAttributeForVNLNG|void
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function apply()
    {
        $connection = $this->resourceConnection->getConnection();
        $fieldTable = $this->resourceConnection->getTableName(FieldResource::MAIN_TABLE);
        $store = $this->storeRepository->get(self::STORE_CODE_VN_LNG);
        $storeId = $store->getId();
        $collection = $this->attributeCollectionFactory->create();
        $collection->addFieldToFilter('attribute_code', ['in' => array_keys(self::ADDRESS_ATTRIBUTE)]);
        foreach ($collection->getItems() as $item) {
            $bind = [
                'attribute_id' => $item->getAttributeId(),
                'label' => $item->getFrontendLabel(),
                'sort_order' => self::ADDRESS_ATTRIBUTE[$item->getAttributeCode()],
                'required' => $item->getIsRequired(),
                'width' => 100,
                'enabled' => true,
                'store_id' => $storeId
            ];

            $connection->insert($fieldTable, $bind);
        }
    }
    /**
     * @return array|string[]
     */
    public static function getDependencies(): array
    {
        return [];
    }

    /**
     * @return array|string[]
     */
    public function getAliases(): array
    {
        return [];
    }

    /**
     * {@inheritDoc}
     */
    public static function getVersion()
    {
        return '1.0';
    }
}
