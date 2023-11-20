<?php
/**
 * @author Eguana Commerce
 * @copyright Copyright (c) 2020 Eguana {https://eguanacommerce.com}
 *  Created by PhpStorm
 *  User: Sonia Park
 *  Date: 3/16/20, 1:33 AM
 *
 */

namespace Eguana\BizConnect\Model;

class OperationLogRepository
{
    /**
     * @var LoggedOperationFactory
     */
    private $loggedOperationFactory;
    /**
     * @var LoggedOperation\LogFactory
     */
    private $operationLogFactory;
    /**
     * @var ResourceModel\LoggedOperation
     */
    private $loggedOperationResource;
    /**
     * @var ResourceModel\LoggedOperation\Log
     */
    private $operationLogResource;

    public function __construct(
        \Eguana\BizConnect\Model\LoggedOperationFactory $loggedOperationFactory,
        \Eguana\BizConnect\Model\LoggedOperation\LogFactory $operationLogFactory,
        \Eguana\BizConnect\Model\ResourceModel\LoggedOperation $loggedOperationResource,
        \Eguana\BizConnect\Model\ResourceModel\LoggedOperation\Log $operationLogResource
    ) {
        $this->loggedOperationFactory = $loggedOperationFactory;
        $this->operationLogFactory = $operationLogFactory;
        $this->loggedOperationResource = $loggedOperationResource;
        $this->operationLogResource = $operationLogResource;
    }

    public function createOrUpdateMessage($topicName, $serializedData, $to, $status, $direction, $storeId)
    {
        $operationObject = $this->loggedOperationFactory->create();
        $operationObject->setData('status', $status);
        $operationObject->setData('topic_name', $topicName);
        $operationObject->setData('serialized_data', $serializedData);
        $operationObject->setData('direction', $direction);
        $operationObject->setData('status', $status);
        $operationObject->setData('to', $to);
        $operationObject->setData('store_id', $storeId);

        $this->loggedOperationResource->save($operationObject);
        return $operationObject->getId();
    }
    public function addLogToOperation($operationId, $resustMessage)
    {
        $operationLog = $this->operationLogFactory->create();
        $operationLog->setData('result_message', $resustMessage);
        $operationLog->setData('logged_at', time());
        $operationLog->setData('operation_id', $operationId);

        $this->operationLogResource->save($operationLog);
    }
    public function getLoggedOperationById($operationId)
    {
        $connection = $this->loggedOperationResource->getConnection();
        return $connection->fetchRow(
            $connection
                ->select()
                ->from($this->loggedOperationResource->getMainTable())
                ->where('id = ?', $operationId)
        );
    }

    public function getLastLogsForOperation($operationId)
    {
        $limit = 1;
        $connection = $this->operationLogResource->getConnection();
        return $connection->fetchAll(
            $connection->select()
                ->from($this->operationLogResource->getMainTable())
                ->where('operation_id = ' . $operationId)
                ->limit($limit)
                ->order('logged_at DESC')
        );
    }

    public function getAllTopicNames()
    {
        return $this->getAllColumnValues('topic_name');
    }

    public function getAllStatus()
    {
        return $this->getAllColumnValues('status');
    }
    public function getAllTo()
    {
        return $this->getAllColumnValues('to');
    }

    private function getAllColumnValues($column)
    {
        $connection = $this->loggedOperationResource->getConnection();
        $result = $connection->fetchAll(
            $connection->select()
                ->from($this->loggedOperationResource->getMainTable(), [$column])
                ->distinct(true)
        );
        return array_map(
            function ($row) use ($column) {
                return $row[$column];
            },
            $result
        );
    }
}

