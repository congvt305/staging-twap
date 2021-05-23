<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Eguana\Directory\Setup\Patch\Data;

use Eguana\Directory\Setup\RegionCityDataInstaller;
use Eguana\Directory\Setup\RegionCityDataInstallerFactory;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;

/**
 * Patch is mechanism, that allows to do atomic upgrade data changes
 */
class AddDataForVietnam implements DataPatchInterface
{
    /**
     * @var ModuleDataSetupInterface $moduleDataSetup
     */
    private $moduleDataSetup;
    /**
     * @var RegionCityDataInstallerFactory
     */
    private $regionCityDataInstallerFactory;

    /**
     * @param ModuleDataSetupInterface $moduleDataSetup
     */
    public function __construct(
        ModuleDataSetupInterface $moduleDataSetup,
        RegionCityDataInstallerFactory $regionCityDataInstallerFactory

    ) {
        $this->moduleDataSetup = $moduleDataSetup;
        $this->regionCityDataInstallerFactory = $regionCityDataInstallerFactory;
    }

    /**
     * Do Upgrade
     *
     * @return void
     */
    public function apply()
    {
        $this->moduleDataSetup->startSetup();
        /** @var RegionCityDataInstaller $regionCityDataInstaller */
        $regionCityDataInstaller = $this->regionCityDataInstallerFactory->create();
        $regionCityDataInstaller->addCountryRegionsCities($this->moduleDataSetup->getConnection(), $this->getDataForVietnam());
        $this->moduleDataSetup->endSetup();

    }

    /**
     * {@inheritdoc}
     */
    public function getAliases()
    {
        return [];
    }

    /**
     * {@inheritdoc}
     */
    public static function getDependencies()
    {
        return [\Eguana\Directory\Setup\Patch\Data\AddDataForTaiwan::class];
    }

    /**
     * @return array[]
     */
    public function getDataForVietnam()
    {

    }
}

