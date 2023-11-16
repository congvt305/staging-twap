<?php
/**
* @author Amasty Team
* @copyright Copyright (c) 2022 Amasty (https://www.amasty.com)
* @package SEO Toolkit Base for Magento 2
*/

declare(strict_types=1);

namespace Amasty\SeoToolkitLite\Setup\Patch\Data;

use Amasty\SeoToolkitLite\Model\RegistryConstants;
use Magento\Catalog\Model\Category;
use Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;

class AddRobots implements DataPatchInterface
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
        $eavSetup = $this->eavSetupFactory->create(['setup' => $this->moduleDataSetup]);
        if (!$eavSetup->getAttribute(Category::ENTITY, RegistryConstants::AMTOOLKIT_ROBOTS)) {
            $eavSetup->addAttribute(
                Category::ENTITY,
                RegistryConstants::AMTOOLKIT_ROBOTS,
                [
                    'type' => 'varchar',
                    'label' => 'Robots',
                    'input' => 'select',
                    'source' => \Amasty\SeoToolkitLite\Model\Source\Eav\Robots::class,
                    'required' => false,
                    'visible'  => true,
                    'sort_order' => 110,
                    'global' => ScopedAttributeInterface::SCOPE_STORE,
                    'group' => 'Search Engine Optimization'
                ]
            );
        }
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
