<?php
/**
 * Created by Eguana Team.
 * User: sonia
 * Date: 6/6/20
 * Time: 8:37 PM
 */

namespace Eguana\GWLogistics\Controller\ReceiverServerReply;


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
        $cvsStoreData = $this->getRequest()->getParams();

        if (!$cvsStoreData || $this->getRequest()->getMethod() !== 'POST') {
            $this->logger->debug('isXmlHttpRequest: ', [$this->getRequest()->isXmlHttpRequest()]);
            return $resultRaw->setHttpResponseCode($httpBadRequestCode);
        }
        $html = '';
        try {
            $this->saveCsvStoreData($cvsStoreData);
            $html = '<script>window.close();</script>';
        } catch (\Exception $e) {
            $html = $e->getMessage();
            $this->logger->error($e->getMessage());
        }
        return $resultRaw->setContents($html);
    }

    private function saveCsvStoreData($cvsStoreData)
    {
        $this->logger->debug('cvsStoreData: ', $cvsStoreData);
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
