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

namespace Eguana\CustomerBulletin\Controller\Note;

use Eguana\CustomerBulletin\Api\NoteRepositoryInterface;
use Eguana\CustomerBulletin\Model\Email\EmailSender;
use Eguana\CustomerBulletin\Api\TicketRepositoryInterface;
use Eguana\CustomerBulletin\Helper\Data;
use Eguana\CustomerBulletin\Model\NoteFactory;
use Eguana\CustomerBulletin\Model\TicketFactory;
use Magento\Customer\Model\Session;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\Request\Http;
use Magento\Framework\Data\Form\FormKey\Validator;
use Magento\Framework\Filesystem;
use Magento\Framework\Image\AdapterFactory;
use Magento\Framework\Message\ManagerInterface;
use Magento\MediaStorage\Model\File\UploaderFactory;
use Magento\User\Model\UserFactory;
use Psr\Log\LoggerInterface;

/**
 * That save the massges in database
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
     * @var AdapterFactory
     */
    private $adapterFactory;

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
     * @var Http
     */
    private $request;

    /**
     * @var ManagerInterface
     */
    protected $messageManager;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * Save constructor.
     * @param Context $context
     * @param TicketFactory $ticketFactory
     * @param EmailSender $emailSender
     * @param UploaderFactory $uploader
     * @param UserFactory $userFactory
     * @param Data $helperData
     * @param Filesystem $filesystem
     * @param AdapterFactory $adapterFactory
     * @param TicketRepositoryInterface $ticketRepository
     * @param Session $customerSession
     * @param NoteFactory $noteFactory
     * @param NoteRepositoryInterface $noteRepository
     * @param Validator $formKeyValidator
     * @param Http $request
     * @param LoggerInterface $logger
     */
    public function __construct(
        Context $context,
        TicketFactory $ticketFactory,
        EmailSender $emailSender,
        UploaderFactory $uploader,
        UserFactory $userFactory,
        Data $helperData,
        Filesystem $filesystem,
        AdapterFactory $adapterFactory,
        TicketRepositoryInterface $ticketRepository,
        Session $customerSession,
        NoteFactory $noteFactory,
        NoteRepositoryInterface $noteRepository,
        Validator $formKeyValidator,
        Http $request,
        LoggerInterface $logger
    ) {
        $this->formKeyValidator  = $formKeyValidator;
        $this->emailSender          = $emailSender;
        $this->ticketFactory     = $ticketFactory;
        $this->userFactory = $userFactory;
        $this->helperData = $helperData;
        $this->customerSession   = $customerSession;
        $this->ticketRepository = $ticketRepository;
        $this->noteRepository  = $noteRepository;
        $this->uploader          = $uploader;
        $this->adapterFactory    = $adapterFactory;
        $this->filesystem        = $filesystem;
        $this->noteFactory     = $noteFactory;
        $this->messageManager = $context->getMessageManager();
        $this->request     = $request;
        $this->logger = $logger;
        parent::__construct($context);
    }

    /**
     * Svae Message action
     * That class get the post valus of add message form
     * and save that values in to the database
     * using model and data repository and also upload file
     *
     * @return \Magento\Framework\Controller\ResultInterface|\Magento\Framework\View\Result\Layout
     */
    public function execute()
    {
        $notification = '';
        $customerId = $this->customerSession->getCustomer()->getId();
        $files = $this->getRequest()->getFiles();
        $post = (array) $this->request->getPostValue();
        if (!empty($post)) {
            $form_data = explode('&', $post['formData']);
            $message = $post['message'];
            $ticket_id = explode('=', $form_data[2]);
            $model = $this->noteFactory->create();
            $model->setData('note_message', $message);
            $model->setData('ticket_id', $ticket_id[1]);
            $model->setData('user_type', '0');
            $model->setData('user_id', $customerId);
            try {
                if (isset($files) && !empty($files)) {
                    $fileName = $this->uploadFilesOfNote($files);
                    if ($fileName != false) {
                        $model->setData('note_attachment', $fileName);
                    }
                }
                $this->noteRepository->save($model);
                $this->changeReadStatus($ticket_id[1]);
            } catch (\Exception $e) {
                $this->messageManager->addExceptionMessage(
                    $e,
                    __('Something went wrong while saving the note.')
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
        $model->setData('is_read_admin', '0');
        try {
            $this->ticketRepository->save($model);
        } catch (\Exception $e) {
            $this->logger->info($e->getMessage());
        }
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
}
