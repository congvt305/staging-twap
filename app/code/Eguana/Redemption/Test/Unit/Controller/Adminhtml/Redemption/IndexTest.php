<?php
/**
 * @author Eguana Team
 * @copyriht Copyright (c) 2020 Eguana {http://eguanacommerce.com}
 * Created by PhpStorm
 * User: arslan
 * Date: 10/6/20
 * Time: 6:21 PM
 */
namespace Eguana\Redemption\Test\Unit\Controller\Adminhtml\Redemption;

use PHPUnit\Framework\MockObject\MockObject as MockObjectAlias;
use PHPUnit\Framework\TestCase;
use Magento\Framework\View\Result\PageFactory;
use Magento\Backend\App\Action\Context;
use Eguana\Redemption\Controller\Adminhtml\Redemption\Index;

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

        $this->object = new Index(
            $this->context,
            $this->resultPageFactory
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
