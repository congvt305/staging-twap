<?php

namespace Sapt\Megamenu\Setup;

use Magento\Framework\Module\Setup\Migration;
use Magento\Framework\Setup\InstallDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Catalog\Setup\CategorySetupFactory;

/**
 * @codeCoverageIgnore
 */
class InstallData implements InstallDataInterface
{
    /**
     * Category setup factory
     *
     * @var CategorySetupFactory
     */
    private $categorySetupFactory;

    /**
     * Init
     *
     * @param CategorySetupFactory $categorySetupFactory
     */
    public function __construct(CategorySetupFactory $categorySetupFactory)
    {
        $this->categorySetupFactory = $categorySetupFactory;
    }

    /**
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function install(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        $installer = $setup;

        $installer->startSetup();

        $categorySetup = $this->categorySetupFactory->create(['setup' => $setup]);
        $entityTypeId = $categorySetup->getEntityTypeId(\Magento\Catalog\Model\Category::ENTITY);
        $attributeSetId = $categorySetup->getDefaultAttributeSetId($entityTypeId);

        $menu_attributes = [
            'sapt_menu_cat_columns' => [
                'type' => 'varchar',
                'label' => 'Sub Category Columns',
                'input' => 'select',
                'source' => 'Sapt\Megamenu\Model\Attribute\Subcatcolumns',
                'required' => false,
                'sort_order' => 40,
                'global' => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_STORE,
                'group' => 'Sapt Menu'
            ],
            'sapt_menu_float_type' => [
                'type' => 'varchar',
                'label' => 'Float',
                'input' => 'select',
                'source' => 'Sapt\Megamenu\Model\Attribute\Floattype',
                'required' => false,
                'sort_order' => 50,
                'global' => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_STORE,
                'group' => 'Sapt Menu'
            ],
            'sapt_menu_block_right_width' => [
                'type' => 'varchar',
                'label' => 'Right Block Width',
                'input' => 'select',
                'source' => 'Sapt\Megamenu\Model\Attribute\Width',
                'required' => false,
                'sort_order' => 120,
                'global' => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_STORE,
                'group' => 'Sapt Menu'
            ],
            'sapt_menu_block_right_content' => [
                'type' => 'text',
                'label' => 'Right Block',
                'input' => 'textarea',
                'required' => false,
                'sort_order' => 130,
                'wysiwyg_enabled' => true,
                'is_html_allowed_on_front' => true,
                'global' => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_STORE,
                'group' => 'Sapt Menu'
            ]
        ];

        foreach($menu_attributes as $item => $data) {
            $categorySetup->addAttribute(\Magento\Catalog\Model\Category::ENTITY, $item, $data);
        }

        $idg =  $categorySetup->getAttributeGroupId($entityTypeId, $attributeSetId, 'Sapt Menu');

        foreach($menu_attributes as $item => $data) {
            $categorySetup->addAttributeToGroup(
                $entityTypeId,
                $attributeSetId,
                $idg,
                $item,
                $data['sort_order']
            );
        }

        $installer->endSetup();
    }
}
