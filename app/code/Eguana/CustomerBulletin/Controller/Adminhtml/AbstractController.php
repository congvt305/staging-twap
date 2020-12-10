<?php
/**
 * @author Eguana Team
 * @copyriht Copyright (c) 2020 Eguana {http://eguanacommerce.com}
 * Created by PhpStorm
 * User: bilalyounas
 * Date: 30/9/20
 * Time: 06:48 PM
 */
namespace Eguana\CustomerBulletin\Controller\Adminhtml;

use Eguana\CustomerBulletin\Api\TicketRepositoryInterface;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Registry;
use Magento\Framework\View\Result\PageFactory;
use Magento\Backend\Model\View\Result\ForwardFactory;

/**
 * Class AbstractVideo to be extended by other controllers
 */
abstract class AbstractController extends Action
{
    /**
     * authorization
     * @see _isAllowed()
     */
    const ADMIN_RESOURCE = 'Eguana_CustomerBulletin::ticket_manage';

    /**
     * @var TicketRepositoryInterface
     */
    protected $ticketRepository;

    /**
     * Core registry
     *
     * @var Registry
     */
    protected $coreRegistry;

    /**
     * Result Page Factory
     *
     * @var PageFactory
     */
    protected $resultPageFactory;

    /**
     * Result Forward Factory
     *
     * @var ForwardFactory
     */
    protected $resultForwardFactory;

    /**
     * AbstractController constructor.
     *
     * @param PageFactory $resultPageFactory
     * @param ForwardFactory $resultForwardFactory
     * @param Context $context
     * @param Registry $registry
     * @param TicketRepositoryInterface $ticketRepository
     */
    public function __construct(
        PageFactory $resultPageFactory,
        ForwardFactory $resultForwardFactory,
        Context $context,
        Registry $registry,
        TicketRepositoryInterface $ticketRepository
    ) {
        $this->coreRegistry         = $registry;
        $this->ticketRepository  = $ticketRepository;
        $this->resultPageFactory    = $resultPageFactory;
        $this->resultForwardFactory = $resultForwardFactory;
        parent::__construct($context);
    }
}
