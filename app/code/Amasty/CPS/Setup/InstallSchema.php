<?php

namespace Amasty\CPS\Setup;

use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

class InstallSchema implements InstallSchemaInterface
{
    /**
     * @var InstallSchema\AddModuleTables
     */
    private $addModuleTables;

    public function __construct(InstallSchema\AddModuleTables $addModuleTables)
    {
        $this->addModuleTables = $addModuleTables;
    }

    /**
     * @param SchemaSetupInterface $setup
     * @param ModuleContextInterface $context
     * @throws \Zend_Db_Exception
     */
    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();

        $this->addModuleTables->execute($setup);

        $setup->endSetup();
    }
}
