<?php
/**
 * @author Eguana Team
 * @copyriht Copyright (c) 2020 Eguana {http://eguanacommerce.com}
 * Created by PhpStorm
 * User: bilalyounas
 * Date: 7/10/20
 * Time: 7:32 PM
 */
namespace Eguana\CustomerBulletin\Controller\Adminhtml\Ticket;

use Eguana\CustomerBulletin\Model\ResourceModel\Note\CollectionFactory as NoteCollectionFactory;
use Eguana\CustomerBulletin\Model\ResourceModel\Ticket\CollectionFactory;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Backend\Model\View\Result\Redirect;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\App\Response\Http\FileFactory;
use Magento\Framework\App\ResponseInterface as ResponseInterfaceAlias;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Controller\ResultInterface as ResultInterfaceAlias;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Directory\WriteInterface;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Ui\Component\MassAction\Filter;
use Magento\User\Model\UserFactory;
use Psr\Log\LoggerInterface;

/**
 * This class is used for Orders Reports
 *
 * Class MassReport
 */
class MassReport extends Action
{
    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var Filter
     */
    private $filter;

    /**
     * @var CollectionFactory
     */
    private $collectionFactory;

    /**
     * @var WriteInterface
     */
    private $directory;

    /**
     * @var FileFactory
     */
    private $fileFactory;

    /**
     * @var DateTime
     */
    private $date;

    /**
     * @var NoteCollectionFactory
     */
    private $noteCollectionFactory;

    /**
     * @var CustomerRepositoryInterface
     */
    private $customerRepository;

    /**
     * @var UserFactory
     */
    private $userFactory;

    /**
     * @var StoreManagerInterface
     */
    private $storeManagerInterface;

    /**
     * MassReport constructor.
     *
     * @param Context $context
     * @param Filter $filter
     * @param LoggerInterface $logger
     * @param DateTime $date
     * @param StoreManagerInterface $storeManagerInterface
     * @param UserFactory $userFactory
     * @param CustomerRepositoryInterface $customerRepository
     * @param FileFactory $fileFactory
     * @param Filesystem $filesystem
     * @param NoteCollectionFactory $noteCollectionFactory
     * @param CollectionFactory $collectionFactory
     * @throws \Magento\Framework\Exception\FileSystemException
     */
    public function __construct(
        Context $context,
        Filter $filter,
        LoggerInterface $logger,
        DateTime $date,
        StoreManagerInterface $storeManagerInterface,
        UserFactory $userFactory,
        CustomerRepositoryInterface $customerRepository,
        FileFactory $fileFactory,
        Filesystem $filesystem,
        NoteCollectionFactory $noteCollectionFactory,
        CollectionFactory $collectionFactory
    ) {
        $this->logger = $logger;
        $this->storeManagerInterface = $storeManagerInterface;
        $this->filter = $filter;
        $this->userFactory = $userFactory;
        $this->noteCollectionFactory = $noteCollectionFactory;
        $this->fileFactory = $fileFactory;
        $this->date = $date;
        $this->customerRepository = $customerRepository;
        $this->collectionFactory = $collectionFactory;
        $this->directory = $filesystem->getDirectoryWrite(DirectoryList::VAR_DIR);
        parent::__construct($context);
    }

    /**
     * Execute action to delete news
     *
     * @return Redirect|ResponseInterfaceAlias|ResultInterfaceAlias
     */
    public function execute()
    {
        $csvfilename = '';
        try {
            $collection = $this->filter->getCollection($this->collectionFactory->create());
            $name = date('m-d-Y-H-i-s');
            $filepath = 'export/export-data-' . $name . '.csv';
            $this->directory->create('export');
            $stream = $this->directory->openFile($filepath, 'w+');
            $stream->lock();
            $columns = ['ID','Customer name','Subject','Category','Store View','status',
                'created time','modified time','note status',' '];
            foreach ($columns as $column) {
                $header[] = $column;
            }
            $stream->writeCsv($header);
            foreach ($collection as $ticket) {
                $itemData = [];
                $itemData[] = $ticket->getData('ticket_id');
                $itemData[] = $this->getCustomerName($ticket->getData('customer_id'));
                $itemData[] = $ticket->getData('subject');
                $itemData[] = $ticket->getData('category');
                $itemData[] = $this->getStoreViewName($ticket->getData('store_id'));
                $itemData[] = $this->getStatus($ticket->getData('status'));
                $itemData[] = $this->getCreationTime($ticket->getData('creation_time'));
                $itemData[] = $this->getCreationTime($ticket->getData('update_time'));
                $itemData[] = 'Customer : ' . $this->getNoteStatus($ticket->getData('is_read_customer')) . "\r\n" .
                    'Admin : ' . $this->getNoteStatus($ticket->getData('is_read_admin'));
                $itemData[] = $this->getNoteCollection($ticket->getData('ticket_id'));

                $stream->writeCsv($itemData);
            }
            $content = [];
            $content['type'] = 'filename';
            $content['value'] = $filepath;
            $content['rm'] = '1';

            $csvfilename = 'Ticket-Report' . $name . '.csv';
            return $this->fileFactory->create($csvfilename, $content, DirectoryList::VAR_DIR);
        } catch (\Exception $e) {
            $this->logger->error($e->getMessage());
            /** @var Redirect $resultRedirect */
            $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
            return $resultRedirect->setPath('*/*/');
        }
        return $csvfilename;
    }

    /**
     * @param $store_id
     * @return string
     */
    private function getStoreViewName($store_id)
    {
        $storeName = '';
        try {
            $storeName = $this->storeManagerInterface->getStore($store_id)->getName();
        } catch (\Exception $e) {
            $this->logger->error($e->getMessage());
        }
        return $storeName;
    }

    /**
     * get collection of note
     *
     * @param $ticket_id
     * @return string
     */
    public function getNoteCollection($ticket_id)
    {
        $noteCollection = $this->noteCollectionFactory->create();
        $collection = $noteCollection->addFieldToFilter('ticket_id', [ 'eq' => [$ticket_id]]);
        $notes = '';
        foreach ($collection as $note) {
            $name = '';
            if ($note['user_type'] == 1) {
                $name = $this->getUserName($note['user_id']);
            } else {
                $name = $this->getCustomerName($note['user_id']);
            }
            if (empty($notes)) {
                $notes = $this->getCreationTime($note['creation_time']) . ' ' . $name . ' : ' . $note['note_message'];
            } else {
                $notes = $notes . "\r\n" . $this->getCreationTime($note['creation_time']) . ' ' . $name .
                    ' : ' . $note['note_message'];
            }
        }
        return $notes;
    }

    /**
     * get customer name from its id
     *
     * @param $customerId
     * @return string
     */
    public function getCustomerName($customerId)
    {
        $customer = '';
        try {
            $customer = $this->customerRepository->getById($customerId);
            return '(' . $customer->getFirstname() . ')';
        } catch (\Exception $e) {
            $this->logger->info($e->getMessage());
        }
        return $customer;
    }

    /**
     * get user name from its id
     *
     * @param $adminId
     * @return string
     */
    public function getUserName($adminId)
    {
        $user = $this->userFactory->create()->load($adminId);
        return '(' . $user->getFirstName() . ' ' . $user->getLastName() . ')';
    }

    /**
     * get time from creation date time
     *
     * @param $dateTime
     * @return string
     */
    public function getCreationTime($dateTime)
    {
        $time = $this->date->date('H:m A', strtotime($dateTime));
        $am = explode(" ", $time);
        return $dateTime . ' ' . $am[1];
    }

    /**
     * get status of Ticket by using its value
     *
     * @param $status
     * @return string
     */
    public function getStatus($status)
    {
        if ($status == 1) {
            return "Open";
        } elseif ($status == 0) {
            return "Close";
        } else {
            return "Hold";
        }
    }

    /**
     * get label of Note status by using its value
     *
     * @param $status
     * @return string
     */
    public function getNoteStatus($status) : string
    {
        if ($status == 1) {
            return "Read";
        } else {
            return "Unread";
        }
    }
}
