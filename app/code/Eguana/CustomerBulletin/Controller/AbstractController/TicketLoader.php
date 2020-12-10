<?php
/**
 * @author Eguana Team
 * @copyriht Copyright (c) 2020 Eguana {http://eguanacommerce.com}
 * Created by PhpStorm
 * User: bilalyounas
 * Date: 15/9/20
 * Time: 4:46 PM
 */
namespace Eguana\CustomerBulletin\Controller\AbstractController;

use Magento\Framework\App\Action\Context;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\Registry;
use Magento\Framework\Controller\Result\ForwardFactory;
use Magento\Framework\Controller\Result\RedirectFactory;
use Eguana\CustomerBulletin\Model\TicketFactory;
use Eguana\CustomerBulletin\Api\TicketRepositoryInterface;
use Magento\Framework\UrlInterface;

/**
 * Load Ticket
 *
 * Class TicketLoader
 */
class TicketLoader implements TicketLoaderInterface
{
    /**
     * @var TicketRepositoryInterface
     */
    private $ticketRepository;

    /**
     * @var TicketFactory
     */
    private $ticketFactory;

    /**
     * @var Registry
     */
    private $registry;

    /**
     * @var TicketViewAuthorizationInterface
     */
    private $ticketAuthorization;

    /**
     * @var UrlInterface
     */
    private $url;

    /**
     * @var ManagerInterface
     */
    protected $messageManager;

    /**
     * @var ForwardFactory
     */
    private $resultForwardFactory;

    /**
     * @var RedirectFactory
     */
    private $redirectFactory;

    /**
     * TicketLoader constructor.
     * @param TicketFactory $orderFactory
     * @param Context $context
     * @param TicketViewAuthorizationInterface $ticketAuthorization
     * @param TicketRepositoryInterface $ticketRepository
     * @param Registry $registry
     * @param UrlInterface $url
     * @param ForwardFactory $resultForwardFactory
     * @param RedirectFactory $redirectFactory
     */
    public function __construct(
        TicketFactory $orderFactory,
        Context $context,
        TicketViewAuthorizationInterface $ticketAuthorization,
        TicketRepositoryInterface $ticketRepository,
        Registry $registry,
        UrlInterface $url,
        ForwardFactory $resultForwardFactory,
        RedirectFactory $redirectFactory
    ) {
        $this->ticketFactory = $orderFactory;
        $this->ticketRepository = $ticketRepository;
        $this->ticketAuthorization = $ticketAuthorization;
        $this->registry = $registry;
        $this->messageManager = $context->getMessageManager();
        $this->url = $url;
        $this->resultForwardFactory = $resultForwardFactory;
        $this->redirectFactory = $redirectFactory;
    }

    /**
     * load ticket by it id
     *
     * @param RequestInterface $request
     * @return bool|Forward|Redirect
     */
    public function load(RequestInterface $request)
    {
        $ticketId = (int)$request->getParam('ticket_id');
        if (!$ticketId) {
            /** @var Forward $resultForward */
            $resultForward = $this->resultForwardFactory->create();
            return $resultForward->forward('noroute');
        }
        /** @var Redirect $resultRedirect */
        $resultRedirect = $this->redirectFactory->create();
        try {
            $ticket = $this->ticketRepository->getById($ticketId);
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage(__('This Ticket is not longer exist'));
            return $resultRedirect->setUrl($this->url->getUrl('ticket/'));
        }
        if ($this->ticketAuthorization->canView($ticket)) {
            return true;
        }
        $this->messageManager->addErrorMessage(__('You do not have access of this ticket'));
        return $resultRedirect->setUrl($this->url->getUrl('ticket/'));
    }
}
