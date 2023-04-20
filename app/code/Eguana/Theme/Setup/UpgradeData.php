<?php

namespace Eguana\Theme\Setup;
use Magento\Catalog\Model\Category;
use Magento\Framework\Module\Setup\Migration;
use Magento\Framework\Setup\UpgradeDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Catalog\Setup\CategorySetupFactory;

/**
 * @codeCoverageIgnore
 */
class UpgradeData implements UpgradeDataInterface
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
    public function upgrade(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        $installer = $setup;
        $installer->startSetup();
        if (version_compare($context->getVersion(), '2.2.0') < 0) {
            // TODO
            /** @var \Magento\Catalog\Setup\CategorySetup $categorySetup */
            $categorySetup = $this->categorySetupFactory->create(['setup' => $setup]);
            $attributeCodes = [
                'satp_menu_block_bottom_content',
                'satp_menu_block_left_content',
                'satp_menu_block_left_width',
                'satp_menu_block_top_content',
                'satp_menu_font_icon',
                'satp_menu_icon_img',
                'satp_menu_cat_label',
                'satp_menu_static_width',
                'satp_menu_type',
                'satp_menu_hide_item'
            ];
            foreach ($attributeCodes as $code) {
                $categorySetup->removeAttribute(Category::ENTITY, $code);
            }
        }

        if (version_compare($context->getVersion(), '2.2.1') < 0) {
            /** @var \Magento\Catalog\Setup\CategorySetup $categorySetup */
            $categorySetup = $this->categorySetupFactory->create(['setup' => $setup]);
            $entityTypeId = $categorySetup->getEntityTypeId(\Magento\Catalog\Model\Category::ENTITY);
            $attributeSetId = $categorySetup->getDefaultAttributeSetId($entityTypeId);

            $menu_attributes = [
                'satp_menu_block_external_url' => [
                    'type' => 'text',
                    'label' => 'External Url',
                    'input' => 'text',
                    'required' => false,
                    'sort_order' => 150,
                    'global' => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_STORE,
                    'group' => 'Satp Menu'
                ]
            ];

            foreach($menu_attributes as $item => $data) {
                $categorySetup->addAttribute(\Magento\Catalog\Model\Category::ENTITY, $item, $data);
            }

            $idg =  $categorySetup->getAttributeGroupId($entityTypeId, $attributeSetId, 'Satp Menu');

            foreach($menu_attributes as $item => $data) {
                $categorySetup->addAttributeToGroup(
                    $entityTypeId,
                    $attributeSetId,
                    $idg,
                    $item,
                    $data['sort_order']
                );
            }
        }

        $setup->endSetup();
    }
}

