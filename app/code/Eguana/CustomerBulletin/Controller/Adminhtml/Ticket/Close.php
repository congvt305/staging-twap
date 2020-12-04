<?php
/**
 * @author Eguana Team
 * @copyriht Copyright (c) 2020 Eguana {http://eguanacommerce.com}
 * Created by PhpStorm
 * User: bilalyounas
 * Date: 15/9/20
 * Time: 4:46 PM
 */
namespace Eguana\CustomerBulletin\Controller\Adminhtml\Ticket;

use Eguana\CustomerBulletin\Api\TicketRepositoryInterface;
use Eguana\CustomerBulletin\Model\TicketFactory;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Model\Session;
use Magento\Framework\View\Result\Layout;
use Magento\Framework\App\Action\Action;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Controller\Result\RedirectFactory;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\App\ResponseInterface;
use Psr\Log\LoggerInterface;

/**
 * This controller will Close the Ticket
 *
 * Class Close
 */
class Close extends Action implements HttpGetActionInterface
{
    /**
     * @var Session
     */
    private $customerSession;

    /**
     * @var RedirectFactory
     */
    private $redirectFactory;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var TicketFactory
     */
    private $ticketFactory;

    /**
     * @var RequestInterface
     */
    private $requestInterface;

    /**
     * @var TicketRepositoryInterface
     */
    private $ticketRepository;

    /**
     * @var CustomerRepositoryInterface
     */
    private $customerRepository;

    /**
     * Close constructor.
     * @param Context $context
     * @param CustomerRepositoryInterface $customerRepository
     * @param LoggerInterface $logger
     * @param Session $customerSession
     * @param TicketFactory $ticketFactory
     * @param RequestInterface $requestInterface
     * @param TicketRepositoryInterface $ticketRepository
     * @param RedirectFactory $redirectFactory
     */
    public function __construct(
        Context $context,
        CustomerRepositoryInterface $customerRepository,
        LoggerInterface $logger,
        Session $customerSession,
        TicketFactory $ticketFactory,
        RequestInterface $requestInterface,
        TicketRepositoryInterface $ticketRepository,
        RedirectFactory $redirectFactory
    ) {
        $this->ticketRepository  = $ticketRepository;
        $this->customerRepository = $customerRepository;
        $this->ticketFactory     = $ticketFactory;
        $this->requestInterface = $requestInterface;
        $this->redirectFactory = $redirectFactory;
        $this->customerSession = $customerSession;
        $this->logger = $logger;
        parent::__construct($context);
    }

    /**
     * That functioon use to close the ticket
     *
     * @return ResponseInterface|ResultInterface|Layout
     */
    public function execute()
    {
        $model=$this->ticketFactory->create();
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        $model->load($this->requestInterface->getParam('ticket_id'));
        $model->setData('status', '0');
        try {
            $this->ticketRepository->save($model);
        } catch (\Exception $e) {
            $this->logger->info($e->getMessage());
        }
        $resultRedirect->setUrl($this->_redirect->getRefererUrl());
        return $resultRedirect;
    }
}
