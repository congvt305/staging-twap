<?php

declare(strict_types=1);

namespace Amasty\RewardsGraphQl\Test\GraphQl;

use Magento\Integration\Api\CustomerTokenServiceInterface;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\TestCase\GraphQlAbstract;

class GetRewardsHistoryTest extends GraphQlAbstract
{

    /**
     * @var CustomerTokenServiceInterface
     */
    private $customerTokenService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->customerTokenService = Bootstrap::getObjectManager()->get(CustomerTokenServiceInterface::class);
    }

    /**
     * @magentoApiDataFixture Amasty_RewardsGraphQl::Test/GraphQl/_files/customer/rewards_customer.php
     */
    public function testGetCustomerRewardsHistoryNotItems()
    {
        $query = $this->getQueryHistory();
        $headerMap = $this->getHeader('rewardspoints@amasty.com', 'rewardspassword');
        $response = $this->graphQlQuery($query, [], '', $headerMap);
        $this->assertEquals('0', $response['rewards']['history']['total_count']);
        $this->assertIsArray($response['rewards']['history']['items']);
        $this->assertEmpty($response['rewards']['history']['items']);
    }

    /**
     * @magentoApiDataFixture Amasty_RewardsGraphQl::Test/GraphQl/_files/rules/rewards_rule_newsletter.php
     * @magentoApiDataFixture Amasty_RewardsGraphQl::Test/GraphQl/_files/customer/rewards_customer.php
     * @magentoApiDataFixture Amasty_RewardsGraphQl::Test/GraphQl/_files/customer/add_points_by_rule_newsletter.php
     */
    public function testGetCustomerRewardsHistoryRuleItem()
    {
        $query = $this->getQueryHistory();
        $headerMap = $this->getHeader('rewardspoints@amasty.com', 'rewardspassword');
        $response = $this->graphQlQuery($query, [], '', $headerMap);
        $this->assertEquals('1', $response['rewards']['history']['total_count']);
        $this->assertIsArray($response['rewards']['history']['items']);
        $this->assertNotEmpty($response['rewards']['history']['items']);
        $this->assertItem($response['rewards']['history']['items']['0'], 5, 'subscription', 5, 10);
    }

    /**
     * @magentoApiDataFixture Amasty_RewardsGraphQl::Test/GraphQl/_files/customer/rewards_customer.php
     * @magentoApiDataFixture Amasty_RewardsGraphQl::Test/GraphQl/_files/customer/add_points_by_admin.php
     */
    public function testGetCustomerRewardsHistoryAdminItem()
    {
        $query = $this->getQueryHistory();
        $headerMap = $this->getHeader('rewardspoints@amasty.com', 'rewardspassword');
        $response = $this->graphQlQuery($query, [], '', $headerMap);
        $this->assertEquals('1', $response['rewards']['history']['total_count']);
        $this->assertIsArray($response['rewards']['history']['items']);
        $this->assertNotEmpty($response['rewards']['history']['items']);
        $this->assertItem($response['rewards']['history']['items']['0'], 10, 'Admin Point Change', 10);
    }

    /**
     * @magentoApiDataFixture Amasty_RewardsGraphQl::Test/GraphQl/_files/rules/rewards_rule_newsletter.php
     * @magentoApiDataFixture Amasty_RewardsGraphQl::Test/GraphQl/_files/rules/rewards_rule_registration.php
     * @magentoApiDataFixture Amasty_RewardsGraphQl::Test/GraphQl/_files/customer/rewards_customer.php
     * @magentoApiDataFixture Amasty_RewardsGraphQl::Test/GraphQl/_files/customer/add_points_by_admin.php
     * @magentoApiDataFixture Amasty_RewardsGraphQl::Test/GraphQl/_files/customer/add_points_by_rule_registration.php
     * @magentoApiDataFixture Amasty_RewardsGraphQl::Test/GraphQl/_files/customer/add_points_by_rule_newsletter.php
     * @magentoApiDataFixture Amasty_RewardsGraphQl::Test/GraphQl/_files/customer/deduct_points.php
     */
    public function testGetCustomerRewardsHistoryItems()
    {
        $query = $this->getQueryHistory();
        $headerMap = $this->getHeader('rewardspoints@amasty.com', 'rewardspassword');
        $response = $this->graphQlQuery($query, [], '', $headerMap);
        $this->assertEquals('4', $response['rewards']['history']['total_count']);
        $this->assertIsArray($response['rewards']['history']['items']);
        $this->assertNotEmpty($response['rewards']['history']['items']);
        $this->assertItem($response['rewards']['history']['items']['0'], -3.25, 'refund', 26.75);
        $this->assertItem($response['rewards']['history']['items']['1'], 5, 'subscription', 30, 10);
        $this->assertItem($response['rewards']['history']['items']['2'], 15, 'registration', 25);
        $this->assertItem($response['rewards']['history']['items']['3'], 10, 'Admin Point Change', 10);
    }

    /**
     * @return string
     */
    private function getQueryHistory(): string
    {
        $query = <<<QUERY
{
    rewards {
        history {
            total_count
            items (pageSize:10, currentPage:1) {
                action_date
                amount
                action
                points_left
                expiration_date
            }
        }
    }
}
QUERY;
        return $query;
    }

    /**
     * @param string $userName
     * @param string $password
     *
     * @return string[]
     */
    private function getHeader(string $userName, string $password): array
    {
        $customerToken = $this->customerTokenService->createCustomerAccessToken($userName, $password);
        return ['Authorization' => 'Bearer ' . $customerToken];
    }

    /**
     * @param array $item
     * @param float $amount
     * @param string $action
     * @param float $points_left
     * @param int $exp_period
     *
     * @return void
     */
    private function assertItem(array $item, float $amount, string $action, float $points_left, int $exp_period = 0)
    {
        $date_hour = date("Y-m-d H");
        $exp_date = date('Y-m-d', strtotime('+ ' . $exp_period . ' days'));

        $this->assertStringContainsString($date_hour, $item['action_date']);
        $this->assertEquals($amount, $item['amount']);
        $this->assertEquals($action, $item['action']);
        $this->assertEquals($points_left, $item['points_left']);
        if ($exp_period == 0) {
            $this->assertNull($item['expiration_date']);
        } else {
            $this->assertStringContainsString($exp_date, $item['expiration_date']);
        }
    }
}
