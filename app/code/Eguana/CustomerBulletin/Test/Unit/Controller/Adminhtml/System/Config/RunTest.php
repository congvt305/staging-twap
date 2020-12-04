<?php
/**
 * @author Eguana Team
 * @copyriht Copyright (c) 2020 Eguana {http://eguanacommerce.com}
 * Created by PhpStorm
 * User: bilalyounas
 * Date: 15/9/20
 * Time: 4:46 PM
 */

namespace Eguana\CustomerBulletin\Test\Unit\Controller\Adminhtml\System\Config;

use PHPUnit\Framework\MockObject\MockObject as MockObjectAlias;
use PHPUnit\Framework\TestCase;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;
use Eguana\CustomerBulletin\Model\TicketCloser;
use Eguana\CustomerBulletin\Controller\Adminhtml\System\Config\Run;

/**
 * This class is used to test the run controller instance
 *
 * Class RunTest
 */
class RunTest extends TestCase
{
    /**
     * @var MockObjectAlias
     */
    private $resultJsonFactory;

    /**
     * @var MockObjectAlias
     */
    private $ticketCloser;

    /**
     * @var MockObjectAlias
     */
    private $context;

    /**
     * To test the run controller
     */
    protected function setUp() : void
    {
        $this->context = $this->getMockBuilder(Context::class)
            ->disableOriginalConstructor()->getMock();
        $this->resultJsonFactory = $this->getMockBuilder(JsonFactory::class)
            ->disableOriginalConstructor()->getMock();
        $this->ticketCloser = $this->getMockBuilder(TicketCloser::class)
            ->disableOriginalConstructor()->getMock();

        $this->object = new Run(
            $this->context,
            $this->resultJsonFactory,
            $this->ticketCloser
        );
        parent::setUp();
    }

    /**
     * testrunControllerInstance()
     * this will test controller Instance
     */
    public function testIndexControllerInstance()
    {
        $this->assertInstanceOf(Run::class, $this->object);
    }

}
