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
use Eguana\CustomerBulletin\Model\Email\EmailSender;
use Magento\Framework\App\ResponseInterface;
use Eguana\CustomerBulletin\Helper\Data;
use Eguana\CustomerBulletin\Model\TicketFactory;
use Magento\Customer\Model\Session;
use Magento\Framework\View\Result\Page;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\View\Result\Layout;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Controller\Result\RedirectFactory;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Data\Form\FormKey\Validator;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Filesystem;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\Event\ManagerInterface as ManagerInterfaceAlias;
use Magento\Framework\View\Result\PageFactory;
use Magento\MediaStorage\Model\File\UploaderFactory;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Store\Model\StoreManagerInterface;
use Psr\Log\LoggerInterface;

/**
 * Create ticket from frontend
 *
 * Class CreateTicket
 */
class CreateTicket extends Action
{
    /**#@+*/
    const CUSTOMER_EMAIL_TEMPLATE = 'ticket_managment/email/customer_email_open';
    const ADMIN_EMAIL_TEMPLATE = 'ticket_managment/email/staff_email_open';
    /**#@-*/

    /**
     * @var EmailSender
     */
    private $emailSender;

    /**
     * @var RedirectFactory
     */
    private $redirectFactory;

    /**
     * @var PageFactory
     */
    private $pageFactory;

    /**
     * @var Validator
     */
    private $formKeyValidator;

    /**
     * @var TicketRepositoryInterface
     */
    private $ticketRepository;

    /**
     * @var TicketFactory
     */
    private $ticketFactory;

    /**
     * @var UploaderFactory
     */
    private $uploader;

    /**
     * @var Filesystem
     */
    private $filesystem;

    /**
     * @var Session
     */
    private $customerSession;

    /**
     * @var Data
     */
    private $helperData;

    /**
     * @var ManagerInterface
     */
    protected $messageManager;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var ManagerInterfaceAlias
     */
    private $eventManager;

    /**
     * CreateTicket constructor.
     * @param Context $context
     * @param RedirectFactory $redirectFactory
     * @param Data $helperData
     * @param UploaderFactory $uploader
     * @param Filesystem $filesystem
     * @param EmailSender $emailSender
     * @param TicketRepositoryInterface $ticketRepository
     * @param Session $customerSession
     * @param TicketFactory $ticketFactory
     * @param Validator $formKeyValidator
     * @param PageFactory $pageFactory
     * @param StoreManagerInterface $storeManager
     * @param LoggerInterface $logger
     */
    public function __construct(
        Context $context,
        RedirectFactory $redirectFactory,
        Data $helperData,
        UploaderFactory $uploader,
        Filesystem $filesystem,
        EmailSender $emailSender,
        TicketRepositoryInterface $ticketRepository,
        Session $customerSession,
        TicketFactory $ticketFactory,
        Validator $formKeyValidator,
        PageFactory $pageFactory,
        StoreManagerInterface $storeManager,
        LoggerInterface $logger,
        ManagerInterfaceAlias $eventManager
    ) {
        $this->formKeyValidator  = $formKeyValidator;
        $this->ticketRepository  = $ticketRepository;
        $this->emailSender          = $emailSender;
        $this->redirectFactory = $redirectFactory;
        $this->customerSession   = $customerSession;
        $this->helperData = $helperData;
        $this->uploader          = $uploader;
        $this->filesystem        = $filesystem;
        $this->pageFactory     = $pageFactory;
        $this->ticketFactory     = $ticketFactory;
        $this->storeManager = $storeManager;
        $this->messageManager = $context->getMessageManager();
        $this->logger = $logger;
        $this->eventManager = $eventManager;
        parent::__construct($context);
    }
    /**
     * Booking action
     * That class get the post valus of add ticket form
     * and save that values in to the database
     * using model and data repository and also upload file
     *
     * @return ResponseInterface|Redirect|ResultInterface|Layout|Page
     */
    public function execute()
    {
        if ($this->getEnableValue()== 0) {
            return $this->redirectFactory->create()->setPath('/');
        }
        $customerId = $this->customerSession->getCustomer()->getId();
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        if (!$this->customerSession->isLoggedIn()) {
            $resultRedirect->setUrl('/customer/account/login');
            $this->messageManager->addErrorMessage('Please Login Yourself');
            return $resultRedirect;
        }
        $post = (array) $this->getRequest()->getPost();
        if (!empty($post) && $this->formKeyValidator->validate($this->getRequest())) {
            try {
                $model = $this->ticketFactory->create();
                $files = $this->getRequest()->getFiles();
                $storeId = $this->storeManager->getStore()->getId();
                $model->setData('store_id', $storeId);
                $model->setData('subject', $post['subject']);
                $model->setData('category', $post['category']);
                $model->setData('message', $post['message']);
                $model->setData('customer_id', $customerId);
                if (isset($files) && !empty($files)) {
                    $fileName = $this->uploadFilesOfNote($files);
                    if ($fileName != false) {
                        $model->setData('attachment', $fileName);
                    }
                }
                try {
                    $this->ticketRepository->save($model);
                    $this->changeReadStatus($model->getData('ticket_id'));
                    $ticketID = $model->getData('ticket_id');
                    $this->messageManager->addSuccessMessage(__("Your Ticket has been created!"));
                    $this->eventManager->dispatch(
                        "gcrm_customer_bulletin_data_export",
                        [
                            'ticketID' => $ticketID
                        ]
                    );
                } catch (\Exception $e) {
                    $this->messageManager->addExceptionMessage(
                        $e,
                        __("Something went wrong while saving the ticket.")
                    );
                }
                $resultRedirect->setUrl('/ticket/');
                return $resultRedirect;
            } catch (\Exception $e) {
                $this->logger->error($e->getMessage());
            }
        }
        return  $this->pageFactory->create();
    }

    /**
     * Change the reade status for admin
     *
     * @param $ticket_id
     */
    public function changeReadStatus($ticket_id)
    {
        $model=$this->ticketFactory->create();
        $model->load($ticket_id);
        $model->setData('is_read_admin', '0');
        try {
            $this->ticketRepository->save($model);
        } catch (\Exception $e) {
            $this->logger->info($e->getMessage());
        }
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

    /**
     * upload Ticket files
     *
     * @param $files
     * @return string
     */

    public function uploadFilesOfNote($files)
    {
        $fileName = '';
        try {
            $i = 0;
            foreach ($files as $value) {
                foreach ($value as $key => $file) {
                    if (isset($file) && isset($file['name']) && strlen($file['name'])) {
                        $uploader = $this->uploader->create(
                            ['fileId' => $file]
                        );
                        $uploader->setAllowRenameFiles(true);
                        $uploader->setFilesDispersion(true);
                        $mediaDirectory = $this->filesystem->getDirectoryRead(DirectoryList::MEDIA);
                        $result = $uploader->save(
                            $mediaDirectory->getAbsolutePath('Ticket/files')
                        );
                        if ($i == 0) {
                            $fileName = 'Ticket/files' . $result['file'];
                        } else {
                            $fileName = $fileName . ',Ticket/files' . $result['file'];
                        }
                    }
                    $i++;
                }
                break;
            }
            if (!isset($fileName)) {
                return false;
            } else {
                return $fileName;
            }
        } catch (\Exception $e) {
            $this->logger->info($e->getMessage());
        }
        return $fileName;
    }
}
