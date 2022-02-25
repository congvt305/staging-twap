<?php

namespace Eguana\CustomCatalog\Setup\Patch\Data;

use Magento\Catalog\Model\Product\Type;
use Magento\Eav\Setup\EavSetup;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface;
use Magento\Eav\Model\Entity\Attribute\Source\Boolean as SourceBoolean;

class AddProductAttribute implements DataPatchInterface
{
    /**
     * @var ModuleDataSetupInterface
     */
    private $moduleDataSetup;
    /**
     * @var EavSetupFactory
     */
    private $eavSetupFactory;

    public function __construct(
        ModuleDataSetupInterface $moduleDataSetup,
        EavSetupFactory $eavSetupFactory
    ) {
        $this->moduleDataSetup = $moduleDataSetup;
        $this->eavSetupFactory = $eavSetupFactory;
    }

    public function apply()
    {
        /** @var EavSetup $eavSetup */
        $eavSetup = $this->eavSetupFactory->create(['setup' => $this->moduleDataSetup]);

        $eavSetup->addAttribute('catalog_product', 'volume_configurable', [
            'type' => 'int',
            'label' => 'Volumes',
            'input' => 'swatch_text',
            'required' => false,
            'user_defined' => true,
            'searchable' => true,
            'filterable' => false,
            'comparable' => false,
            'visible_in_advanced_search' => true,
            'apply_to' => 'simple,virtual,configurable',
            'is_used_in_grid' => true,
            'is_visible_in_grid' => false,
            'is_filterable_in_grid' => true,
            'option' => [
                'values' => ['30ml','60ml'],
            ],
        ]);

        $eavSetup->addAttribute('catalog_product', 'type_configurable', [
            'type' => 'int',
            'label' => 'Types',
            'input' => 'swatch_text',
            'required' => false,
            'user_defined' => true,
            'searchable' => true,
            'filterable' => false,
            'comparable' => false,
            'visible_in_advanced_search' => true,
            'apply_to' => 'simple,virtual,configurable',
            'is_used_in_grid' => true,
            'is_visible_in_grid' => false,
            'is_filterable_in_grid' => true,
            'option' => [
                'values' => ['Original','Light'],
            ],
        ]);
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
