<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Amore\CustomerRegistration\Model;

use Magento\Framework\App\ResourceConnection as AppResource;
use Magento\Framework\DB\Ddl\Sequence as DdlSequence;
use Magento\Framework\Webapi\Exception;
use Psr\Log\LoggerInterface as Logger;
use Magento\Framework\App\ResourceConnection;

/**
 * Class Builder
 *
 * @api
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @since 100.0.2
 */
class SequenceBuilder
{

    /**
     * @var AppResource
     */
    protected $appResource;

    /**
     * @var DdlSequence
     */
    protected $ddlSequence;

    /**
     * Concrete data of sequence
     *
     * @var array
     */
    protected $data = [];

    /**
     * @var Logger
     */
    protected $logger;

    /**
     * @param AppResource $appResource
     * @param DdlSequence $ddlSequence
     * @param Logger $logger
     */
    public function __construct(
        AppResource $appResource,
        DdlSequence $ddlSequence,
        Logger $logger
    ) {
        $this->appResource = $appResource;
        $this->ddlSequence = $ddlSequence;
        $this->logger = $logger;
    }

    /**
     * @param string $entityType
     * @return $this
     */
    public function setEntityType($entityType)
    {
        $this->data['entity_type'] = $entityType;
        return $this;
    }

    /**
     * @param int $storeId
     * @return $this
     */
    public function setWebsiteId($websiteId)
    {
        $this->data['webiste_id'] = $websiteId;
        return $this;
    }

    /**
     * @param int $startValue
     * @return $this
     */
    public function setStartValue($startValue)
    {
        $this->data['start_value'] = $startValue;
        return $this;
    }

    /**
     * Returns sequence table name
     *
     * @return string
     */
    protected function getSequenceName()
    {
        return $this->appResource->getTableName(
            sprintf(
                'sequence_%s_%s',
                $this->data['entity_type'],
                $this->data['webiste_id']
            )
        );
    }

    /**
     * Create sequence with metadata and profile
     *
     * @throws \Exception
     * @throws \Magento\Framework\Exception\AlreadyExistsException
     * @return void
     */
    public function create()
    {

        $this->data['sequence_table'] = $this->getSequenceName();

        try {

            $connection = $this->appResource->getConnection(ResourceConnection::DEFAULT_CONNECTION);
            if (!$connection->isTableExists($this->data['sequence_table'])) {
                $connection->query(
                    $this->ddlSequence->getCreateSequenceDdl(
                        $this->data['sequence_table'],
                        $this->data['start_value']
                    )
                );
            }
        } catch (Exception $e) {
            $this->logger->critical($e);
            throw $e;
        }
    }
}
