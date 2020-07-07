<?php
/**
 * @author Eguana Team
 * @copyright Copyright (c) 2020 Eguana {http://eguanacommerce.com}
 * Created by PhpStorm
 * User: umer
 * Date: 1/7/20
 * Time: 7:58 PM
 */
namespace Eguana\MobileLogin\Test\Unit\Plugin\ValidateCustomer;

use Eguana\MobileLogin\Helper\Data;
use Eguana\MobileLogin\Plugin\ValidateCustomer;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Model\AccountManagement;
use Magento\Framework\Api\SearchCriteriaBuilder;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

/**
 * Class ValidateCustomerTest
 *
 * For the unit test of validate customer plugin
 */
class ValidateCustomerTest extends TestCase
{

    public function setUp(): void
    {
        $this->helper = $this->createMock(Data::class);
        $this->customerRepository = $this->getMockBuilder(CustomerRepositoryInterface::class)
            ->setMethods(['getList'])
            ->disableOriginalConstructor()->getMockForAbstractClass();
        $this->searchCriteriaBuilder = $this->createMock(SearchCriteriaBuilder::class);
        $this->searchCriteria = $this
            ->getMockBuilder(\Magento\Framework\Api\SearchCriteria::class)
            ->setMethods(['create','addFilter'])
            ->disableOriginalConstructor()->getMock();
        $this->searchResults = $this->getMockForAbstractClass(
            \Magento\Framework\Api\SearchResultsInterface::class,
            ['getTotalCount', 'getItems']
        );
        $this->searchResults
            ->expects($this->any())
            ->method('getTotalCount');
        $this->searchResults
            ->expects($this->any())
            ->method('getItems')
            ->willReturn($this->returnValue([]));
        $this->logger = $this->createMock(LoggerInterface::class);
        $this->accountManagement = $this->createMock(AccountManagement::class);
        $this->object = new ValidateCustomer(
            $this->helper,
            $this->customerRepository,
            $this->searchCriteriaBuilder,
            $this->logger
        );
    }

    /**
     * Test plugin instance
     */
    public function testPluginInstance()
    {
        $this->assertInstanceOf(ValidateCustomer::class, $this->object);
    }

    /**
     * Test customer by mobile no
     */
    public function testGetCustomerByMobileNumber()
    {
        $this->objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $customerData = '2147483647';
        $this->searchCriteriaBuilder = $this->createMock(SearchCriteriaBuilder::class);
        $filterGroupBuilder = $this->objectManager
            ->getObject(\Magento\Framework\Api\Search\FilterGroupBuilder::class);
        /** @var \Magento\Framework\Api\SearchCriteriaBuilder $searchBuilder */
        $this->searchCriteriaBuilder = $this->objectManager->getObject(
            \Magento\Framework\Api\SearchCriteriaBuilder::class,
            ['filterGroupBuilder' => $filterGroupBuilder]
        );
        $this->filterBuilder = $this->objectManager->getObject(\Magento\Framework\Api\FilterBuilder::class);
        $this->customerRepository1 = $this->getMockBuilder(CustomerRepositoryInterface::class)
            ->getMock();
        $expectedSearchCriteria = $this->searchCriteriaBuilder
            ->addFilters(
                [
                    $this->filterBuilder->setField('mobile_number')->setConditionType('eq')
                        ->setValue($customerData)->create(),
                ]
            )->create();
        $result = $this->customerRepository1->expects($this->any())
            ->method('getList')
            ->with($this->equalTo($expectedSearchCriteria))
            ->will($this->returnValue($this->searchResults));

        $filterGroupBuilder = $this->objectManager
            ->getObject(\Magento\Framework\Api\Search\FilterGroupBuilder::class);
        /** @var \Magento\Framework\Api\SearchCriteriaBuilder $searchBuilder */
        $this->searchCriteriaBuilder = $this->objectManager->getObject(
            \Magento\Framework\Api\SearchCriteriaBuilder::class,
            ['filterGroupBuilder' => $filterGroupBuilder]
        );
        $filterGroupBuilder = $this->objectManager
            ->getObject(\Magento\Framework\Api\Search\FilterGroupBuilder::class);
        $filterBuilder = $this->objectManager
            ->getObject(\Magento\Framework\Api\FilterBuilder::class);
        $filter1 = $filterBuilder->setField('mobile_number')
            ->setValue($customerData)
            ->setConditionType("eq")
            ->create();
        $filterGroup = $filterGroupBuilder->addFilter($filter1)->create();
        $search = $this->objectManager
            ->getObject(\Magento\Framework\Api\SearchCriteriaBuilder::class);
        $searchCriteria = $search->setFilterGroups([$filterGroup])->create();
        $customerObj = $this->customerRepository1->getList($searchCriteria);
        $this->assertInstanceOf(\Magento\Framework\Api\SearchCriteriaBuilder::class, $result);
    }
}
