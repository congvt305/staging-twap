<?php

declare(strict_types=1);

namespace Amasty\XmlSitemap\Setup\Patch\Data;

use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Amasty\XmlSitemap\Model\ResourceModel\Sitemap;
use Magento\Store\Model\StoreManagerInterface;

class AddExample implements DataPatchInterface
{
    public const EXAMPLE_NAME = 'Imported From Google Sitemap Settings';
    public const EXAMPLE_PATH = 'pub/media/google_sitemap_%s.xml';

    /**
     * @var ModuleDataSetupInterface
     */
    private $moduleDataSetup;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    public function __construct(
        StoreManagerInterface $storeManager,
        ModuleDataSetupInterface $moduleDataSetup
    ) {
        $this->moduleDataSetup = $moduleDataSetup;
        $this->storeManager = $storeManager;
    }

    public function apply(): DataPatchInterface
    {
        if ($this->isCanApply()) {
            $this->addExample();
        }

        return $this;
    }

    private function isCanApply(): bool
    {
        $connection = $this->moduleDataSetup->getConnection();
        $select = $connection->select();

        $select->from(
            $this->moduleDataSetup->getTable(Sitemap::TABLE_NAME),
            [new \Zend_Db_Expr('COUNT(*)')]
        );

        return !$connection->fetchOne($select);
    }

    private function addExample(): void
    {
        $connection = $this->moduleDataSetup->getConnection();
        $this->storeManager->reinitStores();

        foreach ($this->storeManager->getStores() as $store) {
            $data = [
                'name' => self::EXAMPLE_NAME,
                'path' => sprintf(self::EXAMPLE_PATH, $store->getId()),
                'store_id' => $store->getId()
            ];
            $connection->insert($this->moduleDataSetup->getTable(Sitemap::TABLE_NAME), $data);
        }
    }

    public static function getDependencies(): array
    {
        return [
            MoveDataToNewSchema::class
        ];
    }

    public function getAliases(): array
    {
        return [];
    }
}
