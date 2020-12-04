<?php
/**
 * @author Eguana Team
 * @copyriht Copyright (c) 2020 Eguana {http://eguanacommerce.com}
 * Created by PhpStorm
 * User: bilalyounas
 * Date: 15/9/20
 * Time: 4:46 PM
 */
namespace Eguana\CustomerBulletin\Test\Unit\Controller\Index;

use PHPUnit\Framework\MockObject\MockObject as MockObjectAlias;
use PHPUnit\Framework\TestCase;
use Eguana\CustomerBulletin\Api\TicketRepositoryInterface;
use Eguana\CustomerBulletin\Helper\Data;
use Eguana\CustomerBulletin\Controller\AbstractController\TicketLoaderInterface;
use Eguana\CustomerBulletin\Model\TicketFactory;
use Magento\Customer\Model\Session;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Controller\Result\RedirectFactory;
use Magento\Framework\View\Result\PageFactory;
use Psr\Log\LoggerInterface;
use Eguana\CustomerBulletin\Controller\Index\Detail;

/**
 * This class is used to test the Detail controller instance
 *
 * Class DetailTest
 */
class DetailTest extends TestCase
{
    /**
     * @var MockObjectAlias
     */
    private $logger;

    /**
     * @var MockObjectAlias
     */
    private $requestInterface;

    /**
     * @var MockObjectAlias
     */
    private $ticketFactory;

    /**
     * @var MockObjectAlias
     */
    private $ticketRepository;

    /**
     * @var MockObjectAlias
     */
    private $customerSession;

    /**
     * @var MockObjectAlias
     */
    private $redirectFactory;

    /**
     * @var MockObjectAlias
     */
    private $pageFactory;

    /**
     * @var MockObjectAlias
     */
    private $helperData;

    /**
     * @var MockObjectAlias
     */
    private $ticketLoader;

    /**
     * To test the save controller
     */
    protected function setUp() : void
    {
        $this->pageFactory = $this->getMockBuilder(PageFactory::class)
            ->disableOriginalConstructor()->getMock();
        $this->context = $this->getMockBuilder(Context::class)
            ->disableOriginalConstructor()->getMock();
        $this->ticketRepository = $this->getMockBuilder(TicketRepositoryInterface::class)
            ->disableOriginalConstructor()->getMock();
        $this->requestInterface = $this->getMockBuilder(RequestInterface::class)
            ->disableOriginalConstructor()->getMock();
        $this->ticketFactory = $this->getMockBuilder(TicketFactory::class)
            ->disableOriginalConstructor()->getMock();
        $this->redirectFactory = $this->getMockBuilder(RedirectFactory::class)
            ->disableOriginalConstructor()->getMock();
        $this->helperData = $this->getMockBuilder(Data::class)
            ->disableOriginalConstructor()->getMock();
        $this->ticketLoader = $this->getMockBuilder(TicketLoaderInterface::class)
            ->disableOriginalConstructor()->getMock();
        $this->customerSession = $this->getMockBuilder(Session::class)
            ->disableOriginalConstructor()->getMock();
        $this->logger = $this->getMockBuilder(LoggerInterface::class)
            ->disableOriginalConstructor()->getMock();

        $this->object = new Detail(
            $this->ticketFactory,
            $this->ticketLoader,
            $this->ticketRepository,
            $this->requestInterface,
            $this->context,
            $this->logger,
            $this->helperData,
            $this->customerSession,
            $this->redirectFactory,
            $this->pageFactory
        );
        parent::setUp();
    }

    /**
     * testSaveControllerInstance()
     */
    public function testIndexControllerInstance()
    {
        $this->assertInstanceOf(Detail::class, $this->object);
    }

}
