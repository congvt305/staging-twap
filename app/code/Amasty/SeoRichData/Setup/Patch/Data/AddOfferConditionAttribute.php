<?php

declare(strict_types=1);

namespace Amasty\SeoRichData\Setup\Patch\Data;

use Amasty\SeoRichData\Model\Source\Product\OfferItemCondition;
use Exception;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\ResourceModel\Eav\Attribute;
use Magento\Eav\Setup\EavSetup;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Framework\Setup\Patch\PatchRevertableInterface;
use Zend_Validate_Exception;

class AddOfferConditionAttribute implements DataPatchInterface, PatchRevertableInterface
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
    /**
     * @return string[]
     */
    public static function getDependencies()
    {
        return [];
    }

    /**
     * @return string[]
     */
    public function getAliases()
    {
        return [];
    }

    /**
     * @return AddOfferConditionAttribute
     * @throws LocalizedException
     * @throws Zend_Validate_Exception
     */
    public function apply()
    {
        /** @var EavSetup $eavSetup */
        $eavSetup = $this->eavSetupFactory->create(['setup' => $this->moduleDataSetup]);
        $eavSetup->addAttribute(
            Product::ENTITY,
            OfferItemCondition::ATTRIBUTE_CODE,
            [
                'label' => 'Offer Item Condition',
                'type' => 'int',
                'backend' => '',
                'frontend' => '',
                'input' => 'select',
                'class' => '',
                'source' => OfferItemCondition::class,
                'global' => Attribute::SCOPE_GLOBAL,
                'visible' => true,
                'used_in_product_listing' => true,
                'required' => false,
                'user_defined' => true,
                'default' => OfferItemCondition::NEW_CONDITION,
                'searchable' => false,
                'filterable' => false,
                'comparable' => false,
                'visible_on_front' => false,
                'unique' => false,
                'apply_to' => ''
            ]
        );
        $this->addToAttributeSet($eavSetup);

        return $this;
    }

    /**
     * @return void
     */
    public function revert()
    {
        /** @var EavSetup $eavSetup */
        $eavSetup = $this->eavSetupFactory->create(['setup' => $this->moduleDataSetup]);
        $eavSetup->removeAttribute(Product::ENTITY, OfferItemCondition::ATTRIBUTE_CODE);
    }

    private function addToAttributeSet(EavSetup $eavSetup): void
    {
        $attributeId = $eavSetup->getAttributeId(
            Product::ENTITY,
            OfferItemCondition::ATTRIBUTE_CODE
        );
        $attributeSetIds = $eavSetup->getAllAttributeSetIds(
            Product::ENTITY
        );
        foreach ($attributeSetIds as $attributeSetId) {
            try {
                $attributeGroupId = $eavSetup->getAttributeGroupId(
                    Product::ENTITY,
                    $attributeSetId,
                    'search-engine-optimization'
                );
            } catch (Exception $e) {
                $attributeGroupId = $eavSetup->getDefaultAttributeGroupId(
                    Product::ENTITY,
                    $attributeSetId
                );
            }
            $eavSetup->addAttributeToSet(
                Product::ENTITY,
                $attributeSetId,
                $attributeGroupId,
                $attributeId
            );
        }
    }
}
