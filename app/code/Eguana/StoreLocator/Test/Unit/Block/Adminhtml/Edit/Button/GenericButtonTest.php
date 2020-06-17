<?php
/**
 * @author Eguana Team
 * @copyriht Copyright (c) 2019 Eguana {http://eguanacommerce.com}
 * Created by PhpStorm
 * User: danish
 * Date: 11/28/19
 * Time: 4:07 PM
 */

namespace Eguana\StoreLocator\Test\Unit\Block\Adminhtml\Edit\Button;

use PHPUnit\Framework\TestCase;
use Eguana\StoreLocator\Block\Adminhtml\Edit\Button\GenericButton;
use Magento\Backend\Block\Widget\Context;

/**
 * test class for generic button
 * Class GenericButtonTest
 *  Eguana\StoreLocator\Test\Unit\Block\Adminhtml\Edit\Button
 */
class GenericButtonTest extends TestCase
{
    /**
     * @var MockObject
     */
    private $contextMock;

    /**
     * @var GenericButton
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
        $this->object = new GenericButton($this->contextMock);
    }
    /**
     * tesTestInstance()
     * this will test block Instance
     */
    public function testTestInstance()
    {
        $this->assertInstanceOf(GenericButton::class, $this->object);
    }
}
