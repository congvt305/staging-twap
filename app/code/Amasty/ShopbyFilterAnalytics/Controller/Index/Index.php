<?php

declare(strict_types=1);

namespace Amasty\ShopbyFilterAnalytics\Controller\Index;

use Amasty\ShopbyFilterAnalytics\Model\ProcessAnalytics;
use Exception;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Controller\Result\Json as JsonResult;
use Magento\Framework\Controller\ResultFactory;
use Monolog\Logger;
use Psr\Log\LoggerInterface;

class Index implements HttpPostActionInterface
{
    public const ENTITY_ID_PARAM  = 'entity_id';

    public const OPTION_IDS_PARAM  = 'option_ids';

    /**
     * @var RequestInterface
     */
    private $request;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var ResultFactory
     */
    private $resultFactory;

    /**
     * @var ProcessAnalytics
     */
    private $processAnalytics;

    public function __construct(
        ProcessAnalytics $processAnalytics,
        RequestInterface $request,
        ResultFactory $resultFactory,
        LoggerInterface $logger
    ) {
        $this->request = $request;
        $this->logger = $logger;
        $this->resultFactory = $resultFactory;
        $this->processAnalytics = $processAnalytics;
    }

    public function execute()
    {
        /** @var JsonResult $resultJson */
        $resultJson = $this->resultFactory->create(ResultFactory::TYPE_JSON);
        if (!$this->request->isAjax()) {
            return $this->setForbiddenData($resultJson);
        }

        $data = ['error' => true];
        try {
            $entityId = $this->request->getParam(self::ENTITY_ID_PARAM);
            $this->processAnalytics->execute(
                $this->request->getParam(self::OPTION_IDS_PARAM, []),
                $entityId !== null ? (int) $entityId : $entityId
            );
            $data = ['success' => true];
        } catch (Exception $exception) {
            $this->logger->log(
                Logger::ERROR,
                'Cannot save statistics. Error: ' . $exception->getMessage()
            );
        }
        $resultJson->setData($data);

        return $resultJson;
    }

    private function setForbiddenData(JsonResult $resultJson): JsonResult
    {
        $resultJson->setStatusHeader(403, '1.1', 'Forbidden');

        return $resultJson->setData(
            [
                'error' => __('Forbidden'),
                'errorcode' => 403
            ]
        );
    }
}
