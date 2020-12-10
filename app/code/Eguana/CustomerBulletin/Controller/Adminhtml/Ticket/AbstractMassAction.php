<?php
/**
 * @author Eguana Team
 * @copyriht Copyright (c) 2020 Eguana {http://eguanacommerce.com}
 * Created by PhpStorm
 * User: bilalyounas
 * Date: 30/9/20
 * Time: 06:48 PM
 */
declare(strict_types=1);

namespace Eguana\CustomerBulletin\Controller\Adminhtml\Ticket;

use Eguana\CustomerBulletin\Api\TicketRepositoryInterface;
use Eguana\CustomerBulletin\Helper\Data;
use Eguana\CustomerBulletin\Controller\Adminhtml\AbstractController;
use Eguana\CustomerBulletin\Model\ResourceModel\Ticket\CollectionFactory;
use Eguana\CustomerBulletin\Model\Ticket;
use Magento\Backend\App\Action\Context;
use Eguana\CustomerBulletin\Model\Email\EmailSender;
use Magento\Framework\Registry;
use Magento\Backend\Model\View\Result\ForwardFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\View\Result\PageFactory;
use Magento\Ui\Component\MassAction\Filter;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\Controller\ResultInterface;

/**
 * Abstract Class AbstractMassAction
 */
abstract class AbstractMassAction extends AbstractController
{
    /**
     * @var Filter
     */
    private $filter;

    /**
     * @var Data
     */
    protected $helperData;

    /**
     * @var CollectionFactory
     */
    private $collectionFactory;

    /**
     * Core registry
     *
     * @var Registry
     */
    protected $coreRegistry;

    /**
     * @var TicketRepositoryInterface
     */
    protected $ticketRepository;

    /**
     * @var ForwardFactory
     */
    protected $resultForwardFactory;

    /**
     * @var string
     */
    private $successMessage;

    /**
     * @var string
     */
    private $errorMessage;

    /**
     * @var EmailSender
     */
    protected $emailSender;

    /**
     * AbstractMassAction constructor.
     * @param $errorMessage
     * @param $successMessage
     * @param Filter $filter
     * @param Registry $registry
     * @param EmailSender $emailSender
     * @param Data $helperData
     * @param Context $context
     * @param PageFactory $resultPageFactory
     * @param ForwardFactory $resultForwardFactory
     * @param CollectionFactory $collectionFactory
     * @param TicketRepositoryInterface $ticketRepository
     */
    public function __construct(
        $errorMessage,
        $successMessage,
        Filter $filter,
        Registry $registry,
        EmailSender $emailSender,
        Data $helperData,
        Context $context,
        PageFactory $resultPageFactory,
        ForwardFactory $resultForwardFactory,
        CollectionFactory $collectionFactory,
        TicketRepositoryInterface $ticketRepository
    ) {
        $this->filter               = $filter;
        $this->helperData           = $helperData;
        $this->emailSender          = $emailSender;
        $this->errorMessage         = $errorMessage;
        $this->successMessage       = $successMessage;
        $this->ticketRepository      = $ticketRepository;
        $this->collectionFactory    = $collectionFactory;
        $this->resultForwardFactory = $resultForwardFactory;
        parent::__construct(
            $resultPageFactory,
            $resultForwardFactory,
            $context,
            $registry,
            $ticketRepository
        );
    }

    /**
     * @param Ticket $data
     * @return mixed
     */
    abstract protected function massAction(Ticket $data);

    /**
     * AbstractMassAction execute method
     *
     * @return ResponseInterface|Redirect|ResultInterface
     */
    public function execute()
    {
        try {
            $collection = $this->filter->getCollection($this->collectionFactory->create());
            $collectionSize = $collection->getSize();
            foreach ($collection as $data) {
                $this->massAction($data);
            }
            $this->messageManager->addSuccessMessage(__($this->successMessage, $collectionSize));
        } catch (LocalizedException $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
        } catch (\Exception $e) {
            $this->messageManager->addExceptionMessage($e, __($this->errorMessage));
        }

        $redirectResult = $this->resultRedirectFactory->create();
        $redirectResult->setPath('*/*/');

        return $redirectResult;
    }
}
