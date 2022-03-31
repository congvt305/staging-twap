<?php
declare(strict_types=1);

namespace Amasty\Feed\Setup\Patch\Data;

use Amasty\Feed\Setup\Operation\UpgradeDataOperationInterface;
use Magento\Framework\App\Area;
use Magento\Framework\App\State;
use Magento\Framework\Module\ResourceInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;

class UpgradeDataTo270 implements DataPatchInterface
{
    /**
     * @var ResourceInterface
     */
    private $moduleResource;

    /**
     * @var ModuleDataSetupInterface
     */
    private $moduleDataSetup;

    /**
     * @var State
     */
    private $appState;

    /**
     * @var array
     */
    private $operations;

    public function __construct(
        ResourceInterface $moduleResource,
        ModuleDataSetupInterface $moduleDataSetup,
        State $appState,
        array $operations = []
    ) {
        $this->moduleResource = $moduleResource;
        $this->moduleDataSetup = $moduleDataSetup;
        $this->appState = $appState;
        $this->operations = $operations;
    }

    public function apply()
    {
        $this->appState->emulateAreaCode(
            Area::AREA_ADMINHTML,
            [$this, 'upgradeDataWithEmulationAreaCode']
        );
    }

    public static function getDependencies()
    {
        return [];
    }

    public function getAliases()
    {
        return [];
    }

    public function upgradeDataWithEmulationAreaCode()
    {
        $setupDataVersion = (string)$this->moduleResource->getDataVersion('Amasty_Feed');
        $this->moduleDataSetup->startSetup();
        foreach ($this->operations as $operation) {
            if ($operation instanceof UpgradeDataOperationInterface) {
                $operation->execute($this->moduleDataSetup, $setupDataVersion);
            }
        }
        $this->moduleDataSetup->endSetup();
    }
}
