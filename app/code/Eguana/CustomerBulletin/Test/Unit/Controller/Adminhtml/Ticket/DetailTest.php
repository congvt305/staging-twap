<?php
/**
 * @author Eguana Team
 * @copyriht Copyright (c) 2020 Eguana {http://eguanacommerce.com}
 * Created by PhpStorm
 * User: bilal
 * Date: 22/11/20
 * Time: 11:57 PM
 */

namespace Eguana\CustomerBulletin\Test\Unit\Controller\Adminhtml\Ticket;

use Eguana\CustomerBulletin\Api\NoteRepositoryInterface;
use Eguana\CustomerBulletin\Api\TicketRepositoryInterface;
use Eguana\CustomerBulletin\Model\NoteFactory;
use Eguana\CustomerBulletin\Model\TicketFactory;
use Magento\Backend\Model\Auth\Session as AdminSession;
use Magento\Customer\Model\Session;
use PHPUnit\Framework\MockObject\MockObject as MockObjectAlias;
use PHPUnit\Framework\TestCase;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Data\Form\FormKey\Validator;
use Magento\Framework\Filesystem;
use Magento\Framework\Image\AdapterFactory;
use Magento\Framework\View\Result\PageFactory;
use Magento\MediaStorage\Model\File\UploaderFactory;
use Psr\Log\LoggerInterface;
use Eguana\CustomerBulletin\Controller\Adminhtml\Ticket\Detail;

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
    private $formKeyValidator;

    /**
     * @var MockObjectAlias
     */
    private $noteRepository;

    /**
     * @var MockObjectAlias
     */
    private $noteFactory;

    /**
     * @var MockObjectAlias
     */
    private $adapterFactory;

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
    private $adminSession;

    /**
     * @var MockObjectAlias
     */
    private $pageFactory;
    /**
     * @var MockObjectAlias
     */
    private $logger;

    /**
     * @var MockObjectAlias
     */
    private $context;

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
        $this->formKeyValidator = $this->getMockBuilder(Validator::class)
            ->disableOriginalConstructor()->getMock();
        $this->noteRepository = $this->getMockBuilder(NoteRepositoryInterface::class)
            ->disableOriginalConstructor()->getMock();
        $this->noteFactory = $this->getMockBuilder(NoteFactory::class)
            ->disableOriginalConstructor()->getMock();
        $this->adapterFactory = $this->getMockBuilder(AdapterFactory::class)
            ->disableOriginalConstructor()->getMock();
        $this->uploader = $this->getMockBuilder(UploaderFactory::class)
            ->disableOriginalConstructor()->getMock();
        $this->filesystem = $this->getMockBuilder(Filesystem::class)
            ->disableOriginalConstructor()->getMock();
        $this->customerSession = $this->getMockBuilder(Session::class)
            ->disableOriginalConstructor()->getMock();
        $this->adminSession = $this->getMockBuilder(AdminSession::class)
            ->disableOriginalConstructor()->getMock();
        $this->logger = $this->getMockBuilder(LoggerInterface::class)
            ->disableOriginalConstructor()->getMock();

        $this->object = new Detail(
            $this->context,
            $this->ticketFactory,
            $this->requestInterface,
            $this->ticketRepository,
            $this->logger,
            $this->adminSession,
            $this->uploader,
            $this->filesystem,
            $this->adapterFactory,
            $this->customerSession,
            $this->noteFactory,
            $this->noteRepository,
            $this->formKeyValidator,
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
