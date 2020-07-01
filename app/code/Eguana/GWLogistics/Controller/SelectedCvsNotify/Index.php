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

    public function __construct(
        \Eguana\GWLogistics\Model\Service\SaveQuoteCvsLocation $saveQuoteCvsLocation, //todo move business logic to here.
        LoggerInterface $logger,
        RawFactory $rawFactory,
        Context $context
    ) {
        parent::__construct($context);

        $this->saveQuoteCvsLocation = $saveQuoteCvsLocation;
        $this->logger = $logger;
        $this->rawFactory = $rawFactory;
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
            $this->saveQuoteCvsLocation->process($cvsStoreData);
            $html = '<script>window.close();</script>';
        } catch (\Exception $e) {
            $html = $e->getMessage();
            $this->logger->error($e->getMessage());
        }
        return $resultRaw->setContents($html);
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
