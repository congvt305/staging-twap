<?php

namespace CJ\CatalogProduct\Setup\Patch\Data;

use Magento\Catalog\Model\Product;
use Magento\Eav\Model\Entity\Attribute\Source\Boolean;
use CJ\CatalogProduct\Model\Entity\Attribute\Source;
use Magento\Eav\Setup\EavSetup;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Framework\Setup\Patch\PatchVersionInterface;
use Magento\Eav\Model\Entity\Attribute\Backend\ArrayBackend;
use CJ\CatalogProduct\Helper\Data;

class AddProductAttributes implements DataPatchInterface, PatchVersionInterface
{
    /**
     * @var ModuleDataSetupInterface
     */
    private $moduleDataSetup;

    /**
     * @var EavSetupFactory
     */
    private $eavSetupFactory;

    /**
     * PatchInitial constructor.
     * @param ModuleDataSetupInterface $moduleDataSetup
     */
    public function __construct(
        ModuleDataSetupInterface $moduleDataSetup,
        EavSetupFactory          $eavSetupFactory
    )
    {
        $this->moduleDataSetup = $moduleDataSetup;
        $this->eavSetupFactory = $eavSetupFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function apply()
    {
        /** @var EavSetup $eavSetup */
        $eavSetup = $this->eavSetupFactory->create(['setup' => $this->moduleDataSetup]);

        $eavSetup->addAttribute(
            Product::ENTITY,
            Data::RANKING_STATUS_ATTRIBUTE_CODE,
            [
                'group' => 'General',
                'backend' => ArrayBackend::class,
                'frontend' => '',
                'label' => 'Ranking Status',
                'type' => 'int',
                'input' => 'select',
                'source' => Source::class,
                'default' => Source::VALUE_STABLE,
                'global' => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_STORE,
                'visible' => true,
                'required' => false,
                'user_defined' => false,
                'apply_to' => '',
                'visible_on_front' => true,
                'used_in_product_listing' => true,
                'is_used_in_grid' => true,
                'is_visible_in_grid' => true,
                'is_filterable_in_grid' => true,
            ]
        );

        $eavSetup->addAttribute(
            Product::ENTITY,
            Data::RANKING_ATTRIBUTE_CODE,
            [
                'group' => 'General',
                'backend' => '',
                'frontend' => '',
                'label' => 'Ranking',
                'type' => 'int',
                'input' => 'text',
                'class' => '',
                'default' => 9999999,
                'global' => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_STORE,
                'visible' => true,
                'required' => false,
                'user_defined' => true,
                'apply_to' => '',
                'visible_on_front' => true,
                'used_in_product_listing' => true,
                'is_used_in_grid' => true,
                'is_visible_in_grid' => true,
                'is_filterable_in_grid' => true,
            ]
        );

        $eavSetup->addAttribute(
            Product::ENTITY,
            Data::ON_SALES_ATTRIBUTE_CODE,
            [
                'group' => 'General',
                'backend' => '',
                'frontend' => '',
                'label' => 'On Sales',
                'type' => 'int',
                'input' => 'boolean',
                'class' => '',
                'source' => Boolean::class,
                'default' => Boolean::VALUE_NO,
                'global' => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_STORE,
                'visible' => true,
                'required' => false,
                'user_defined' => true,
                'apply_to' => '',
                'visible_on_front' => true,
                'used_in_product_listing' => true,
                'is_used_in_grid' => true,
                'is_visible_in_grid' => true,
                'is_filterable_in_grid' => true,
            ]
        );
    }

    /**
     * {@inheritdoc}
     */
    public static function getDependencies()
    {
        return [];
    }

    /**
     * {@inheritdoc}
     */
    public static function getVersion()
    {
        return '2.0.0';
    }

    /**
     * {@inheritdoc}
     */
    public function getAliases()
    {
        return [];
    }
}
