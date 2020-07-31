<?php
/**
 * Created by Eguana Team.
 * User: sonia
 * Date: 6/20/20
 * Time: 5:41 PM
 */

namespace Eguana\GWLogistics\Controller\OrderStatusNotify;


use Eguana\GWLogistics\Model\Service\OrderStatusNotificationHandler;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\CsrfAwareActionInterface;
use Magento\Framework\App\Request\InvalidRequestException;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Controller\Result\RawFactory;
use Psr\Log\LoggerInterface;

class Index extends Action implements CsrfAwareActionInterface
{

    /**
     * @var RawFactory
     */
    private $rawFactory;
    /**
     * @var LoggerInterface
     */
    private $logger;
    /**
     * @var OrderStatusNotificationHandler
     */
    private $orderStatusNotificationHandler;

    public function __construct(
        OrderStatusNotificationHandler $orderStatusNotificationHandler,
        LoggerInterface $logger,
        RawFactory $rawFactory,
        Context $context
    ) {
        parent::__construct($context);
        $this->rawFactory = $rawFactory;
        $this->logger = $logger;
        $this->orderStatusNotificationHandler = $orderStatusNotificationHandler;
    }

    public function execute()
    {
        $refundData = null;
        $httpBadRequestCode = 400;
        $httpSuccessCode = 200;

        /** @var \Magento\Framework\Controller\Result\Raw $resultRaw */
        $resultRaw = $this->rawFactory->create();
        $notifyData = $this->getRequest()->getParams();

        if (!$notifyData || $this->getRequest()->getMethod() !== 'POST') {
            return $resultRaw->setHttpResponseCode($httpBadRequestCode);
        }
        $html = '';
        try {
            $this->saveNotifyData($notifyData);
            $html = '1|OK';
        } catch (\Exception $e) {
            $html = '0|' . $e->getMessage();
            $this->logger->error($e->getMessage());
        }
        return $resultRaw->setContents($html);
    }

    private function saveNotifyData(array $notifyData)
    {
        $this->logger->info('order status notification from GWLogistics: ', $notifyData);
        $this->orderStatusNotificationHandler->process($notifyData);
    }

    /**
     * @param RequestInterface $request
     * @return InvalidRequestException|null
     */
    public function createCsrfValidationException(RequestInterface $request): ?InvalidRequestException
    {
        return null;
    }

    /**
     * @param RequestInterface $request
     * @return bool|null
     */
    public function validateForCsrf(RequestInterface $request): ?bool
    {
        return true;
    }
}
