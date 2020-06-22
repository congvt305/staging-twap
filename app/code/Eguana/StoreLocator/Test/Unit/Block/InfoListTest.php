<?php
/**
 * @author Eguana Team
 * @copyright Copyright (c) 2019 Eguana {http://eguanacommerce.com}
 * Created by PhpStorm
 * User: danish
 * Date: 11/28/19
 * Time: 11:21 AM
 */

namespace Eguana\StoreLocator\Test\Unit\Block;

use Eguana\StoreLocator\Api\StoreInfoRepositoryInterface;
use Eguana\StoreLocator\Api\WorkTimeRepositoryInterface;
use Eguana\StoreLocator\Block\InfoList;
use Eguana\StoreLocator\Helper\ConfigData;
use Eguana\StoreLocator\Model\StoreInfoRepository;
use Magento\Directory\Model\ResourceModel\Region\CollectionFactory as RegionCollection;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Api\SortOrderBuilder;
use Magento\Framework\View\Element\Template\Context;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Block test
 *
 * Class InfoListTest
 *  Eguana\StoreLocator\Test\Unit\Block
 */
class InfoListTest extends TestCase
{
    private $contextMock;

    /**
     * @var RegionCollection
     */
    private $regionCollectionFactoryMock;

    /**
     * @var
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
     * @var InfoList
     */
    private $object;

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
        $this->regionCollectionFactoryMock = $this->getMockBuilder(RegionCollection::class)
            ->disableOriginalConstructor()->getMock();
        $this->searchCriteriaBuilderMock = $this->getMockBuilder(SearchCriteriaBuilder::class)
            ->disableOriginalConstructor()->getMock();
        $this->storeInfoRepoMock = $this->getMockBuilder(StoreInfoRepository::class)
            ->disableOriginalConstructor()->getMock();
        $this->workTimeRepoMock = $this->getMockBuilder(WorkTimeRepositoryInterface::class)
            ->disableOriginalConstructor()->getMock();
        $this->sortOrderBuilderMock = $this->getMockBuilder(SortOrderBuilder::class)
            ->disableOriginalConstructor()->getMock();
        $this->configData = $this->getMockBuilder(ConfigData::class)
            ->disableOriginalConstructor()->getMock();
        $this->object = new InfoList(
            $this->contextMock,
            $this->configData,
            $this->regionCollectionFactoryMock,
            $this->searchCriteriaBuilderMock,
            $this->storeInfoRepoMock,
            $this->workTimeRepoMock,
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
        $this->assertInstanceOf(InfoList::class, $this->object);
    }
}
