<?php
/**
 * @author Eguana Team
 * @copyriht Copyright (c) 2020 Eguana {http://eguanacommerce.com}
 * Created by PhpStorm
 * User: bilal
 * Date: 22/11/20
 * Time: 11:57 PM
 */
namespace Eguana\CustomerBulletin\Test\Unit\ViewModel;

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
use Magento\Store\Model\StoreManagerInterface;
use Magento\User\Model\UserFactory;
use Eguana\CustomerBulletin\ViewModel\Detail;
use PHPUnit\Framework\MockObject\MockObject as MockObjectAlias;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

/**
 * This class is used to test the detail ticket viewmodel instance
 *
 * Class DetailTest
 */
class DetailTest extends TestCase
{
    /**
     * @var MockObjectAlias
     */
    private $customerSession;

    /**
     * @var MockObjectAlias
     */
    private $ticketFactory;

    /**
     * @var MockObjectAlias
     */
    private $storeManager;

    /**
     * @var MockObjectAlias
     */
    private $date;

    /**
     * @var MockObjectAlias
     */
    private $helperData;

    /**
     * @var MockObjectAlias
     */
    private $ticketCollectionFactory;

    /**
     * @var MockObjectAlias
     */
    private $noteRepository;

    /**
     * @var MockObjectAlias
     */
    private $noteRepositoryFactory;

    /**
     * @var MockObjectAlias
     */
    private $ticketRepository;

    /**
     * @var MockObjectAlias
     */
    private $searchCriteriaBuilder;

    /**
     * @var MockObjectAlias
     */
    private $sortOrderBuilder;

    /**
     * @var MockObjectAlias
     */
    private $noteCollectionFactory;

    /**
     * @var MockObjectAlias
     */
    private $customerRepository;

    /**
     * @var MockObjectAlias
     */
    private $userFactory;

    /**
     * @var MockObjectAlias
     */
    private $request;

    /**
     * @var MockObjectAlias
     */
    private $filterProvider;

    /**
     * @var MockObjectAlias
     */
    private $logger;

    /**
     * To test the clode ticket controller
     */
    protected function setUp() : void
    {
        $this->ticketCollectionFactory = $this->getMockBuilder(CollectionFactory::class)
            ->disableOriginalConstructor()->getMock();
        $this->customerRepository = $this->getMockBuilder(CustomerRepositoryInterface::class)
            ->disableOriginalConstructor()->getMock();
        $this->storeManager = $this->getMockBuilder(StoreManagerInterface::class)
            ->disableOriginalConstructor()->getMock();
        $this->ticketRepository = $this->getMockBuilder(TicketRepositoryInterface::class)
            ->disableOriginalConstructor()->getMock();
        $this->ticketFactory = $this->getMockBuilder(TicketFactory::class)
            ->disableOriginalConstructor()->getMock();
        $this->filterProvider = $this->getMockBuilder(FilterProvider::class)
            ->disableOriginalConstructor()->getMock();
        $this->helperData = $this->getMockBuilder(Data::class)
            ->disableOriginalConstructor()->getMock();
        $this->request = $this->getMockBuilder(RequestInterface::class)
            ->disableOriginalConstructor()->getMock();
        $this->customerSession = $this->getMockBuilder(Session::class)
            ->disableOriginalConstructor()->getMock();
        $this->noteRepositoryFactory = $this->getMockBuilder(NoteRepositoryFactory::class)
            ->disableOriginalConstructor()->getMock();
        $this->noteRepository = $this->getMockBuilder(NoteRepositoryInterface::class)
            ->disableOriginalConstructor()->getMock();
        $this->searchCriteriaBuilder = $this->getMockBuilder(SearchCriteriaBuilder::class)
            ->disableOriginalConstructor()->getMock();
        $this->sortOrderBuilder = $this->getMockBuilder(SortOrderBuilder::class)
            ->disableOriginalConstructor()->getMock();
        $this->date = $this->getMockBuilder(DateTime::class)
            ->disableOriginalConstructor()->getMock();
        $this->userFactory = $this->getMockBuilder(UserFactory::class)
            ->disableOriginalConstructor()->getMock();
        $this->noteCollectionFactory = $this->getMockBuilder(NoteCollectionFactory::class)
            ->disableOriginalConstructor()->getMock();
        $this->logger = $this->getMockBuilder(LoggerInterface::class)
            ->disableOriginalConstructor()->getMock();

        $this->object = new Detail(
            $this->ticketCollectionFactory,
            $this->ticketFactory,
            $this->helperData,
            $this->customerSession,
            $this->customerRepository,
            $this->storeManager,
            $this->filterProvider,
            $this->noteRepositoryFactory,
            $this->noteRepository,
            $this->ticketRepository,
            $this->searchCriteriaBuilder,
            $this->sortOrderBuilder,
            $this->date,
            $this->userFactory,
            $this->request,
            $this->noteCollectionFactory,
            $this->logger
        );
        parent::setUp();
    }

    /**
     * testDetailControllerInstance()
     * this will test viewmodel Instance
     */
    public function testIndexControllerInstance()
    {
        $this->assertInstanceOf(Detail::class, $this->object);
    }

}
