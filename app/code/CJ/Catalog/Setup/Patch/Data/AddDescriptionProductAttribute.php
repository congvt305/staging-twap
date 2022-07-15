<?php

namespace CJ\Catalog\Setup\Patch\Data;

use Magento\Eav\Setup\EavSetup;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface;
use Magento\Catalog\Model\Product as ProductModel;

/**
 * Class AddDescriptionProductAttribute
 */
class AddDescriptionProductAttribute implements DataPatchInterface
{

    const EAV_EXT_DESCRIPTION = 'product_extra_description';

    /**
     * @var ModuleDataSetupInterface
     */
    protected ModuleDataSetupInterface $moduleDataSetup;

    /**
     * @var EavSetupFactory
     */
    protected EavSetupFactory $eavSetupFactory;

    /**
     * @var \Magento\Catalog\Model\Config
     */
    protected $config;

    /**
     * @var \Magento\Eav\Api\AttributeManagementInterface
     */
    protected $attributeManagement;

    /**
     * @var string
     */
    protected $attributeGroup = 'Product Details';

    /**
     * @param ModuleDataSetupInterface $moduleDataSetup
     * @param EavSetupFactory $eavSetupFactory
     */
    public function __construct(
        \Magento\Framework\Setup\ModuleDataSetupInterface $moduleDataSetup,
        \Magento\Eav\Setup\EavSetupFactory $eavSetupFactory,
        \Magento\Catalog\Model\Config $config,
        \Magento\Eav\Api\AttributeManagementInterface $attributeManagement
    ) {
        $this->config = $config;
        $this->attributeManagement = $attributeManagement;
        $this->moduleDataSetup = $moduleDataSetup;
        $this->eavSetupFactory = $eavSetupFactory;
    }

    /**
     * @return AddVisualAidsAttribute|void
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Zend_Validate_Exception
     */
    public function apply()
    {
        /** @var EavSetup $eavSetup */
        $eavSetup = $this->eavSetupFactory->create(['setup' => $this->moduleDataSetup]);

        $eavSetup->addAttribute(ProductModel::ENTITY,self::EAV_EXT_DESCRIPTION, [
            'type' => 'text',
            'label' => 'Extra Product Description',
            'input' => 'textarea',
            'source' => \Magento\Catalog\Model\Product\Attribute\Source\Layout::class,
            'global' => ScopedAttributeInterface::SCOPE_STORE,
            'user_defined' => true,
            'visible' => true,
            'required' => false,
            'default' => false,
            'is_used_in_grid' => true,
            'is_visible_in_grid' => false,
            'is_filterable_in_grid' => false,
            'is_visible_on_front' => true,
            'used_in_product_listing' => true
        ]);

        // Assign attribute to all attribute set
        $entityTypeId = $eavSetup->getEntityTypeId(\Magento\Catalog\Model\Product::ENTITY);
        $attributeSetIds = $eavSetup->getAllAttributeSetIds($entityTypeId);

        foreach ($attributeSetIds as $attributeSetId) {
            if ($attributeSetId) {
                $group_id = $this->config->getAttributeGroupId($attributeSetId, $this->attributeGroup);
                $this->attributeManagement->assign(
                    'catalog_product',
                    $attributeSetId,
                    $group_id,
                    self::EAV_EXT_DESCRIPTION,
                    999
                );
            }
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
}
