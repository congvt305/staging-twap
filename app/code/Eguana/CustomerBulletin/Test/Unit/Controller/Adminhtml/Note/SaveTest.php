<?php
/**
 * @author Eguana Team
 * @copyriht Copyright (c) 2020 Eguana {http://eguanacommerce.com}
 * Created by PhpStorm
 * User: bilal
 * Date: 22/11/20
 * Time: 11:57 PM
 */
namespace Eguana\CustomerBulletin\Test\Unit\Controller\Adminhtml\Note;

use Eguana\CustomerBulletin\Api\NoteRepositoryInterface;
use PHPUnit\Framework\MockObject\MockObject as MockObjectAlias;
use Eguana\CustomerBulletin\Api\TicketRepositoryInterface;
use Eguana\CustomerBulletin\Helper\Data;
use Eguana\CustomerBulletin\Model\Email\EmailSender;
use Eguana\CustomerBulletin\Model\NoteFactory;
use Eguana\CustomerBulletin\Model\TicketFactory;
use Magento\Backend\Model\Auth\Session as AdminSession;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\App\Request\Http;
use Magento\Framework\Data\Form\FormKey\Validator;
use Magento\Framework\Filesystem;
use Magento\MediaStorage\Model\File\UploaderFactory;
use Magento\User\Model\UserFactory;
use Psr\Log\LoggerInterface;
use PHPUnit\Framework\TestCase;
use Magento\Backend\App\Action\Context;
use Eguana\CustomerBulletin\Controller\Adminhtml\Note\Save;

/**
 * This class is used to test the save note controller instance
 *
 * Class SaveTest
 */

class SaveTest extends TestCase
{
    /**
     * @var MockObjectAlias
     */
    private $emailSender;

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
    private $userFactory;

    /**
     * @var MockObjectAlias
     */
    private $helperData;

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
    private $uploader;

    /**
     * @var MockObjectAlias
     */
    private $filesystem;

    /**
     * @var MockObjectAlias
     */
    private $request;

    /**
     * @var MockObjectAlias
     */
    protected $messageManager;

    /**
     * @var MockObjectAlias
     */
    private $adminSession;

    /**
     * @var MockObjectAlias
     */
    private $searchCriteriaBuilder;

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
        $this->emailSender = $this->getMockBuilder(EmailSender::class)
            ->disableOriginalConstructor()->getMock();
        $this->context = $this->getMockBuilder(Context::class)
            ->disableOriginalConstructor()->getMock();
        $this->ticketRepository = $this->getMockBuilder(TicketRepositoryInterface::class)
            ->disableOriginalConstructor()->getMock();
        $this->ticketFactory = $this->getMockBuilder(TicketFactory::class)
            ->disableOriginalConstructor()->getMock();
        $this->uploader = $this->getMockBuilder(UploaderFactory::class)
            ->disableOriginalConstructor()->getMock();
        $this->userFactory = $this->getMockBuilder(UserFactory::class)
            ->disableOriginalConstructor()->getMock();
        $this->helperData = $this->getMockBuilder(Data::class)
            ->disableOriginalConstructor()->getMock();
        $this->filesystem = $this->getMockBuilder(Filesystem::class)
            ->disableOriginalConstructor()->getMock();
        $this->searchCriteriaBuilder = $this->getMockBuilder(SearchCriteriaBuilder::class)
            ->disableOriginalConstructor()->getMock();
        $this->noteFactory = $this->getMockBuilder(NoteFactory::class)
            ->disableOriginalConstructor()->getMock();
        $this->noteRepository = $this->getMockBuilder(NoteRepositoryInterface::class)
            ->disableOriginalConstructor()->getMock();
        $this->formKeyValidator = $this->getMockBuilder(Validator::class)
            ->disableOriginalConstructor()->getMock();
        $this->adminSession = $this->getMockBuilder(AdminSession::class)
            ->disableOriginalConstructor()->getMock();
        $this->request = $this->getMockBuilder(Http::class)
            ->disableOriginalConstructor()->getMock();
        $this->logger = $this->getMockBuilder(LoggerInterface::class)
            ->disableOriginalConstructor()->getMock();

        $this->object = new Save(
            $this->context,
            $this->emailSender,
            $this->ticketFactory,
            $this->uploader,
            $this->userFactory,
            $this->helperData,
            $this->filesystem,
            $this->ticketRepository,
            $this->searchCriteriaBuilder,
            $this->noteFactory,
            $this->noteRepository,
            $this->formKeyValidator,
            $this->adminSession,
            $this->request,
            $this->logger
        );
        parent::setUp();
    }

    /**
     * testSaveControllerInstance()
     * this will test controller Instance
     */
    public function testIndexControllerInstance()
    {
        $this->assertInstanceOf(Save::class, $this->object);
    }

}
