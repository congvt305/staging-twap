<?php

namespace Amasty\CPS\Setup;

use Magento\Framework\Setup\InstallDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Amasty\CPS\Setup\InstallData\InstallModuleData;

class InstallData implements InstallDataInterface
{
    /**
     * @var InstallModuleData
     */
    private $installModuleDataData;

    public function __construct(InstallModuleData $installModuleDataData)
    {
        $this->installModuleDataData = $installModuleDataData;
    }

    /**
     * @param ModuleDataSetupInterface $setup
     * @param ModuleContextInterface $context
     */
    public function install(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        $this->installModuleDataData->execute();
    }
}
