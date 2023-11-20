<?php

namespace Sapt\Megamenu\Setup;

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
                'sapt_menu_block_bottom_content',
                'sapt_menu_block_left_content',
                'sapt_menu_block_left_width',
                'sapt_menu_block_top_content',
                'sapt_menu_font_icon',
                'sapt_menu_icon_img',
                'sapt_menu_cat_label',
                'sapt_menu_static_width',
                'sapt_menu_type',
                'sapt_menu_hide_item'
            ];
            foreach ($attributeCodes as $code) {
                $categorySetup->removeAttribute(Category::ENTITY, $code);
            }
        }

        $setup->endSetup();
    }
}
