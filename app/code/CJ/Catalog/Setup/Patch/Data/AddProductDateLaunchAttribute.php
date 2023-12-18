<?php
/**
 * Created by Eguana.
 * User: Brian
 * Date: 2020-07-02
 * Time: 오전 9:51
 */

namespace CJ\Catalog\Setup\Patch\Data;

use Magento\Catalog\Model\Product as ProductModel;
use Magento\Eav\Setup\EavSetup;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface;
use Magento\Eav\Model\Entity\Attribute\Source\Boolean as SourceBoolean;
use Psr\Log\LoggerInterface;

class AddProductDateLaunchAttribute implements DataPatchInterface
{
    const DATE_LAUNCH = 'date_launch';

    const PROMOTION_TEXT = 'promotion_text';

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

    public function __construct(
        ModuleDataSetupInterface $moduleDataSetup,
        EavSetupFactory $eavSetupFactory,
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $collectionFactory,
        \Magento\Framework\App\State $state,
        LoggerInterface $logger
    ) {
        $this->moduleDataSetup = $moduleDataSetup;
        $this->eavSetupFactory = $eavSetupFactory;
        $this->collectionFactory = $collectionFactory;
        $this->state = $state;
        $this->logger = $logger;
    }

    public function apply()
    {
        $this->state->setAreaCode(\Magento\Framework\App\Area::AREA_FRONTEND);
        $this->moduleDataSetup->startSetup();
        /** @var EavSetup $eavSetup */
        $eavSetup = $this->eavSetupFactory->create(['setup' => $this->moduleDataSetup]);

        $eavSetup->addAttribute(ProductModel::ENTITY, self::DATE_LAUNCH, [
            'type' => 'datetime',
            'backend' => 'Magento\Catalog\Model\Attribute\Backend\Startdate',
            'frontend' => '',
            'label' => 'Date Launch',
            'input' => 'date',
            'input_renderer' => 'CJ\Catalog\Block\Adminhtml\Form\Element\Datetime',
            'class' => 'validate-date',
            'global' => ScopedAttributeInterface::SCOPE_STORE,
            'visible' => true,
            'required' => false,
            'user_defined' => true,
            'default' => '',
            'searchable' => true,
            'filterable' => true,
            'filterable_in_search' => true,
            'visible_in_advanced_search' => true,
            'comparable' => false,
            'visible_on_front' => false,
            'used_in_product_listing' => true,
            'unique' => false
        ]);

        $eavSetup->addAttribute(ProductModel::ENTITY, self::PROMOTION_TEXT,
            [
                'type' => 'text',
                'label' => 'Promotion Text',
                'input' => 'text',
                'sort_order' => 100,
                'global' => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_STORE,
                'visible' => true,
                'required' => false,
                'user_defined' => true,
                'backend' => ''
            ]
        );

        $ATTRIBUTE_GROUP = 'General'; // Attribute Group Name
        $entityTypeId = $eavSetup->getEntityTypeId(\Magento\Catalog\Model\Product::ENTITY);
        $allAttributeSetIds = $eavSetup->getAllAttributeSetIds($entityTypeId);
        foreach ($allAttributeSetIds as $attributeSetId) {
            $groupId = $eavSetup->getAttributeGroupId($entityTypeId, $attributeSetId, $ATTRIBUTE_GROUP);
            $eavSetup->addAttributeToGroup(
                $entityTypeId,
                $attributeSetId,
                $groupId,
                self::DATE_LAUNCH,
                100
            );
            $eavSetup->addAttributeToGroup(
                $entityTypeId,
                $attributeSetId,
                $groupId,
                self::PROMOTION_TEXT,
                110
            );
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
