<?php
/**
 * @author Eguana Commerce
 * @copyright Copyright (c) 2020 Eguana {https://eguanacommerce.com}
 *  Created by PhpStorm
 *  User: Sonia Park
 *  Date: 3/14/20, 5:11 PM
 *
 */

namespace Eguana\BizConnect\Controller\Adminhtml\Operation\Log;

use Magento\Backend\App\Action;
use Magento\Framework\App\Action\HttpGetActionInterface as HttpGet;
use Magento\Framework\Controller\Result\JsonFactory;

class Details extends Action implements HttpGet
{
    /**
     * Authorization level of a basic admin session.
     *
     * @see _isAllowed()
     */
    const ADMIN_RESOURCE = 'Eguana_BizConnect::operation_log';
    /**
     * @var JsonFactory
     */
    private $jsonFactory;
    /**
     * @var \Eguana\BizConnect\Model\OperationLogRepository
     */
    private $operationLogRepository;

    public function __construct(
        Action\Context $context,
        JsonFactory $jsonFactory,
        \Eguana\BizConnect\Model\OperationLogRepository $operationLogRepository
    ) {
        parent::__construct($context);
        $this->jsonFactory = $jsonFactory;
        $this->operationLogRepository = $operationLogRepository;
    }



    public function execute()
    {
        $operationId = $this->getRequest()->getParam('id');
        $loggedOperation = $this->operationLogRepository->getLoggedOperationById($operationId);
        $payload = [
            'view_log_title' => $loggedOperation['topic_name'],
            'view_log_message' => $loggedOperation['serialized_data'],
            'view_log_logs' => $this->getLogs($operationId),
        ];
        return $this->jsonFactory->create()->setData($payload);
    }

    private function getLogs($operationId)
    {
        $logs = $this->operationLogRepository->getLastLogsForOperation($operationId);
        return array_map(function ($log) {
            preg_match("/.*Exception: (.+) in/", $log['result_message'], $matches);

            if (empty($matches[1])) {
                preg_match("/^(Message [a-zA-Z]+).*/", $log['result_message'], $successMatches);
                if (!empty($successMatches[1])) {
                    $matches[1] = $successMatches[1];
                }
            }

            $log['error_title'] = !empty($matches[1]) ? $matches[1] : '';

            return $log;
        }, $logs);
        return $logs;
    }
}
