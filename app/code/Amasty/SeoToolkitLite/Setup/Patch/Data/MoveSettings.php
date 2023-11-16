<?php
/**
* @author Amasty Team
* @copyright Copyright (c) 2022 Amasty (https://www.amasty.com)
* @package SEO Toolkit Base for Magento 2
*/

declare(strict_types=1);

namespace Amasty\SeoToolkitLite\Setup\Patch\Data;

use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;

class MoveSettings implements DataPatchInterface
{
    private const CORE_CONFIG_DATA_TABLE = 'core_config_data';
    private const SETTING_CONFIG_ID = 'config_id';
    private const SETTING_PATH = 'path';

    /**
     * @var array
     */
    private $settingMap = [
        'amseotoolkit/general/home_redirect' => 'amseotoolkit/redirect_settings/home_redirect',
        'amseotoolkit/general/four_zero_four_redirect' => 'amseotoolkit/redirect_settings/four_zero_four_redirect'
    ];

    /**
     * @var ModuleDataSetupInterface
     */
    private $setup;

    public function __construct(
        ModuleDataSetupInterface $setup
    ) {
        $this->setup = $setup;
    }

    public static function getDependencies(): array
    {
        return [];
    }

    public function getAliases(): array
    {
        return [];
    }

    public function apply(): void
    {
        $connection = $this->setup->getConnection();
        $coreConfig = $this->setup->getTable(self::CORE_CONFIG_DATA_TABLE);
        
        $select = $this->setup->getConnection()->select()
            ->from($coreConfig, [self::SETTING_CONFIG_ID, self::SETTING_PATH])
            ->where(self::SETTING_PATH . ' IN(?)', array_keys($this->settingMap));
        
        $settings = $connection->fetchPairs($select);

        foreach ($settings as $key => $value) {
            if (isset($this->settingMap[$value])) {
                $connection->update(
                    $coreConfig,
                    [self::SETTING_PATH => $this->settingMap[$value]],
                    [self::SETTING_CONFIG_ID . ' = ?' => $key]
                );
            }
        }
    }
}
