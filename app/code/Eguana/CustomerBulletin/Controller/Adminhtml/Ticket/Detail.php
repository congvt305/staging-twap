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

use Eguana\CustomerBulletin\Api\NoteRepositoryInterface;
use Eguana\CustomerBulletin\Api\TicketRepositoryInterface;
use Eguana\CustomerBulletin\Model\NoteFactory;
use Eguana\CustomerBulletin\Model\TicketFactory;
use Magento\Backend\Model\Auth\Session as AdminSession;
use Magento\Customer\Model\Session;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Data\Form\FormKey\Validator;
use Magento\Framework\Filesystem;
use Magento\Framework\Image\AdapterFactory;
use Magento\Framework\View\Result\Page;
use Magento\Framework\View\Result\PageFactory;
use Magento\MediaStorage\Model\File\UploaderFactory;
use Psr\Log\LoggerInterface;

/**
 * This class is used for view ticket deatils
 *
 * Class Detail
 */
class Detail extends Action implements HttpGetActionInterface
{
    /**
     * @var RequestInterface
     */
    private $requestInterface;

    /**
     * @var TicketFactory
     */
    private $ticketFactory;

    /**
     * @var TicketRepositoryInterface
     */
    private $ticketRepository;

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
     * @var
     */
    private $adapterFactory;
    /**
     * @var
     */
    private $uploader;
    /**
     * @var
     */
    private $filesystem;

    /**
     * @var Session
     */
    private $customerSession;
    /**
     * @var AdminSession
     */
    private $adminSession;

    /**
     * @var PageFactory
     */
    private $pageFactory;
    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * Detail constructor.
     *
     * @param Context $context
     * @param TicketFactory $ticketFactory
     * @param RequestInterface $requestInterface
     * @param TicketRepositoryInterface $ticketRepository
     * @param LoggerInterface $logger
     * @param AdminSession $adminSession
     * @param UploaderFactory $uploader
     * @param Filesystem $filesystem
     * @param AdapterFactory $adapterFactory
     * @param Session $customerSession
     * @param NoteFactory $noteFactory
     * @param NoteRepositoryInterface $noteRepository
     * @param Validator $formKeyValidator
     * @param PageFactory $pageFactory
     */
    public function __construct(
        Context $context,
        TicketFactory $ticketFactory,
        RequestInterface $requestInterface,
        TicketRepositoryInterface $ticketRepository,
        LoggerInterface $logger,
        AdminSession $adminSession,
        UploaderFactory $uploader,
        Filesystem $filesystem,
        AdapterFactory $adapterFactory,
        Session $customerSession,
        NoteFactory $noteFactory,
        NoteRepositoryInterface $noteRepository,
        Validator $formKeyValidator,
        PageFactory $pageFactory
    ) {
        $this->formKeyValidator = $formKeyValidator;
        $this->requestInterface = $requestInterface;
        $this->ticketFactory     = $ticketFactory;
        $this->ticketRepository = $ticketRepository;
        $this->logger = $logger;
        $this->adminSession = $adminSession;
        $this->customerSession = $customerSession;
        $this->noteRepository = $noteRepository;
        $this->uploader = $uploader;
        $this->adapterFactory = $adapterFactory;
        $this->filesystem = $filesystem;
        $this->noteFactory = $noteFactory;
        $this->pageFactory = $pageFactory;
        parent::__construct($context);
    }
    /**
     * Booking action
     * That class get the post valus of add ticket form
     * and save that values in to the database
     * using model and data repository and also upload file
     *
     * @return ResponseInterface|ResultInterface|Page
     */
    public function execute()
    {
        $ticketId = '';
        try {
            $userId = $this->adminSession->getUser()->getId();
            $ticketId = $this->getRequest()->getParam('ticket_id');
            $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
            $post = (array) $this->getRequest()->getPost();
            $files = $this->getRequest()->getFiles();
            if (!empty($post) && $this->formKeyValidator->validate($this->getRequest())) {
                try {
                    $model = $this->noteFactory->create();
                    $model->setData('note_message', $post['note']);
                    $model->setData('ticket_id', $post['ticket_id']);
                    $model->setData('user_type', '1');
                    $model->setData('user_id', $userId);
                    if (isset($files) && !empty($files)) {
                        $fileName = $this->uploadFilesOfNote($files);
                        if ($fileName != false) {
                            $model->setData('note_attachment', $fileName);
                        }
                    }
                    $this->noteRepository->save($model);
                    $this->changeReadStatus($post['ticket_id']);
                } catch (\Exception $e) {
                    $this->messageManager->addExceptionMessage(
                        $e,
                        __('Something went wrong while saving the ticket.')
                    );
                }
                return $resultRedirect->setUrl($this->_redirect->getRefererUrl());
            }
            $this->changeReadStatusForAdmin();
        } catch (\Exception $e) {
            $this->logger->info($e->getMessage());
        }
        $pageFactory =$this->pageFactory->create();
        $pageFactory->getConfig()->getTitle()->prepend(__('Ticket #%1', $ticketId));
        return  $pageFactory;
    }

    /**
     * Change the reade status for customer
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
     * Change the reade status for admin
     */
    public function changeReadStatusForAdmin()
    {
        $model=$this->ticketFactory->create();
        $model->load($this->requestInterface->getParam('ticket_id'));
        $model->setData('is_read_admin', '1');
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
