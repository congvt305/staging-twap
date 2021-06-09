<?php
/**
 * Created by Eguana Team.
 * User: sonia
 * Date: 6/28/20
 * Time: 9:45 AM
 */

namespace Eguana\GWLogistics\Controller\SelectedCvsNotify;

use Eguana\GWLogistics\Model\QuoteCvsLocation;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\CsrfAwareActionInterface;
use Magento\Framework\App\Request\InvalidRequestException;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Controller\Result\RawFactory;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Quote\Api\CartRepositoryInterface;
use Psr\Log\LoggerInterface;

class Index extends Action implements CsrfAwareActionInterface
{

    /**
     * @var \Eguana\GWLogistics\Model\Service\SaveQuoteCvsLocation
     */
    private $saveQuoteCvsLocation;
    /**
     * @var LoggerInterface
     */
    private $logger;
    /**
     * @var RawFactory
     */
    private $rawFactory;
    /**
     * @var \Magento\Framework\HTTP\Header
     */
    private $header;

    public function __construct(
        \Magento\Framework\HTTP\Header $header,
        \Eguana\GWLogistics\Model\Service\SaveQuoteCvsLocation $saveQuoteCvsLocation, //todo move business logic to here.
        LoggerInterface $logger,
        RawFactory $rawFactory,
        Context $context
    ) {
        parent::__construct($context);

        $this->saveQuoteCvsLocation = $saveQuoteCvsLocation;
        $this->logger = $logger;
        $this->rawFactory = $rawFactory;
        $this->header = $header;
    }

    public function execute()
    {
        $refundData = null;
        $httpBadRequestCode = 400;
        $httpErrorCode = 502;
        $httpSuccessCode = 200;

        /** @var \Magento\Framework\App\ResponseInterface $response */
        $response = $this->getResponse();
        $html = '';

        $cvsStoreData = $this->getRequest()->getParams();
        $resultRedirect = $this->resultRedirectFactory->create();
        $redirectUrl = $this->isMobile() ? 'checkout/index/index/#shipping' : '*/index/index';
        $writer = new \Zend\Log\Writer\Stream(BP . '/var/log/sociallogin.log');
        $logger = new \Zend\Log\Logger();
        $logger->addWriter($writer);
        $logger->info("Log 2: ".$redirectUrl);
//        $redirectUrl = $this->isMobile() ? 'checkout/#shipping' : '*/index/index';
//        $redirectUrl = $this->isLineApp() ? 'checkout/index/index/#shipping' : '*/index/index';

        if (!$cvsStoreData || $this->getRequest()->getMethod() !== 'POST') {
            $response->setHttpResponseCode($httpBadRequestCode);
            $response->setHeader('Content-Type', 'text/plain');
            $response->setBody($html);
            $response->sendResponse();
        }

        try {
            $this->saveQuoteCvsLocation->process($cvsStoreData);
        } catch (\Exception $e) {
            $this->logger->error('gwlogistics | cvs store data for a map selection', [$e->getMessage()]);
        }

        $resultRedirect->setPath($redirectUrl);
        return $resultRedirect;
    }

    private function isLineApp()
    {
//        $userAgent = $this->header->getHttpUserAgent();
//        return preg_match('/(iPhone|iPod|iPad|Android).*AppleWebKit.*Line/', $userAgent);
        return true;
    }

    private function isMobile()
    {
        $userAgent = $this->header->getHttpUserAgent();
        $writer = new \Zend\Log\Writer\Stream(BP . '/var/log/sociallogin.log');
        $logger = new \Zend\Log\Logger();
        $logger->addWriter($writer);
        $logger->info("Log 3: ".$userAgent);
        return preg_match('/(Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini)/i', $userAgent);
//        return true;
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
