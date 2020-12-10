<?php
/**
 * @author Eguana Team
 * @copyriht Copyright (c) 2020 Eguana {http://eguanacommerce.com}
 * Created by PhpStorm
 * User: bilalyounas
 * Date: 15/9/20
 * Time: 4:46 PM
 */
declare(strict_types=1);

namespace Eguana\CustomerBulletin\Controller\Index;

use Eguana\CustomerBulletin\Api\TicketRepositoryInterface;
use Eguana\CustomerBulletin\Helper\Data;
use Magento\Framework\App\ResponseInterface;
use Eguana\CustomerBulletin\Controller\AbstractController\TicketLoaderInterface;
use Eguana\CustomerBulletin\Model\TicketFactory;
use Magento\Customer\Model\Session;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Controller\Result\RedirectFactory;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\View\Result\Layout;
use Magento\Framework\View\Result\Page;
use Magento\Framework\Controller\Result\Redirect;
use Psr\Log\LoggerInterface;

/**
 * Class Detail use to crate the page for masseges
 */
class Detail extends Action
{
    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var RequestInterface
     */
    private $requestInterface;

    /**
     * @var TicketFactory
     */
    private $ticketFactory;

    /**
     * @var TicketRepositoryInterface
     */
    private $ticketRepository;

    /**
     * @var Session
     */
    private $customerSession;

    /**
     * @var RedirectFactory
     */
    private $redirectFactory;

    /**
     * @var PageFactory
     */
    private $pageFactory;

    /**
     * @var Data
     */
    private $helperData;

    /**
     * @var TicketLoaderInterface
     */
    private $ticketLoader;

    /**
     * Detail constructor.
     * @param TicketFactory $ticketFactory
     * @param TicketLoaderInterface $ticketLoader
     * @param TicketRepositoryInterface $ticketRepository
     * @param RequestInterface $requestInterface
     * @param Context $context
     * @param LoggerInterface $logger
     * @param Data $helperData
     * @param Session $customerSession
     * @param RedirectFactory $redirectFactory
     * @param PageFactory $pageFactory
     */
    public function __construct(
        TicketFactory $ticketFactory,
        TicketLoaderInterface $ticketLoader,
        TicketRepositoryInterface $ticketRepository,
        RequestInterface $requestInterface,
        Context $context,
        LoggerInterface $logger,
        Data $helperData,
        Session $customerSession,
        RedirectFactory $redirectFactory,
        PageFactory $pageFactory
    ) {
        $this->pageFactory     = $pageFactory;
        $this->ticketLoader = $ticketLoader;
        $this->requestInterface = $requestInterface;
        $this->logger = $logger;
        $this->ticketFactory     = $ticketFactory;
        $this->ticketRepository = $ticketRepository;
        $this->helperData = $helperData;
        $this->redirectFactory = $redirectFactory;
        $this->customerSession   = $customerSession;
        parent::__construct($context);
    }

    /**
     * Message action
     *
     * @return ResponseInterface|Redirect|ResultInterface|Layout|Page
     */
    public function execute()
    {
        if ($this->getEnableValue()== 0) {
            return $this->redirectFactory->create()->setPath('/');
        }
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        if (!$this->customerSession->isLoggedIn()) {
            $resultRedirect->setUrl('/customer/account/login');
            $this->messageManager->addErrorMessage(__('Please Login Yourself'));
            return $resultRedirect;
        }
        $result = $this->ticketLoader->load($this->_request);
        if ($result instanceof ResultInterface) {
            return $result;
        }
        return  $this->pageFactory->create();
    }

    /**
     * Get the value of configuration
     *
     * @return mixed
     */
    public function getEnableValue()
    {
        return $this->helperData->getGeneralConfig('configuration/enabled');
    }
}
