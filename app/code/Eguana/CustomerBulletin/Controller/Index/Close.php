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
use Eguana\CustomerBulletin\Model\Email\EmailSender;
use Eguana\CustomerBulletin\Model\TicketFactory;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Customer\Model\Session;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Controller\Result\RedirectFactory;
use Magento\Framework\View\Result\Layout;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Message\ManagerInterface;
use Psr\Log\LoggerInterface;

/**
 * This controller will Close the Ticket
 *
 * Class Close
 */
class Close extends Action
{
    /**#@+*/
    const CUSTOMER_EMAIL_TEMPLATE = 'ticket_managment/email/customer_email_close';
    const ADMIN_EMAIL_TEMPLATE = 'ticket_managment/email/staff_email_close';
    /**#@-*/

    /**
     * @var EmailSender
     */
    private $emailSender;
    /**
     * @var Session
     */
    private $customerSession;

    /**
     * @var RedirectFactory
     */
    private $redirectFactory;

    /**
     * @var Data
     */
    private $helperData;

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
     * @var ManagerInterface
     */
    protected $messageManager;

    /**
     * Close constructor.
     * @param Context $context
     * @param Data $helperData
     * @param EmailSender $emailSender
     * @param LoggerInterface $logger
     * @param Session $customerSession
     * @param TicketFactory $ticketFactory
     * @param RequestInterface $requestInterface
     * @param TicketRepositoryInterface $ticketRepository
     * @param RedirectFactory $redirectFactory
     */
    public function __construct(
        Context $context,
        Data $helperData,
        EmailSender $emailSender,
        LoggerInterface $logger,
        Session $customerSession,
        TicketFactory $ticketFactory,
        RequestInterface $requestInterface,
        TicketRepositoryInterface $ticketRepository,
        RedirectFactory $redirectFactory
    ) {
        $this->ticketRepository  = $ticketRepository;
        $this->emailSender          = $emailSender;
        $this->ticketFactory     = $ticketFactory;
        $this->requestInterface = $requestInterface;
        $this->redirectFactory = $redirectFactory;
        $this->helperData = $helperData;
        $this->customerSession = $customerSession;
        $this->messageManager = $context->getMessageManager();
        $this->logger = $logger;
        parent::__construct($context);
    }

    /**
     * That functioon use to close the ticket
     *
     * @return ResponseInterface|Redirect|ResultInterface|Layout
     */

    public function execute()
    {
        if ($this->getEnableValue() == 0) {
            return $this->redirectFactory->create()->setPath('/');
        }
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        if (!$this->customerSession->isLoggedIn()) {
            $resultRedirect->setUrl('/customer/account/login');
            $this->messageManager->addErrorMessage('Please Login Yourself');
            return $resultRedirect;
        } else {
            $model=$this->ticketFactory->create();
            $model->load($this->requestInterface->getParam('ticket_id'));
            $model->setData('status', '0');
            try {
                $this->ticketRepository->save($model);
            } catch (\Exception $e) {
                $this->logger->info($e->getMessage());
            }
        }
        $resultRedirect->setUrl($this->_redirect->getRefererUrl());
        return $resultRedirect;
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
