<?php
/**
 * @author Eguana Team
 * @copyright Copyright (c) 2019 Eguana {http://eguanacommerce.com}
 * Created by PhpStorm
 * User: danish
 * Date: 11/28/19
 * Time: 11:14 AM
 */

namespace Eguana\StoreLocator\Test\Unit\Controller\Info;

use Eguana\StoreLocator\Controller\Info\View;
use Eguana\StoreLocator\Helper\ConfigData;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\Result\ForwardFactory;
use Magento\Framework\View\Result\PageFactory;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Test class for testing controller
 *
 * Class ViewTest
 *  Eguana\StoreLocator\Test\Unit\Controller\Info
 */
class ViewTest extends TestCase
{

    /**
     * @var MockObject
     */
    private $contextMock;

    /**
     * @var MockObject
     */
    private $pageFactoryMock;

    /**
     * @var MockObject
     */
    private $forwardFactoryMock;

    /**
     * @var MockObject
     */
    private $configData;

    /**
     * @var View
     */
    private $object;

    /**
     * Setup function
     * For creating Moc objects and and assigning
     */
    public function setUp()
    {
        $this->contextMock = $this->getMockBuilder(Context::class)
            ->disableOriginalConstructor()->getMock();
        $this->pageFactoryMock = $this->getMockBuilder(PageFactory::class)
            ->disableOriginalConstructor()->getMock();
        $this->forwardFactoryMock = $this->getMockBuilder(ForwardFactory::class)
            ->disableOriginalConstructor()->getMock();
        $this->configData = $this->getMockBuilder(ConfigData::class)
            ->disableOriginalConstructor()->getMock();
        $this->object = new View(
            $this->contextMock,
            $this->forwardFactoryMock,
            $this->pageFactoryMock,
            $this->configData
        );
    }

    /**
     * testControllerInstance()
     * this will test Controller Instance
     */
    public function testControllerInstance()
    {
        $this->assertInstanceOf(View::class, $this->object);
    }
}
