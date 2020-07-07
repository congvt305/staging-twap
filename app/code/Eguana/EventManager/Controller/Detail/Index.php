<?php
/**
 * @author Eguana Team
 * @copyriht Copyright (c) 2020 Eguana {http://eguanacommerce.com}
 * Created by PhpStorm
 * User: arslan
 * Date: 30/6/20
 * Time: 8:31 PM
 */
namespace Eguana\EventManager\Controller\Detail;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\ResponseInterface as ResponseInterfaceAlias;
use Magento\Framework\Controller\ResultInterface as ResultInterfaceAlias;
use Magento\Framework\View\Result\PageFactory;
use Eguana\EventManager\Api\EventManagerRepositoryInterface;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\Controller\ResultFactory;

/**
 * Class Index
 * Controller to display details about the Event
 */
class Index extends Action
{
    /**
     * @var PageFactory
     */
    private $resultPageFactory;

    /**
     * @var EventManagerRepositoryInterface
     */
    private $eventManagerRepository;

    /**
     * @var ResultFactory
     */
    private $result;

    /**
     * @var ManagerInterface
     */
    private $managerInterface;

    /**
     * Construct
     *
     * @param Context $context
     * @param View  $eventManager
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        EventManagerRepositoryInterface $eventManagerRepository,
        ResultFactory $result,
        ManagerInterface $managerInterface
    ) {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
        $this->eventManagerRepository = $eventManagerRepository;
        $this->result = $result;
        $this->managerInterface = $managerInterface;
    }

    /**
     * Dispatch request
     * @return ResponseInterfaceAlias|ResultInterfaceAlias|void
     */
    public function execute()
    {
        $eventId = $this->_request->getParam('id');
        if (isset($eventId)) {
            $event = $this->eventManagerRepository->getById($eventId);
            if (empty($event->getData())) {
                $this->managerInterface->addErrorMessage(__('No event exsit with ' .$eventId . ' id'));
                $resultRedirect = $this->result->create(ResultFactory::TYPE_REDIRECT);
                $resultRedirect->setUrl('/events');
                return $resultRedirect;
            }
        } elseif (!isset($eventId)) {
            $resultRedirect = $this->result->create(ResultFactory::TYPE_REDIRECT);
            $resultRedirect->setUrl('/events');
            return $resultRedirect;
        }
        return $this->resultPageFactory->create();
    }
}
