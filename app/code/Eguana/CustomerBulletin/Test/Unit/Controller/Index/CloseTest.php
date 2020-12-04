<?php
/**
 * @author Eguana Team
 * @copyriht Copyright (c) 2020 Eguana {http://eguanacommerce.com}
 * Created by PhpStorm
 * User: bilal
 * Date: 22/11/20
 * Time: 11:57 PM
 */
namespace Eguana\CustomerBulletin\Test\Unit\Controller\Index;

use Eguana\CustomerBulletin\Api\TicketRepositoryInterface;
use PHPUnit\Framework\MockObject\MockObject as MockObjectAlias;
use PHPUnit\Framework\TestCase;
use Eguana\CustomerBulletin\Helper\Data;
use Eguana\CustomerBulletin\Model\Email\EmailSender;
use Eguana\CustomerBulletin\Model\TicketFactory;
use Magento\Customer\Model\Session;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Controller\Result\RedirectFactory;
use Eguana\CustomerBulletin\Controller\Index\Close;
use Psr\Log\LoggerInterface;

/**
 * This class is used to test the close ticket controller instance
 *
 * Class CloseTest
 */
class CloseTest extends TestCase
{
    /**
     * @var MockObjectAlias
     */
    private $emailSender;

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
    private $helperData;

    /**
     * @var MockObjectAlias
     */
    private $logger;

    /**
     * @var MockObjectAlias
     */
    private $ticketFactory;

    /**
     * @var MockObjectAlias
     */
    private $requestInterface;

    /**
     * @var MockObjectAlias
     */
    private $ticketRepository;

    /**
     * @var MockObjectAlias
     */
    protected $messageManager;

    /**
     * @var MockObjectAlias
     */
    private $context;

    /**
     * To test the clode ticket controller
     */
    protected function setUp() : void
    {
        $this->emailSender = $this->getMockBuilder(EmailSender::class)
            ->disableOriginalConstructor()->getMock();
        $this->redirectFactory = $this->getMockBuilder(RedirectFactory::class)
            ->disableOriginalConstructor()->getMock();
        $this->context = $this->getMockBuilder(Context::class)
            ->disableOriginalConstructor()->getMock();
        $this->ticketRepository = $this->getMockBuilder(TicketRepositoryInterface::class)
            ->disableOriginalConstructor()->getMock();
        $this->ticketFactory = $this->getMockBuilder(TicketFactory::class)
            ->disableOriginalConstructor()->getMock();
        $this->logger = $this->getMockBuilder(LoggerInterface::class)
            ->disableOriginalConstructor()->getMock();
        $this->helperData = $this->getMockBuilder(Data::class)
            ->disableOriginalConstructor()->getMock();
        $this->requestInterface = $this->getMockBuilder(RequestInterface::class)
            ->disableOriginalConstructor()->getMock();
        $this->customerSession = $this->getMockBuilder(Session::class)
            ->disableOriginalConstructor()->getMock();

        $this->object = new Close(
            $this->context,
            $this->helperData,
            $this->emailSender,
            $this->logger,
            $this->customerSession,
            $this->ticketFactory,
            $this->requestInterface,
            $this->ticketRepository,
            $this->redirectFactory
        );
        parent::setUp();
    }

    /**
     * testCloseControllerInstance()
     * this will test controller Instance
     */
    public function testIndexControllerInstance()
    {
        $this->assertInstanceOf(Close::class, $this->object);
    }
}
