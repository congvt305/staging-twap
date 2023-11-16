<?php

namespace Amasty\CPS\Plugin\Catalogsearch\Model\Indexer;

use Amasty\CPS\Model\Indexer\DataHandler;

class FulltextPlugin
{
    /**
     * @var DataHandler
     */
    private $dataHandler;

    public function __construct(DataHandler $dataHandler)
    {
        $this->dataHandler = $dataHandler;
    }

    /**
     * @param \Magento\CatalogSearch\Model\Indexer\Fulltext $subject
     * @param $result
     * @return mixed
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function afterExecuteFull($subject, $result)
    {
        $this->dataHandler->reindexAll();

        return $result;
    }

    /**
     * @param \Magento\CatalogSearch\Model\Indexer\Fulltext $subject
     * @param mixed $result
     * @param array $ids
     * @return mixed
     */
    public function afterExecute($subject, $result, $ids)
    {
        $this->dataHandler->reindexByProduct($ids);

        return $result;
    }
}
