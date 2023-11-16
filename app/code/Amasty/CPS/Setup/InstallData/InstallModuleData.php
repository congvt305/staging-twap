<?php

namespace Amasty\CPS\Setup\InstallData;

class InstallModuleData
{
    /**
     * @var \Amasty\CPS\Api\Indexer\DataHandlerInterface
     */
    private $indexer;

    public function __construct(\Amasty\CPS\Api\Indexer\DataHandlerInterface $dataHandler)
    {
        $this->indexer = $dataHandler;
    }

    /**
     * Installs module data
     */
    public function execute()
    {
        $this->indexer->reindexAll();
    }
}
