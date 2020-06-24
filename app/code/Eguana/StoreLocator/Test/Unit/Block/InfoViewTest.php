<?php
/**
 * @author Eguana Team
 * @copyriht Copyright (c) 2019 Eguana {http://eguanacommerce.com}
 * Created by PhpStorm
 * User: danish
 * Date: 11/28/19
 * Time: 3:42 PM
 */

namespace Eguana\StoreLocator\Test\Unit\Block;

use Eguana\StoreLocator\Api\StoreInfoRepositoryInterface;
use Eguana\StoreLocator\Api\WorkTimeRepositoryInterface;
use Eguana\StoreLocator\Block\InfoView;
use Eguana\StoreLocator\Helper\ConfigData;
use Eguana\StoreLocator\Model\StoreInfoRepository;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Api\SortOrderBuilder;
use Magento\Framework\View\Element\Template\Context;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

/**
 * infoView test class
 * Class InfoViewTest
 *  Eguana\StoreLocator\Test\Unit\Block
 */
class InfoViewTest extends TestCase
{
    /**
     * @var MockObject
     */
    private $contextMock;

    /**
     * @var MockObject
     */
    private $searchCriteriaBuilderMock;

    /**
     * @var StoreInfoRepositoryInterface
     */
    private $storeInfoRepoMock;

    /**
     * @var WorkTimeRepositoryInterface
     */
    private $workTimeRepoMock;

    /**
     * @var SortOrderBuilder
     */
    private $sortOrderBuilderMock;

    /**
     * @var MockObject
     */
    private $configData;

    /**
     * @var InfoView
     */
    private $object;

    /**
     * @var MockObject
     */
    private $loggerMock;

    /**
     * @var array
     */
    private $data=[];

    /**
     * Setup function
     * For creating Moc objects and and assigning
     */
    public function setUp()
    {
        $this->contextMock = $this->getMockBuilder(Context::class)
            ->disableOriginalConstructor()->getMock();
        $this->configData = $this->getMockBuilder(ConfigData::class)
            ->disableOriginalConstructor()->getMock();
        $this->loggerMock = $this->getMockBuilder(LoggerInterface::class)
            ->disableOriginalConstructor()->getMock();
        $this->storeInfoRepoMock = $this->getMockBuilder(StoreInfoRepository::class)
            ->disableOriginalConstructor()->getMock();
        $this->workTimeRepoMock = $this->getMockBuilder(WorkTimeRepositoryInterface::class)
            ->disableOriginalConstructor()->getMock();
        $this->searchCriteriaBuilderMock = $this->getMockBuilder(SearchCriteriaBuilder::class)
            ->disableOriginalConstructor()->getMock();
        $this->sortOrderBuilderMock = $this->getMockBuilder(SortOrderBuilder::class)
            ->disableOriginalConstructor()->getMock();
        $this->object = new InfoView(
            $this->contextMock,
            $this->configData,
            $this->loggerMock,
            $this->storeInfoRepoMock,
            $this->workTimeRepoMock,
            $this->searchCriteriaBuilderMock,
            $this->sortOrderBuilderMock,
            $this->data
        );
    }
    /**
     * tesTestInstance()
     * this will test block Instance
     */
    public function testTestInstance()
    {
        $this->assertInstanceOf(InfoView::class, $this->object);
    }
}
