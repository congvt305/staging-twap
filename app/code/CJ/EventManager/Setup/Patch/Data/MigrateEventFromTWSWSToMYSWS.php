<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace CJ\EventManager\Setup\Patch\Data;

use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Framework\Setup\Patch\PatchVersionInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Store\Api\StoreRepositoryInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use CJ\EventManager\Helper\Data as HelperData;

class MigrateEventFromTWSWSToMYSWS implements DataPatchInterface, PatchVersionInterface
{
    const TW_SWS_STORE_CODE = 'default';
    const MY_SWS_STORE_CODE = 'my_sulwhasoo';
    /**
     * @var ModuleDataSetupInterface
     */
    private $moduleDataSetup;
    /**
     * @var StoreRepositoryInterface
     */
    protected $storeRepository;
    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;
    /**
     * @var HelperData
     */
    protected $helperData;

    /**
     * @param ModuleDataSetupInterface $moduleDataSetup
     * @param StoreRepositoryInterface $storeRepository
     * @param ScopeConfigInterface $scopeConfig
     * @param HelperData $helperData
     */
    public function __construct(
        \Magento\Framework\Setup\ModuleDataSetupInterface $moduleDataSetup,
        StoreRepositoryInterface $storeRepository,
        ScopeConfigInterface $scopeConfig,
        HelperData $helperData
    ) {
        $this->moduleDataSetup = $moduleDataSetup;
        $this->storeRepository = $storeRepository;
        $this->scopeConfig = $scopeConfig;
        $this->helperData = $helperData;
    }

    /**
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function apply()
    {
        $this->moduleDataSetup->startSetup();

        $storeTWSWSId = $this->storeRepository->get(self::TW_SWS_STORE_CODE)->getId();
        $storeMYSWSId = $this->storeRepository->get(self::MY_SWS_STORE_CODE)->getId();
        if ($storeTWSWSId && $storeMYSWSId){
            $this->helperData->migrateEvents($storeTWSWSId, $storeMYSWSId);
        }

        $this->moduleDataSetup->endSetup();
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
    public static function getVersion()
    {
        return '2.0.0';
    }

    /**
     * {@inheritdoc}
     */
    public function getAliases()
    {
        return [];
    }
}
