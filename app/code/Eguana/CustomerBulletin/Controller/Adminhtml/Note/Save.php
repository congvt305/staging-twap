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

namespace Eguana\CustomerBulletin\Controller\Adminhtml\Note;

use Eguana\CustomerBulletin\Api\NoteRepositoryInterface;
use Eguana\CustomerBulletin\Api\TicketRepositoryInterface;
use Eguana\CustomerBulletin\Helper\Data;
use Eguana\CustomerBulletin\Model\Email\EmailSender;
use Eguana\CustomerBulletin\Model\NoteFactory;
use Eguana\CustomerBulletin\Model\TicketFactory;
use Magento\Backend\App\Action;
use Magento\Backend\Model\Auth\Session as AdminSession;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\Request\Http;
use Magento\Framework\Data\Form\FormKey\Validator;
use Magento\Framework\Filesystem;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\View\Result\Layout;
use Magento\Framework\Message\ManagerInterface;
use Magento\MediaStorage\Model\File\UploaderFactory;
use Magento\User\Model\UserFactory;
use Psr\Log\LoggerInterface;

/**
 * getCustomerId That save the massges in database
 *
 * Class Save
 */
class Save extends Action
{
    /**#@+*/
    const CUSTOMER_EMAIL_TEMPLATE = 'ticket_managment/email/customer_email_reply';
    const ADMIN_EMAIL_TEMPLATE = 'ticket_managment/email/staff_email_reply';
    /**#@-*/
    /**
     * @var EmailSender
     */
    private $emailSender;

    /**
     * @var TicketFactory
     */
    private $ticketFactory;

    /**
     * @var TicketRepositoryInterface
     */
    private $ticketRepository;

    /**
     * @var UserFactory
     */
    private $userFactory;

    /**
     * @var Data
     */
    private $helperData;

    /**
     * @var Validator
     */
    private $formKeyValidator;

    /**
     * @var NoteRepositoryInterface
     */
    private $noteRepository;

    /**
     * @var NoteFactory
     */
    private $noteFactory;

    /**
     * @var UploaderFactory
     */
    private $uploader;

    /**
     * @var Filesystem
     */
    private $filesystem;

    /**
     * @var Http
     */
    private $request;

    /**
     * @var ManagerInterface
     */
    protected $messageManager;

    /**
     * @var AdminSession
     */
    private $adminSession;

    /**
     * @var SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * Save constructor.
     * @param Action\Context $context
     * @param EmailSender $emailSender
     * @param TicketFactory $ticketFactory
     * @param UploaderFactory $uploader
     * @param UserFactory $userFactory
     * @param Data $helperData
     * @param Filesystem $filesystem
     * @param TicketRepositoryInterface $ticketRepository
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param NoteFactory $noteFactory
     * @param NoteRepositoryInterface $noteRepository
     * @param Validator $formKeyValidator
     * @param AdminSession $adminSession
     * @param Http $request
     * @param LoggerInterface $logger
     */
    public function __construct(
        Action\Context $context,
        EmailSender $emailSender,
        TicketFactory $ticketFactory,
        UploaderFactory $uploader,
        UserFactory $userFactory,
        Data $helperData,
        Filesystem $filesystem,
        TicketRepositoryInterface $ticketRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        NoteFactory $noteFactory,
        NoteRepositoryInterface $noteRepository,
        Validator $formKeyValidator,
        AdminSession $adminSession,
        Http $request,
        LoggerInterface $logger
    ) {
        $this->formKeyValidator  = $formKeyValidator;
        $this->emailSender          = $emailSender;
        $this->ticketFactory     = $ticketFactory;
        $this->userFactory = $userFactory;
        $this->helperData = $helperData;
        $this->ticketRepository = $ticketRepository;
        $this->noteRepository  = $noteRepository;
        $this->uploader          = $uploader;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->filesystem        = $filesystem;
        $this->noteFactory     = $noteFactory;
        $this->request     = $request;
        $this->adminSession = $adminSession;
        $this->logger = $logger;
        parent::__construct($context);
    }

    /**
     * Svae Message action
     * That class get the post valus of add message form
     * and save that values in to the database
     * using model and data repository and also upload file
     *
     * @return ResponseInterface|ResultInterface|Layout
     */
    public function execute()
    {
        $notification = '';
        $userId = $this->adminSession->getUser()->getId();
        $files = $this->getRequest()->getFiles();
        $post = (array) $this->request->getPostValue();
        if (!empty($post)) {
            $form_data = explode('&', $post['formData']);
            $message = $post['msg'];
            $ticket_id = explode('=', $form_data[2]);
            $model = $this->noteFactory->create();
            $model->setData('note_message', $message);
            $model->setData('ticket_id', $ticket_id[1]);
            $model->setData('user_type', '1');
            $model->setData('user_id', $userId);
            $fileName = '';
            if (isset($files) && !empty($files)) {
                try {
                    $fileName = $this->uploadFilesOfNote($files);
                } catch (\Exception $e) {
                    $this->logger->error($e->getMessage());
                }
                if ($fileName != false) {
                    $model->setData('note_attachment', $fileName);
                }
            }
            try {
                $this->noteRepository->save($model);
                $this->changeReadStatus($ticket_id[1]);
                $ticketStoreId = $this->getTicketStoreId($ticket_id[1]);
                if ($this->getCustomerEmailEnableValue($ticketStoreId) == 1) {
                    $this->emailSender->sendEmailToCustomer(
                        $ticket_id[1],
                        self::CUSTOMER_EMAIL_TEMPLATE,
                        $this->getCustomerId($ticket_id[1])
                    );
                }
            } catch (\Exception $e) {
                $this->messageManager->addExceptionMessage(
                    $e,
                    __('Something went wrong while saving the ticket.')
                );
            }
        }
        return $notification;
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
        $model->setData('is_read_customer', '0');
        try {
            $this->ticketRepository->save($model);
        } catch (\Exception $e) {
            $this->logger->info($e->getMessage());
        }
    }

    /**
     * get Staff Email Enable value from configuration
     *
     * @return mixed
     */
    public function getStaffEmailEnableValue()
    {
        return $this->helperData->getGeneralConfig('email/staff_email_auto');
    }

    /**
     * get CustomerEmail Enable value from configuration
     *
     * @param $storeId
     * @return mixed
     */
    public function getCustomerEmailEnableValue($storeId)
    {
        return $this->helperData->getCustomerEmailEnableValue('email/customer_email_auto', $storeId);
    }

    /**
     * get ticket detail from ticketrepository
     *
     * @param $ticketId
     * @return string
     */
    public function getCustomerId($ticketId)
    {
        $ticket = '';
        try {
            $ticket = $this->ticketRepository->getById($ticketId);
            return $ticket->getCustomerId();
        } catch (\Exception $e) {
            $this->logger->info($e->getMessage());
        }
        return $ticket;
    }

    /**
     * upload note files
     *
     * @param $files
     * @return string
     * @throws \Exception
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

    /**
     * get ticket Store id from ticketrepository
     *
     * @param $ticketId
     * @return string
     */
    private function getTicketStoreId($ticketId)
    {
        $ticket = '';
        try {
            $ticket = $this->ticketRepository->getById($ticketId);
            return $ticket['store_id'];
        } catch (\Exception $e) {
            $this->logger->info($e->getMessage());
        }
        return $ticket;
    }
}
