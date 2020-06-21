<?php
/**
 * @author Eguana Team
 * @copyriht Copyright (c) 2019 Eguana {http://eguanacommerce.com}
 * Created by PhpStorm
 * User: danish
 * Date: 11/28/19
 * Time: 4:23 PM
 */

namespace Eguana\StoreLocator\Test\Unit\Controller\Adminhtml\Edit;

use Eguana\StoreLocator\Api\StoreInfoRepositoryInterface;
use Eguana\StoreLocator\Controller\Adminhtml\Edit\Validate;
use Eguana\StoreLocator\Helper\ConfigData;
use Eguana\StoreLocator\Model\ResourceModel\StoreInfo as StoreInfoResource;
use Eguana\StoreLocator\Model\StoreInfo;
use Eguana\StoreLocator\Model\WorkTimeFactory;
use Magento\Backend\App\Action\Context;
use Magento\Backend\Model\SessionFactory;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Driver\File;
use Magento\Framework\View\Result\PageFactory;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

/**
 * TestCase for Validate class
 * Class ValidateTest
 *  Eguana\StoreLocator\Test\Unit\Controller\Adminhtml
 */
class ValidateTest extends TestCase
{
    /**
     * @var MockObject
     */
    private $contextMock;

    /**
     * @var PageFactory
     */
    private $resultPageFactoryMock;

    /**
     * @var StoreInfo
     */
    private $storeInfoMock;

    /**
     * @var WorkTimeFactory
     */
    private $workTimeMock;

    /**
     * @var JsonFactory
     */
    private $resultJsonFactoryMock;

    /**
     * @var LoggerInterface
     */
    private $loggerMock;

    /**
     * @var Filesystem
     */
    private $fileSystemMock;

    /**
     * @var File
     */
    private $fileDriverMock;

    /**
     * @var ConfigData
     */
    private $storesHelperMock;

    /**
     * @var SessionFactory
     */
    private $sessionMock;

    /**
     * @var StoreInfoRepositoryInterface
     */
    private $storeInfoRepoMock;

    /**
     * @var StoreInfoResource
     */
    private $storeInfoResourceMock;

    /**
     * @var Validate
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
        $this->resultPageFactoryMock = $this->getMockBuilder(PageFactory::class)
            ->disableOriginalConstructor()->getMock();
        $this->resultJsonFactoryMock = $this->getMockBuilder(JsonFactory::class)
            ->disableOriginalConstructor()->getMock();
        $this->storeInfoMock = $this->getMockBuilder(StoreInfo::class)
            ->disableOriginalConstructor()->getMock();
        $this->workTimeMock = $this->getMockBuilder(WorkTimeFactory::class)
            ->disableOriginalConstructor()->getMock();
        $this->loggerMock =  $this->getMockBuilder(LoggerInterface::class)
            ->disableOriginalConstructor()->getMock();
        $this->fileDriverMock = $this->getMockBuilder(File::class)
            ->disableOriginalConstructor()->getMock();
        $this->fileSystemMock = $this->getMockBuilder(Filesystem::class)
            ->disableOriginalConstructor()->getMock();
        $this->storesHelperMock = $this->getMockBuilder(ConfigData::class)
            ->disableOriginalConstructor()->getMock();
        $this->sessionMock = $this->getMockBuilder(SessionFactory::class)
            ->disableOriginalConstructor()->getMock();
        $this->storeInfoRepoMock = $this->getMockBuilder(StoreInfoRepositoryInterface::class)
            ->disableOriginalConstructor()->getMock();
        $this->storeInfoResourceMock = $this->getMockBuilder(StoreInfoResource::class)
            ->disableOriginalConstructor()->getMock();

        $this->object = new Validate(
            $this->contextMock,
            $this->resultPageFactoryMock,
            $this->resultJsonFactoryMock,
            $this->storeInfoMock,
            $this->workTimeMock,
            $this->loggerMock,
            $this->fileDriverMock,
            $this->fileSystemMock,
            $this->storesHelperMock,
            $this->sessionMock,
            $this->storeInfoRepoMock,
            $this->storeInfoResourceMock
        );
    }

    /**
     * tesTestInstance()
     * this will test block Instance
     */
    public function testTestInstance()
    {
        $this->assertInstanceOf(Validate::class, $this->object);
    }
}
