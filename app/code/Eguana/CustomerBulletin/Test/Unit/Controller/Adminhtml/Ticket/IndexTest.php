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
use Magento\Backend\Model\View\Result\ForwardFactory;
use PHPUnit\Framework\TestCase;
use Magento\Framework\View\Result\PageFactory;
use Magento\Backend\App\Action\Context;
use Eguana\CustomerBulletin\Controller\Adminhtml\Ticket\Index;

/**
 * This class is used to test the index controller instance
 *
 * Class IndexTest
 */
class IndexTest extends TestCase
{
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

        $this->object = new Index(
            $this->resultPageFactory,
            $this->resultForwardFactory,
            $this->context,
            $this->coreRegistry,
            $this->ticketRepository
        );
        parent::setUp();
    }

    /**
     * testIndexControllerInstance()
     * this will test viewmodel Instance
     */
    public function testIndexControllerInstance()
    {
        $this->assertInstanceOf(Index::class, $this->object);
    }
}
