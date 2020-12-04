<?php
/**
 * @author Eguana Team
 * @copyriht Copyright (c) 2020 Eguana {http://eguanacommerce.com}
 * Created by PhpStorm
 * User: bilalyounas
 * Date: 11/10/20
 * Time: 06:48 PM
 */

namespace Eguana\CustomerBulletin\ViewModel;

use Eguana\CustomerBulletin\Api\Data\TicketInterface;
use Eguana\CustomerBulletin\Api\NoteRepositoryInterface;
use Eguana\CustomerBulletin\Api\TicketRepositoryInterface;
use Eguana\CustomerBulletin\Helper\Data;
use Eguana\CustomerBulletin\Model\NoteRepositoryFactory;
use Eguana\CustomerBulletin\Model\ResourceModel\Note\CollectionFactory as NoteCollectionFactory;
use Eguana\CustomerBulletin\Model\ResourceModel\Ticket\CollectionFactory;
use Eguana\CustomerBulletin\Model\TicketFactory;
use Magento\Cms\Model\Template\FilterProvider;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Model\Session;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Api\SortOrderBuilder;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Framework\UrlInterface;
use Magento\Framework\View\Element\Block\ArgumentInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\User\Model\UserFactory;
use Psr\Log\LoggerInterface;

/**
 * Class Detail
 *  Eguana\CustomerBulletin\ViewModel
 */
class Detail implements ArgumentInterface
{
    /**#@+
     * Constants for icon path
     */
    const MESSAGE_ICON = 'Eguana_CustomerBulletin::images/email.svg';
    const FILE_ATTACHEMNT_ICON_STW = 'Eguana_CustomerBulletin::images/s-paper-clip.svg';
    const FILE_ATTACHEMNT_ICON = 'Eguana_CustomerBulletin::images/l-paper-clip.svg';
    const INFORMATION_ICON = 'Eguana_CustomerBulletin::images/information.svg';
    const TICKET_CLOSE = 'ticket/index/close/ticket_id/';
    /**#@-*/

    /**
     * @var Session
     */
    private $customerSession;

    /**
     * @var TicketFactory
     */
    private $ticketFactory;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var DateTime
     */
    private $date;

    /**
     * @var Data
     */
    private $helperData;

    /**
     * @var CollectionFactory
     */
    private $ticketCollectionFactory;

    /**
     * @var NoteRepositoryInterface
     */
    private $noteRepository;

    /**
     * @var NoteRepositoryFactory
     */
    private $noteRepositoryFactory;

    /**
     * @var TicketRepositoryInterface
     */
    private $ticketRepository;

    /**
     * @var SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    /**
     * @var SortOrderBuilder
     */
    private $sortOrderBuilder;

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
     * @var RequestInterface
     */
    private $request;

    /**
     * @var FilterProvider
     */
    private $filterProvider;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * Detail constructor.
     *
     * @param CollectionFactory $ticketCollectionFactory
     * @param TicketFactory $ticketFactory
     * @param Data $helperData
     * @param Session $customerSession
     * @param CustomerRepositoryInterface $customerRepository
     * @param StoreManagerInterface $storeManager
     * @param FilterProvider $filterProvider
     * @param NoteRepositoryFactory $noteRepositoryFactory
     * @param NoteRepositoryInterface $noteRepository
     * @param TicketRepositoryInterface $ticketRepository
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param SortOrderBuilder $sortOrderBuilder
     * @param DateTime $date
     * @param UserFactory $userFactory
     * @param RequestInterface $request
     * @param NoteCollectionFactory $noteCollectionFactory
     * @param LoggerInterface $logger
     */
    public function __construct(
        CollectionFactory $ticketCollectionFactory,
        TicketFactory $ticketFactory,
        Data $helperData,
        Session $customerSession,
        CustomerRepositoryInterface $customerRepository,
        StoreManagerInterface $storeManager,
        FilterProvider $filterProvider,
        NoteRepositoryFactory $noteRepositoryFactory,
        NoteRepositoryInterface $noteRepository,
        TicketRepositoryInterface $ticketRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        SortOrderBuilder $sortOrderBuilder,
        DateTime $date,
        UserFactory $userFactory,
        RequestInterface $request,
        NoteCollectionFactory $noteCollectionFactory,
        LoggerInterface $logger
    ) {
        $this->ticketCollectionFactory = $ticketCollectionFactory;
        $this->helperData = $helperData;
        $this->ticketFactory     = $ticketFactory;
        $this->customerSession = $customerSession;
        $this->noteRepositoryFactory = $noteRepositoryFactory;
        $this->date = $date;
        $this->storeManager = $storeManager;
        $this->noteCollectionFactory = $noteCollectionFactory;
        $this->userFactory = $userFactory;
        $this->customerRepository = $customerRepository;
        $this->noteRepository = $noteRepository;
        $this->ticketRepository = $ticketRepository;
        $this->logger = $logger;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->sortOrderBuilder = $sortOrderBuilder;
        $this->request = $request;
        $this->filterProvider = $filterProvider;
        $this->logger = $logger;
    }
    /**
     * get note of Subject
     *
     * @return mixed
     */
    public function getTicketSubject()
    {
        $subject = '';
        $collection = $this->getTicketCollection();
        foreach ($collection as $ticket) {
            $subject = $ticket->getSubject();
        }
        return $subject;
    }

    /**
     * get collection of ticket
     *
     * @return mixed
     */
    public function getTicketCollection()
    {
        $ticket = '';
        $searchrecorde = $this->searchCriteriaBuilder
            ->addFilter('ticket_id', $this->request->getParam('ticket_id'), 'eq')->create();
        try {
            $ticket = $this->ticketRepository->getList($searchrecorde)->getItems();
        } catch (\Exception $e) {
            $this->logger->info($e->getMessage());
        }
        return $ticket;
    }

    /**
     * get Detail of ticket fronm repository using getById method
     *
     * @return TicketInterface|string
     */
    public function getTicketDetail()
    {
        $ticket = '';
        try {
            $ticket = $this->ticketRepository->getById($this->request->getParam('ticket_id'));
            return $ticket;
        } catch (\Exception $e) {
            $this->logger->info($e->getMessage());
        }
        return $ticket;
    }

    /**
     * get collection of note
     *
     * @return array
     */
    public function getNoteCollection()
    {
        $noteCollection = $this->noteCollectionFactory->create();
        $noteCollection->addFieldToFilter('ticket_id', [ 'eq' => [$this->request->getParam('ticket_id')]])
            ->setOrder('creation_time', 'DESC');
        return $noteCollection->getData();
    }

    /**
     * change name of file
     *
     * @param $filename
     * @return string
     */
    public function changeFileName($filename)
    {
        $fileNameWithExtension = explode('.', $filename);
        $nameLength = count($fileNameWithExtension);
        $extension = $fileNameWithExtension[$nameLength - 1];
        if (strlen($extension) > 3) {
            $extension = 'png';
        }
        $name = explode('/', $fileNameWithExtension[0]);
        if (strlen($name[4]) > 12) {
            $fullFileName = substr($name[4], 0, 11) . '...';
        } else {
            $fullFileName = $name[4] . '.' . $extension;
        }
        return $fullFileName;
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
     * get ticket id from get request
     *
     * @return mixed
     */
    public function getTicketId()
    {
        return $this->request->getParam('ticket_id');
    }

    /**
     * get label of status by using its value
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
     * get date from creation date time
     *
     * @param $dateTime
     * @return string
     */
    public function getCreationDate($dateTime)
    {
        return $this->date->date('Y-m-d', strtotime($dateTime));
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
     * get file url for downloading
     *
     * @param $file
     * @return string
     */
    public function mediaUrl($file)
    {
        $mediaUrl = '';
        try {
            $mediaUrl = $this->storeManager->getStore()->getBaseUrl(UrlInterface::URL_TYPE_MEDIA);
            return $mediaUrl . $file;
        } catch (\Exception $exception) {
            $this->logger->debug($exception->getMessage());
        }
        return $mediaUrl;
    }

    /**
     * change the name of files
     *
     * @param $files
     * @return false|string[]
     */
    public function getFiles($files)
    {
        return explode(',', $files);
    }

    /**
     * get customer name from configuration
     *
     * @return string
     */
    public function getAdminName()
    {
        $name = $this->helperData->getGeneralConfig('configuration/admin_name');
        if (isset($name)) {
            return '(' . $name . ')';
        }
        return $name;
    }

    /**
     * Get Ticket close URL of controller
     *
     * @param $ticketId
     * @return string
     */
    public function getTicketCloseAction($ticketId) : string
    {
        $baseUrl = '';
        try {
            $baseUrl = $this->storeManager->getStore()->getBaseUrl();
        } catch (\Exception $e) {
            $this->logger->error($e->getMessage());
        }
        return ($baseUrl . self::TICKET_CLOSE . $ticketId);
    }

    /**
     * get Url of information icon
     *
     * @return string
     */
    public function getInformationIconUrl()
    {
        return self::INFORMATION_ICON;
    }

    /**
     * get Url of message icon
     *
     * @return string
     */
    public function getMessageIconUrl()
    {
        return self::MESSAGE_ICON;
    }

    /**
     * get Notification if customer hit worng url
     *
     * @param $ticketId
     * @return string
     */
    public function getNotification($ticketId)
    {
        return (__('Sorry There is no Ticket with Id ') . $ticketId . __(' Against Your Account'));
    }

    /**
     * Change the reade status for Customer
     */
    public function changeReadStatus()
    {
        $model = $this->ticketFactory->create();
        $model->load($this->request->getParam('ticket_id'));
        $model->setData('is_read_customer', '1');
        try {
            $this->ticketRepository->save($model);
        } catch (\Exception $e) {
            $this->logger->info($e->getMessage());
        }
    }

    /**
     * get Url of fiel attachment icon
     *
     * @return string
     */
    public function getFileIconSwtUrl() : string
    {
        return self::FILE_ATTACHEMNT_ICON_STW;
    }

    /**
     * get Url of fiel attachment icon
     *
     * @return string
     */
    public function getFileIconUrl() : string
    {
        return self::FILE_ATTACHEMNT_ICON;
    }

    /**
     * get website code
     *
     * @return string
     */
    public function getWebsiteCode()
    {
        $websiteCode = "";
        try {
            $websiteCode = $this->storeManager->getWebsite()->getCode();
            return $websiteCode;
        } catch (\Exception $e) {
            $this->logger->error($e->getMessage());
        }
        return $websiteCode;
    }
}
