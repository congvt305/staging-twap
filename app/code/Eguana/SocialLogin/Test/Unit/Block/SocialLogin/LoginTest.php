<?php
/**
 * @author Eguana Team
 * @copyright Copyright (c) 2020 Eguana {http://eguanacommerce.com}
 * Created by PhpStorm
 * User: umer
 * Date: 10/6/20
 * Time: 2:06 PM
 */
namespace Eguana\SocialLogin\Test\Unit\Block\SocialLogin;

use Eguana\SocialLogin\Block\SocialLogin\Login;
use Eguana\SocialLogin\Helper\Data;
use Magento\Framework\Registry;
use Magento\Framework\Session\SessionManagerInterface;
use Magento\Framework\View\Element\Template\Context;
use Magento\Store\Model\StoreManagerInterface;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

class LoginTest extends TestCase
{

    /**
     * @var Login
     */
    private $object;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var Registry
     */
    private $_registry;

    /**
     * @var SessionManagerInterface
     */
    private $_coreSession;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var Data
     */
    private $helper;

    protected function setUp() : void
    {
        $this->contextMock = $this->getMockBuilder(Context::class)->disableOriginalConstructor()->getMock();
        $this->storeManager = $this->getMockBuilder(
            StoreManagerInterface::class
        )->disableOriginalConstructor()->getMock();
        $this->_registry = $this->getMockBuilder(Registry::class)
            ->disableOriginalConstructor()->getMock();
        $this->_coreSession = $this->getMockBuilder(
            SessionManagerInterface::class
        )->disableOriginalConstructor()->getMock();
        $this->logger = $this->getMockBuilder(LoggerInterface::class)->disableOriginalConstructor()->getMock();
        $this->helper = $this->getMockBuilder(Data::class)->disableOriginalConstructor()->getMock();
        $this->object = new Login(
            $this->contextMock,
            $this->storeManager,
            $this->_registry,
            $this->_coreSession,
            $this->logger,
            $this->helper
        );
        parent::setUp();
    }

    /**
     * testGetHelper()
     * this will test helper Instance
     */
    public function testGetHelper()
    {
        $result = $this->object->getHelper();
        $this->assertEquals(Data::class, $result);
    }

    public function testLoginInstance()
    {
        $this->assertInstanceOf(Login::class, $this->object->getHelper());
    }
}
