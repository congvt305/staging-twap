<?php

namespace Amasty\CPS\Setup;

use Amasty\CPS\Api\Data\BrandProductInterface;
use Magento\Framework\Setup\UninstallInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\Setup\ModuleContextInterface;

class Uninstall implements UninstallInterface
{
    /**
     * @param SchemaSetupInterface $setup
     * @param ModuleContextInterface $context
     */
    public function uninstall(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();

        $table = $setup->getTable(BrandProductInterface::MAIN_TABLE);
        $setup->getConnection()->dropTable($setup->getTable($table));

        $setup->endSetup();
    }
}
