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

use Eguana\CustomerBulletin\Api\TicketRepositoryInterface;
use PHPUnit\Framework\MockObject\MockObject as MockObjectAlias;
use PHPUnit\Framework\TestCase;
use Eguana\CustomerBulletin\Model\Email\EmailSender;
use Eguana\CustomerBulletin\Helper\Data;
use Eguana\CustomerBulletin\Model\TicketFactory;
use Magento\Customer\Model\Session;
use Eguana\CustomerBulletin\Controller\Index\CreateTicket;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\Result\RedirectFactory;
use Magento\Framework\Data\Form\FormKey\Validator;
use Magento\Framework\Filesystem;
use Magento\Framework\View\Result\PageFactory;
use Magento\MediaStorage\Model\File\UploaderFactory;
use Magento\Store\Model\StoreManagerInterface;
use Psr\Log\LoggerInterface;

/**
 * This class is used to test the create ticket controller instance
 *
 * Class CreateTicketTest
 */
class CreateTicketTest extends TestCase
{
    /**
     * @var MockObjectAlias
     */
    private $emailSender;

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
    private $formKeyValidator;

    /**
     * @var MockObjectAlias
     */
    private $ticketRepository;

    /**
     * @var MockObjectAlias
     */
    private $ticketFactory;

    /**
     * @var MockObjectAlias
     */
    private $uploader;

    /**
     * @var MockObjectAlias
     */
    private $filesystem;

    /**
     * @var MockObjectAlias
     */
    private $customerSession;

    /**
     * @var MockObjectAlias
     */
    private $helperData;

    /**
     * @var MockObjectAlias
     */
    protected $messageManager;

    /**
     * @var MockObjectAlias
     */
    private $storeManager;

    /**
     * @var MockObjectAlias
     */
    private $logger;

    /**
     * To test the create ticket controller
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
        $this->customerSession = $this->getMockBuilder(Session::class)
            ->disableOriginalConstructor()->getMock();
        $this->uploader = $this->getMockBuilder(UploaderFactory::class)
            ->disableOriginalConstructor()->getMock();
        $this->filesystem = $this->getMockBuilder(Filesystem::class)
            ->disableOriginalConstructor()->getMock();
        $this->storeManager = $this->getMockBuilder(StoreManagerInterface::class)
            ->disableOriginalConstructor()->getMock();
        $this->formKeyValidator = $this->getMockBuilder(Validator::class)
            ->disableOriginalConstructor()->getMock();
        $this->pageFactory = $this->getMockBuilder(PageFactory::class)
            ->disableOriginalConstructor()->getMock();

        $this->object = new CreateTicket(
            $this->context,
            $this->redirectFactory,
            $this->helperData,
            $this->uploader,
            $this->filesystem,
            $this->emailSender,
            $this->ticketRepository,
            $this->customerSession,
            $this->ticketFactory,
            $this->formKeyValidator,
            $this->pageFactory,
            $this->storeManager,
            $this->logger
        );
        parent::setUp();
    }

    /**
     * testCreateTicketControllerInstance()
     * this will test controller Instance
     */
    public function testIndexControllerInstance()
    {
        $this->assertInstanceOf(CreateTicket::class, $this->object);
    }

}
