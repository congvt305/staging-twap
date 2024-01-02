<?php

namespace CJ\Catalog\Setup\Patch\Data;

use Magento\Catalog\Model\Product as ProductModel;
use Magento\Eav\Setup\EavSetup;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Eav\Api\Data\AttributeSetInterface;
use Magento\Catalog\Api\AttributeSetRepositoryInterface;

class AddProductDateLaunchAttribute implements DataPatchInterface
{

    const PROMOTION_TEXT = 'promotion_text';

    const ATTRIBUTES_SET = ['TW Sulwhasoo', 'TW Laneige'];

    /**
     * @var ModuleDataSetupInterface
     */
    private $moduleDataSetup;
    /**
     * @var EavSetupFactory
     */
    private $eavSetupFactory;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory
     */
    private $collectionFactory;

    /**
     * @var \Magento\Catalog\Model\Config
     */
    private $config;

    /**
     * @var \Magento\Eav\Api\AttributeManagementInterface
     */
    private $attributeManagement;

    /**
     * @param ModuleDataSetupInterface $moduleDataSetup
     * @param EavSetupFactory $eavSetupFactory
     * @param \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $collectionFactory
     * @param \Magento\Framework\App\State $state
     * @param \Magento\Catalog\Model\Config $config
     * @param \Magento\Eav\Api\AttributeManagementInterface $attributeManagement
     * @param AttributeSetRepositoryInterface $attributeSetRepository
     */
    public function __construct(
        ModuleDataSetupInterface $moduleDataSetup,
        EavSetupFactory $eavSetupFactory,
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $collectionFactory,
        \Magento\Framework\App\State $state,
        \Magento\Catalog\Model\Config $config,
        \Magento\Eav\Api\AttributeManagementInterface $attributeManagement,
        AttributeSetRepositoryInterface $attributeSetRepository
    ) {
        $this->moduleDataSetup = $moduleDataSetup;
        $this->eavSetupFactory = $eavSetupFactory;
        $this->collectionFactory = $collectionFactory;
        $this->state = $state;
        $this->config = $config;
        $this->attributeManagement = $attributeManagement;
        $this->attributeSetRepository = $attributeSetRepository;
    }

    public function apply()
    {
        $this->state->setAreaCode(\Magento\Framework\App\Area::AREA_FRONTEND);
        $this->moduleDataSetup->startSetup();
        /** @var EavSetup $eavSetup */
        $eavSetup = $this->eavSetupFactory->create(['setup' => $this->moduleDataSetup]);

        $eavSetup->addAttribute(ProductModel::ENTITY, self::PROMOTION_TEXT,
            [
                'type' => 'text',
                'label' => 'Promotion Text',
                'input' => 'text',
                'sort_order' => 100,
                'global' => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_STORE,
                'user_defined' => true,
                'visible' => true,
                'required' => false,
                'default' => false,
                'is_used_in_grid' => true,
                'is_visible_in_grid' => false,
                'is_filterable_in_grid' => false,
                'is_visible_on_front' => true,
                'used_in_product_listing' => true
            ]
        );

        $ATTRIBUTE_GROUP = 'General'; // Attribute Group Name
        $entityTypeId = $eavSetup->getEntityTypeId(\Magento\Catalog\Model\Product::ENTITY);
        $attributeSetIds = $eavSetup->getAllAttributeSetIds($entityTypeId);

        foreach ($attributeSetIds as $attributeSetId) {
            if ($attributeSetId) {
                $attributeDetail = $this->attributeSetRepository->get($attributeSetId);
                if (in_array($attributeDetail->getAttributeSetName(), self::ATTRIBUTES_SET)){
                    $group_id = $this->config->getAttributeGroupId($attributeSetId, $ATTRIBUTE_GROUP);
                    $this->attributeManagement->assign(
                        \Magento\Catalog\Model\Product::ENTITY,
                        $attributeSetId,
                        $group_id,
                        self::PROMOTION_TEXT,
                        999
                    );
                }
            }
        }

        $this->moduleDataSetup->endSetup();
    }

    public static function getDependencies()
    {
        return [];
    }

    public function getAliases()
    {
        return [];
    }
}
