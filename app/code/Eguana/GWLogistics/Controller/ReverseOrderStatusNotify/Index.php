<?php
/**
 * Created by Eguana Team.
 * User: sonia
 * Date: 6/20/20
 * Time: 5:41 PM
 */

namespace Eguana\GWLogistics\Controller\ReverseOrderStatusNotify;


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

    public function __construct(
        LoggerInterface $logger,
        RawFactory $rawFactory,
        Context $context
    ) {
        parent::__construct($context);
        $this->rawFactory = $rawFactory;
        $this->logger = $logger;
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
            $this->logger->debug('isXmlHttpRequest: ', [$this->getRequest()->isXmlHttpRequest()]);
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

    private function saveNotifyData($notifyData)
    {
        //todo check checksum and save correctly.
        $this->logger->debug('notifyDataforRmaFrom GWLogistics: ', $notifyData);
        //todo save QuoteCvsLocation from repository or resource model
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
