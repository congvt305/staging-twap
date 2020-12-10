<?php
/**
 * @author Eguana Team
 * @copyriht Copyright (c) 2020 Eguana {http://eguanacommerce.com}
 * Created by PhpStorm
 * User: bilalyounas
 * Date: 22/9/20
 * Time: 4:53 PM
 */
namespace Eguana\CustomerBulletin\Test\Integration\Helper;

use PHPUnit\Framework\TestCase;

/**
 * Class DataTest
 */
class DataTest extends TestCase
{
    /**
     * @var \Magento\TestFramework\Annotation\ConfigFixture|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $_object;

    protected function setUp()
    {
        $this->_object = $this->createPartialMock(
            \Magento\TestFramework\Annotation\ConfigFixture::class,
            ['_getConfigValue', '_setConfigValue']
        );
    }

    /**
     * @magentoConfigFixture ticket_managment/configuration/enabled 1
     */
    public function testEnableConfig()
    {
        $this->_object->startTest($this);
        $this->_object->expects(
            $this->at(0)
        )->method(
            '_getConfigValue'
        )->with(
            'ticket_managment/configuration/enabled'
        )->will(
            $this->returnValue('1')
        );
        $this->_object->expects(
            $this->at(1)
        )->method(
            '_setConfigValue'
        )->with(
            'ticket_managment/configuration/enabled',
            '1'
        );
        $this->_object->initStoreAfter();
    }
}
