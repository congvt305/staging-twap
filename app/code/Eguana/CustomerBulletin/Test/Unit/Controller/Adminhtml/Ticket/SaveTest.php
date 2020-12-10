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

use PHPUnit\Framework\MockObject\MockObject as MockObjectAlias;
use Eguana\CustomerBulletin\Api\TicketRepositoryInterface;
use Magento\Framework\Registry;
use Eguana\CustomerBulletin\Model\TicketFactory;
use Magento\Framework\Message\Manager;
use Magento\Framework\Api\DataObjectHelper;
use Magento\Backend\Model\View\Result\ForwardFactory;
use PHPUnit\Framework\TestCase;
use Magento\Framework\View\Result\PageFactory;
use Magento\Backend\App\Action\Context;
use Eguana\CustomerBulletin\Controller\Adminhtml\Ticket\Save;

/**
 * This class is used to test the index controller instance
 *
 * Class IndexTest
 */

class SaveTest extends TestCase
{
    /**
     * @var MockObjectAlias
     */
    protected $messageManager;

    /**
     * @var MockObjectAlias
     */
    protected $ticketFactory;

    /**
     * @var MockObjectAlias
     */
    protected $dataObjectHelper;

    /**
     * @var MockObjectAlias
     */
    protected $ticketRepository;

    /**
     * @var MockObjectAlias
     */
    protected $coreRegistry;

    /**
     * @var MockObjectAlias
     */
    protected $resultForwardFactory;

    /**
     * @var MockObjectAlias
     */
    private $resultPageFactory;

    /**
     * @var MockObjectAlias
     */
    private $context;

    /**
     * To test the index controller
     */
    protected function setUp() : void
    {
        $this->resultPageFactory = $this->getMockBuilder(PageFactory::class)
            ->disableOriginalConstructor()->getMock();
        $this->context = $this->getMockBuilder(Context::class)
            ->disableOriginalConstructor()->getMock();
        $this->ticketRepository = $this->getMockBuilder(TicketRepositoryInterface::class)
            ->disableOriginalConstructor()->getMock();
        $this->resultForwardFactory = $this->getMockBuilder(ForwardFactory::class)
            ->disableOriginalConstructor()->getMock();
        $this->coreRegistry = $this->getMockBuilder(Registry::class)
            ->disableOriginalConstructor()->getMock();
        $this->messageManager = $this->getMockBuilder(Manager::class)
            ->disableOriginalConstructor()->getMock();
        $this->ticketFactory = $this->getMockBuilder(TicketFactory::class)
            ->disableOriginalConstructor()->getMock();
        $this->dataObjectHelper = $this->getMockBuilder(DataObjectHelper::class)
            ->disableOriginalConstructor()->getMock();

        $this->object = new Save(
            $this->coreRegistry,
            $this->ticketFactory,
            $this->ticketRepository,
            $this->resultPageFactory,
            $this->resultForwardFactory,
            $this->messageManager,
            $this->dataObjectHelper,
            $this->context
        );
        parent::setUp();
    }

    /**
     * testIndexControllerInstance()
     * this will test viewmodel Instance
     */
    public function testIndexControllerInstance()
    {
        $this->assertInstanceOf(Save::class, $this->object);
    }

}
