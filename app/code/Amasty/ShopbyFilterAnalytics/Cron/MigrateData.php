<?php

declare(strict_types=1);

namespace Amasty\ShopbyFilterAnalytics\Cron;

use Amasty\ShopbyFilterAnalytics\Model\MigrateData as MigrateDataModel;

class MigrateData
{
    /**
     * @var MigrateDataModel
     */
    private $migrateData;

    public function __construct(MigrateDataModel $migrateData)
    {
        $this->migrateData = $migrateData;
    }

    public function execute(): void
    {
        $this->migrateData->execute();
    }
}
