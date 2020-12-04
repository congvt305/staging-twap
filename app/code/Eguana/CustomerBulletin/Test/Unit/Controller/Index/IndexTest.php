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

use Eguana\CustomerBulletin\Helper\Data;
use Magento\Customer\Model\Session;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\Result\RedirectFactory;
use Magento\Framework\View\Result\PageFactory;
use PHPUnit\Framework\MockObject\MockObject as MockObjectAlias;
use Psr\Log\LoggerInterface;
use PHPUnit\Framework\TestCase;
use Eguana\CustomerBulletin\Controller\Index\Index;

/**
 * This class is used to test the ticket listing controller instance
 *
 * Class IndexTest
 */
class IndexTest extends TestCase
{
    /**
     * @var MockObjectAlias
     */
    private $customerSession;

    /**
     * @var MockObjectAlias
     */
    private $pageFactory;

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
    private $context;

    /**
     * To test the clode ticket controller
     */
    protected function setUp() : void
    {
        $this->pageFactory = $this->getMockBuilder(PageFactory::class)
            ->disableOriginalConstructor()->getMock();
        $this->redirectFactory = $this->getMockBuilder(RedirectFactory::class)
            ->disableOriginalConstructor()->getMock();
        $this->logger = $this->getMockBuilder(LoggerInterface::class)
            ->disableOriginalConstructor()->getMock();
        $this->helperData = $this->getMockBuilder(Data::class)
            ->disableOriginalConstructor()->getMock();
        $this->context = $this->getMockBuilder(Context::class)
            ->disableOriginalConstructor()->getMock();
        $this->customerSession = $this->getMockBuilder(Session::class)
            ->disableOriginalConstructor()->getMock();

        $this->object = new Index(
            $this->context,
            $this->helperData,
            $this->logger,
            $this->customerSession,
            $this->redirectFactory,
            $this->pageFactory
        );
        parent::setUp();
    }

    /**
     * testCloseControllerInstance()
     * this will test controller Instance
     */
    public function testIndexControllerInstance()
    {
        $this->assertInstanceOf(Index::class, $this->object);
    }

}
