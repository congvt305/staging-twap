<?php
/**
 * Created by Eguana Team.
 * User: sonia
 * Date: 6/28/20
 * Time: 9:45 AM
 */

namespace Eguana\GWLogistics\Controller\SelectedCvsNotify;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\CsrfAwareActionInterface;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\App\Request\InvalidRequestException;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Controller\Result\RawFactory;
use Psr\Log\LoggerInterface;

class Index extends Action implements CsrfAwareActionInterface, HttpPostActionInterface
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

    /**
     * @var \Magento\Framework\Stdlib\CookieManagerInterface
     */
    private $cookieManager;

    /**
     * @var \Magento\Framework\Stdlib\Cookie\CookieMetadataFactory
     */
    private $cookieMetadataFactory;

    /**
     * @param \Magento\Framework\HTTP\Header $header
     * @param \Eguana\GWLogistics\Model\Service\SaveQuoteCvsLocation $saveQuoteCvsLocation
     * @param LoggerInterface $logger
     * @param RawFactory $rawFactory
     * @param Context $context
     * @param \Magento\Framework\Stdlib\CookieManagerInterface $cookieManager
     * @param \Magento\Framework\Stdlib\Cookie\CookieMetadataFactory $cookieMetadataFactory
     */
    public function __construct(
        \Magento\Framework\HTTP\Header $header,
        \Eguana\GWLogistics\Model\Service\SaveQuoteCvsLocation $saveQuoteCvsLocation, //todo move business logic to here.
        LoggerInterface $logger,
        RawFactory $rawFactory,
        Context $context,
        \Magento\Framework\Stdlib\CookieManagerInterface $cookieManager,
        \Magento\Framework\Stdlib\Cookie\CookieMetadataFactory $cookieMetadataFactory
    ) {
        parent::__construct($context);

        $this->saveQuoteCvsLocation = $saveQuoteCvsLocation;
        $this->logger = $logger;
        $this->rawFactory = $rawFactory;
        $this->header = $header;
        $this->cookieManager = $cookieManager;
        $this->cookieMetadataFactory = $cookieMetadataFactory;
    }

    public function execute()
    {

        $cvsStoreData = $this->getRequest()->getParams();
        $resultRedirect = $this->resultRedirectFactory->create();
        $redirectUrl = $this->isMobile() ? 'checkout/index/index/#shipping' : '*/index/index';
//        $redirectUrl = $this->isMobile() ? 'checkout/#shipping' : '*/index/index';
//        $redirectUrl = $this->isLineApp() ? 'checkout/index/index/#shipping' : '*/index/index';

        try {
            $this->saveQuoteCvsLocation->process($cvsStoreData);
        } catch (\Exception $e) {
            $this->logger->error('gwlogistics | cvs store data for a map selection', [$e->getMessage()]);
        }
        $publicCookieMetadata = $this->cookieMetadataFactory->createPublicCookieMetadata();
        $publicCookieMetadata->setDuration(2000);
        $publicCookieMetadata->setPath('/');
        $publicCookieMetadata->setHttpOnly(false);
        $this->cookieManager->setPublicCookie(
            'updatecvs',
            'true',
            $publicCookieMetadata
        );
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
