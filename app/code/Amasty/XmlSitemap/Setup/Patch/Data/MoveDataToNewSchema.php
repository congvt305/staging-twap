<?php

declare(strict_types=1);

namespace Amasty\XmlSitemap\Setup\Patch\Data;

use Amasty\XmlSitemap\Setup\Model\NewSchemaDataConverter;
use Magento\Framework\Model\ResourceModel\Iterator;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Framework\Setup\Patch\NonTransactionableInterface;

class MoveDataToNewSchema implements DataPatchInterface, NonTransactionableInterface
{
    const OLD_MAIN_TABLE_NAME = 'amasty_xml_sitemap';

    /**
     * @var Iterator
     */
    private $resourceIterator;

    /**
     * @var ModuleDataSetupInterface
     */
    private $moduleDataSetup;

    /**
     * @var NewSchemaDataConverter
     */
    private $newSchemaDataConverter;

    public function __construct(
        Iterator $resourceIterator,
        ModuleDataSetupInterface $moduleDataSetup,
        NewSchemaDataConverter $newSchemaDataConverter
    ) {
        $this->resourceIterator = $resourceIterator;
        $this->moduleDataSetup = $moduleDataSetup;
        $this->newSchemaDataConverter = $newSchemaDataConverter;
    }

    public static function getDependencies()
    {
        return [];
    }

    public function getAliases()
    {
        return [];
    }

    /**
     * @return $this|MoveDataToNewSchema
     *
     * @throws \Throwable
     */
    public function apply()
    {
        $connection = $this->moduleDataSetup->getConnection();
        $oldTable = $this->moduleDataSetup->getTable(self::OLD_MAIN_TABLE_NAME);

        if ($connection->isTableExists($oldTable)) {
            $select = $connection->select()->from($oldTable);
            $connection->beginTransaction();

            try {
                $this->resourceIterator->walk($select, [[$this, 'moveSitemapToNewSchema']]);
                $connection->commit();
            } catch (\Throwable $e) {
                $connection->rollBack();

                throw $e;
            }

            $connection->dropTable($oldTable);
        }

        return $this;
    }

    public function moveSitemapToNewSchema(array $args): void
    {
        if (!empty($args)) {
            $tablesData = $this->newSchemaDataConverter->convert($args['row']);
            $connection = $this->moduleDataSetup->getConnection();

            foreach ($tablesData as $tableName => $tableData) {
                $tableName = $this->moduleDataSetup->getTable($tableName);

                if (!empty($tableData)) {
                    $connection->insertOnDuplicate($tableName, $tableData);
                }
            }
        }
    }
}
