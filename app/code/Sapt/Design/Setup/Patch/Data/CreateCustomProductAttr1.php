<?php 
declare(strict_types=1);

namespace Sapt\Design\Setup\Patch\Data; 

use Magento\Eav\Setup\EavSetup; 
use Magento\Eav\Setup\EavSetupFactory; 
use Magento\Framework\Setup\ModuleDataSetupInterface; 
use Magento\Framework\Setup\Patch\DataPatchInterface; 

class CreateCustomProductAttr1 implements DataPatchInterface
{  
    private $moduleDataSetup;
    private $eavSetupFactory; 
     
    public function __construct( ModuleDataSetupInterface $moduleDataSetup, EavSetupFactory $eavSetupFactory ) { $this->moduleDataSetup = $moduleDataSetup;
        $this->eavSetupFactory = $eavSetupFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function apply()
    {
        /** @var EavSetup $eavSetup */
        $eavSetup = $this->eavSetupFactory->create(['setup' => $this->moduleDataSetup]);
        $eavSetup->addAttribute('catalog_product', 'laneige_collagen', [
            'type' => 'int',
            'backend' => '',
            'frontend' => '',
            'label' => 'Laneige Collagen',
            'input' => 'boolean',
            'class' => '',
            'source' => \Magento\Eav\Model\Entity\Attribute\Source\Boolean::class,
            'global' => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_GLOBAL,
            'visible' => true,
            'required' => false,
            'user_defined' => false,
            'default' => '',
            'searchable' => false,
            'filterable' => false,
            'comparable' => false,
            'visible_on_front' => false,
            'used_in_product_listing' => false,
            'unique' => false,
            'apply_to' => '',
        ]);
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
    public function getAliases()
    {
        return [];
    }
}