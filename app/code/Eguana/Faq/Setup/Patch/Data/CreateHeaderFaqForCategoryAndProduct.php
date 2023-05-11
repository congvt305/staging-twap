<?php
declare(strict_types=1);

namespace Eguana\Faq\Setup\Patch\Data;

use Magento\Catalog\Model\Category as CategoryModel;
use Magento\Catalog\Model\Product as ProductModel;
use Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface;
use Magento\Eav\Setup\EavSetup;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Catalog\Setup\CategorySetupFactory;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;

class CreateHeaderFaqForCategoryAndProduct implements DataPatchInterface
{
    const HEADER_TITLE_FAQ = 'header_title_faq';

    /**
     * @var ModuleDataSetupInterface
     */
    private ModuleDataSetupInterface $moduleDataSetup;
    /**
     * @var EavSetupFactory
     */
    private EavSetupFactory $eavSetupFactory;

    /**
     * @var CategorySetupFactory
     */
    private $categorySetupFactory;

    /**
     * @param ModuleDataSetupInterface $moduleDataSetup
     * @param EavSetupFactory $eavSetupFactory
     * @param CategorySetupFactory $categorySetupFactory
     */
    public function __construct(
        ModuleDataSetupInterface $moduleDataSetup,
        EavSetupFactory          $eavSetupFactory,
        CategorySetupFactory     $categorySetupFactory
    ) {
        $this->moduleDataSetup = $moduleDataSetup;
        $this->eavSetupFactory = $eavSetupFactory;
        $this->categorySetupFactory = $categorySetupFactory;
    }

    /**
     * Create attribute header faq for category and product
     *
     * @return CreateFaqAttributeForCategoryAndProduct|void
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Zend_Validate_Exception
     */
    public function apply()
    {
        /** @var EavSetup $eavSetup */
        $eavSetup = $this->eavSetupFactory->create(['setup' => $this->moduleDataSetup]);
        $categorySetup = $this->categorySetupFactory->create(['setup' => $this->moduleDataSetup]);

        $eavSetup->addAttribute(ProductModel::ENTITY, self::HEADER_TITLE_FAQ, [
            'type' => 'text',
            'label' => 'Header Title FAQ',
            'input' => 'textarea',
            'source' => \Magento\Catalog\Model\Product\Attribute\Source\Layout::class,
            'global' => ScopedAttributeInterface::SCOPE_STORE,
            'user_defined' => true,
            'visible' => true,
            'required' => false,
            'is_used_in_grid' => true,
            'is_visible_in_grid' => false,
            'is_filterable_in_grid' => false,
            'is_visible_on_front' => true
        ]);

        $eavSetup->addAttribute(CategoryModel::ENTITY, self::HEADER_TITLE_FAQ, [
            'type' => 'text',
            'label' => 'Header Title FAQ',
            'input' => 'textarea',
            'sort_order' => 90,
            'source' => \Magento\Catalog\Model\Product\Attribute\Source\Layout::class,
            'global' => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_STORE,
            'visible' => true,
            'required' => false,
            'user_defined' => false,
            'backend' => ''
        ]);

        // get the catalog_product entity type id/code
        $entityTypeId = $categorySetup->getEntityTypeId(\Magento\Catalog\Model\Product::ENTITY);
        // For whatever reason, can only set these properties with updateAttribute
        $eavSetup->updateAttribute(
            ProductModel::ENTITY,
            self::HEADER_TITLE_FAQ,
            [
                'is_pagebuilder_enabled' => 1,
                'is_html_allowed_on_front' => 1,
                'is_wysiwyg_enabled' => 1
            ]
        );
        // get the attribute set ids of all the attribute sets present in your Magento store
        $attributeSetIds = $eavSetup->getAllAttributeSetIds($entityTypeId);
        foreach ($attributeSetIds as $attributeSetId) {
            // add attribute to group
            $categorySetup->addAttributeToGroup(
                $entityTypeId,
                $attributeSetId,
                'general',
                self::HEADER_TITLE_FAQ,
                110
            );
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
