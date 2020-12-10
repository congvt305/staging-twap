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

namespace Eguana\CustomerBulletin\Model\Email;

use Eguana\CustomerBulletin\Api\TicketRepositoryInterface;
use Eguana\CustomerBulletin\Helper\Data;
use Magento\Framework\Url;
use Eguana\CustomerBulletin\Model\TicketFactory;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Model\Session;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Area;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Controller\Result\RedirectFactory;
use Magento\Framework\Mail\Template\TransportBuilder;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\Translate\Inline\StateInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Store\Model\ScopeInterface;
use Psr\Log\LoggerInterface;

/**
 * Class Emailsender
 * send Email to customer as wel admin
 */
class EmailSender
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
     * @var StateInterface
     */
    private $inlineTranslation;

    /**
     * @var TransportBuilder
     */
    private $transportBuilder;

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @var ManagerInterface
     */
    protected $messageManager;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    /**
     * @var CustomerRepositoryInterface
     */
    private $customerRepository;

    /**
     * @var Url
     */
    private $urlHelper;

    /**
     * Close constructor.
     * @param Context $context
     * @param Data $helperData
     * @param Url $urlHelper
     * @param CustomerRepositoryInterface $customerRepository
     * @param LoggerInterface $logger
     * @param Session $customerSession
     * @param TicketFactory $ticketFactory
     * @param RequestInterface $requestInterface
     * @param TicketRepositoryInterface $ticketRepository
     * @param RedirectFactory $redirectFactory
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param StateInterface $inlineTranslation
     * @param TransportBuilder $transportBuilder
     * @param ScopeConfigInterface $scopeConfig
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        Context $context,
        Data $helperData,
        Url $urlHelper,
        CustomerRepositoryInterface $customerRepository,
        LoggerInterface $logger,
        Session $customerSession,
        TicketFactory $ticketFactory,
        RequestInterface $requestInterface,
        TicketRepositoryInterface $ticketRepository,
        RedirectFactory $redirectFactory,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        StateInterface $inlineTranslation,
        TransportBuilder $transportBuilder,
        ScopeConfigInterface $scopeConfig,
        StoreManagerInterface $storeManager
    ) {
        $this->ticketRepository  = $ticketRepository;
        $this->urlHelper = $urlHelper;
        $this->customerRepository = $customerRepository;
        $this->ticketFactory     = $ticketFactory;
        $this->requestInterface = $requestInterface;
        $this->redirectFactory = $redirectFactory;
        $this->helperData = $helperData;
        $this->customerSession = $customerSession;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->inlineTranslation = $inlineTranslation;
        $this->transportBuilder = $transportBuilder;
        $this->scopeConfig = $scopeConfig;
        $this->storeManager = $storeManager;
        $this->messageManager = $context->getMessageManager();
        $this->logger = $logger;
    }

    /**
     * email send to the customer on Closing of ticket Ticket
     *
     * @param $ticket_id
     * @param $template_id
     * @param $customerId
     */
    public function sendEmailToCustomer($ticket_id, $template_id, $customerId)
    {
        try {
            $ticket = $this->getTicketDetail($ticket_id);
            $ticket_store_id = $ticket['store_id'];
            $templateOptions = ['area' => Area::AREA_FRONTEND,
                'store' => $ticket_store_id];
            $templateVars = [
                'customer_name' => $this->getCustomerName($customerId),
                'ticket_url' => $this->getTicketurl($ticket_id, $ticket_store_id),
            ];
            $from = ['email' => $this->getEmail($ticket_store_id), 'name' => $this->getEmailSenderName($ticket_store_id)];
            $this->inlineTranslation->suspend();
            $to = $this->getCustomerEmail($customerId);
            $templateId = $this->scopeConfig->getValue(
                $template_id,
                ScopeInterface::SCOPE_STORE,
                $ticket_store_id
            );
            $transport = $this->transportBuilder
                ->setTemplateIdentifier($templateId)
                ->setTemplateOptions($templateOptions)
                ->setTemplateVars($templateVars)
                ->setFrom($from)
                ->addTo($to)
                ->getTransport();
            try {
                $transport->sendMessage();
                $this->messageManager->addSuccess(__('Email has been successfully sent to Customer'));
                $this->inlineTranslation->resume();
            } catch (\Exception $e) {
                $this->logger->info($e->getMessage());
            }
        } catch (\Exception $e) {
            $this->logger->info($e->getMessage());
        }
    }

    /**
     * email send to the Staff on Closing of ticket Ticket
     *
     * @param $ticket_id
     * @param $temp_id
     * @param $customerId
     */
    public function sendEmailToAdmin($ticket_id, $temp_id, $customerId)
    {
        try {
            $templateOptions = ['area' => Area::AREA_FRONTEND,
                'store' => $this->storeManager->getStore()->getId()];
            $storeId = $this->storeManager->getStore()->getId();
            $storeName = $this->storeManager->getStore()->getName();
            $templateVars = [
                'customer_name' => $this->getCustomerName($customerId),
                'ticket' => $this->getTicketDetail($ticket_id),
                'store_name' => $storeName,
            ];
            $from = ['email' => $this->getEmail(), 'name' => $this->getEmailSenderName()];
            $this->inlineTranslation->suspend();
            $to = $this->getEmailReceiver();
            $templateId = $this->scopeConfig->getValue(
                $temp_id,
                ScopeInterface::SCOPE_STORE,
                $storeId
            );
            $transport = $this->transportBuilder
                ->setTemplateIdentifier($templateId)
                ->setTemplateOptions($templateOptions)
                ->setTemplateVars($templateVars)
                ->setFrom($from)
                ->addTo($to)
                ->getTransport();
            try {
                $transport->sendMessage();
                $this->messageManager->addSuccess(__('Email hase been successfully sent to Administation'));
                $this->inlineTranslation->resume();
            } catch (\Exception $e) {
                $this->logger->info($e->getMessage());
            }
        } catch (\Exception $e) {
            $this->logger->info($e->getMessage());
        }
    }

    /**
     * get url of specific ticket
     * @param $ticket_id
     * @param $storeId
     * @return string
     */
    private function getTicketurl($ticket_id, $storeId)
    {
        $Ticket_url = '';
        try {
            $url =  $this->storeManager->getStore($storeId)->getBaseUrl();
            $Ticket_url = $url.('ticket/index/detail/ticket_id/').$ticket_id;
            return $Ticket_url;
        } catch (\Exception $e) {
            $this->logger->error($e->getMessage());
        }
        return $Ticket_url;
    }
    /**
     * get id of Sender from configuration
     *
     * @return mixed
     */
    private function getEmailSender()
    {
        return $this->helperData->getGeneralConfig('configuration/sender_email_identity');
    }

    /**
     * get Email of admin users from configuration
     *
     * @return mixed
     */
    private function getEmailReceiver()
    {
        $result = [];
        $emails = $this->helperData->getGeneralConfig('email/staff_email');
        if (!empty($emails)) {
            $result =  explode(',', $emails);
        }
        return $result;
    }

    /**
     * get Email of Sender from configuration
     *
     * @param $ticket_store_id
     * @return mixed
     */
    private function getEmail($ticket_store_id)
    {
        return $this->helperData->getEmail('trans_email/ident_' . $this->getEmailSender() . '/email', $ticket_store_id);
    }

    /**
     * get Name of Sender from configuration
     *
     * @param $ticket_store_id
     * @return mixed
     */
    private function getEmailSenderName($ticket_store_id)
    {
        return $this->helperData->getEmail('trans_email/ident_' . $this->getEmailSender() . '/name', $ticket_store_id);
    }

    /**
     * get customer name from its id
     *
     * @param $customerId
     * @return string
     */
    public function getCustomerName($customerId) : string
    {
        $customer = '';
        try {
            $customer = $this->customerRepository->getById($customerId);
            return $customer->getFirstname();
        } catch (\Exception $e) {
            $this->logger->info($e->getMessage());
        }
        return $customer;
    }

    /**
     * get customer Email from its id
     *
     * @param $customerId
     * @return string
     */
    public function getCustomerEmail($customerId) :string
    {
        $customer = '';
        try {
            $customer = $this->customerRepository->getById($customerId);
            return $customer->getEmail();
        } catch (\Exception $e) {
            $this->logger->info($e->getMessage());
        }
        return $customer;
    }

    /**
     * get ticket detail from ticketrepository
     *
     * @param $ticketId
     * @return string
     */
    private function getTicketDetail($ticketId)
    {
        $ticket = '';
        try {
            $ticket = $this->ticketRepository->getById($ticketId);
            return $ticket;
        } catch (\Exception $e) {
            $this->logger->info($e->getMessage());
        }
        return $ticket;
    }
}
