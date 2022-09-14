<?php
declare(strict_types=1);

namespace CJ\Checkout\Controller\Quote;

use Magento\CatalogInventory\Helper\Data;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Message\ManagerInterface;
use Magento\Quote\Model\Quote\Item;
use Magento\Quote\Model\QuoteRepository;
use Psr\Log\LoggerInterface;

class DeleteItems extends Action implements HttpPostActionInterface
{
    /**
     * Cookies name for messages
     */
    const MESSAGES_COOKIES_NAME = 'mage-messages';

    /**
     * @var RequestInterface
     */
    private $request;

    /**
     * @var \Magento\Framework\Json\Helper\Data
     */
    private $jsonHelper;

    /**
     * @var QuoteRepository
     */
    private $quoteRepository;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var \Magento\Framework\Stdlib\CookieManagerInterface
     */
    private $cookieManager;

    /**
     * @var \Magento\Framework\Stdlib\Cookie\CookieMetadataFactory
     */
    private $cookieMetadataFactory;

    /**
     * @param Context $context
     * @param RequestInterface $request
     * @param \Magento\Framework\Json\Helper\Data $jsonHelper
     * @param QuoteRepository $quoteRepository
     * @param LoggerInterface $logger
     * @param \Magento\Framework\Stdlib\Cookie\CookieMetadataFactory $cookieMetadataFactory
     * @param \Magento\Framework\Stdlib\CookieManagerInterface $cookieManager
     */
    public function __construct(
        Context $context,
        RequestInterface $request,
        \Magento\Framework\Json\Helper\Data $jsonHelper,
        QuoteRepository $quoteRepository,
        LoggerInterface $logger,
        \Magento\Framework\Stdlib\Cookie\CookieMetadataFactory $cookieMetadataFactory,
        \Magento\Framework\Stdlib\CookieManagerInterface $cookieManager
    ) {
        $this->request = $request;
        $this->jsonHelper = $jsonHelper;
        $this->quoteRepository = $quoteRepository;
        $this->logger = $logger;
        $this->cookieMetadataFactory = $cookieMetadataFactory;
        $this->cookieManager = $cookieManager;
        parent::__construct($context);
    }

    /**
     * Delete items
     *
     * @return ResponseInterface|\Magento\Framework\Controller\ResultInterface|void
     */
    public function execute()
    {
        if ($this->getRequest()->isAjax()) {
            $params = $this->request->getParams();
            try {
                $quote = $this->quoteRepository->getActive($params['quote_id']);
                foreach ($params['item_id'] as $itemId) {
                    $quote = $quote->removeItem($itemId);
                    $this->_removeErrorsFromQuoteAndItem($quote->getItemById($itemId), Data::ERROR_QTY);
                }
                $this->quoteRepository->save($quote);

                //must reset cookie message
                //because code will run to class Magento\Persistent\Observer\EmulateQuoteObserver first
                //then check error item from quote
                $publicCookieMetadata = $this->cookieMetadataFactory->createPublicCookieMetadata();
                $publicCookieMetadata->setDurationOneYear();
                $publicCookieMetadata->setPath('/');
                $publicCookieMetadata->setHttpOnly(false);
                $publicCookieMetadata->setSameSite('Strict');

                $this->cookieManager->setPublicCookie(
                    self::MESSAGES_COOKIES_NAME,
                    '',
                    $publicCookieMetadata
                );
                $this->messageManager->addSuccessMessage(__('Items has been deleted'));
            } catch (\Exception $e) {
                $this->logger->critical('Error when delete items which do not have enough qty: ' . $e);
                $this->messageManager->addErrorMessage(__('Items cannot be deleted. Please try again'));
            }
            $this->getResponse()->representJson(
                $this->jsonHelper->jsonEncode(
                    [
                        'backUrl' => $this->_redirect->getRedirectUrl()
                    ]
                )
            );
        }
    }

    /**
     * Removes error statuses from quote and item, set by this observer
     *
     * @param Item $item
     * @param int $code
     * @return void
     */
    protected function _removeErrorsFromQuoteAndItem($item, $code)
    {
        if ($item->getHasError()) {
            $params = ['origin' => 'cataloginventory', 'code' => $code];
            $item->removeErrorInfosByParams($params);
        }

        $quote = $item->getQuote();
        if ($quote->getHasError()) {
            $quoteItems = $quote->getItemsCollection();
            $canRemoveErrorFromQuote = true;
            foreach ($quoteItems as $quoteItem) {
                if ($quoteItem->getItemId() == $item->getItemId()) {
                    continue;
                }

                $errorInfos = $quoteItem->getErrorInfos();
                foreach ($errorInfos as $errorInfo) {
                    if ($errorInfo['code'] == $code) {
                        $canRemoveErrorFromQuote = false;
                        break;
                    }
                }

                if (!$canRemoveErrorFromQuote) {
                    break;
                }
            }

            if ($canRemoveErrorFromQuote) {
                $params = ['origin' => 'cataloginventory', 'code' => $code];
                $quote->removeErrorInfosByParams(null, $params);
            }
        }
    }
}
