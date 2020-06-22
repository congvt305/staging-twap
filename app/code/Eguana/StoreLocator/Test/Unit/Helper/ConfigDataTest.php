<?php
/**
 * @author Eguana Team
 * @copyriht Copyright (c) 2019 Eguana {http://eguanacommerce.com}
 * Created by PhpStorm
 * User: danish
 * Date: 11/28/19
 * Time: 3:55 PM
 */

namespace Eguana\StoreLocator\Test\Unit\Helper;

use Eguana\StoreLocator\Helper\ConfigData;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Magento\Framework\App\Helper\Context;

/**
 * Test class for helper class
 * Class ConfigDataTest
 *  Eguana\StoreLocator\Test\Unit\Helper
 */
class ConfigDataTest extends TestCase
{
    /**
     * @var MockObject
     */
    private $contextMock;

    /**
     * @var ConfigData
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
        $this->object = new ConfigData($this->contextMock);
    }

    /**
     * tesTestInstance()
     * this will test block Instance
     */
    public function testTestInstance()
    {
        $this->assertInstanceOf(ConfigData::class, $this->object);
    }
}
