<?php

declare(strict_types=1);

namespace Amasty\RewardsGraphQl\Test\GraphQl;

use Magento\Integration\Api\CustomerTokenServiceInterface;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\TestCase\GraphQlAbstract;

class GetRewardsBalanceTest extends GraphQlAbstract
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
     * @magentoApiDataFixture Amasty_RewardsGraphQl::Test/GraphQl/_files/rules/rewards_rule_order_complete.php
     * @magentoApiDataFixture Amasty_RewardsGraphQl::Test/GraphQl/_files/rules/rewards_rule_every_spent.php
     * @magentoApiDataFixture Amasty_RewardsGraphQl::Test/GraphQl/_files/customer/rewards_customer.php
     * @magentoApiDataFixture Amasty_RewardsGraphQl::Test/GraphQl/_files/customer/add_points_by_admin.php
     * @magentoApiDataFixture Amasty_RewardsGraphQl::Test/GraphQl/_files/customer/add_points_by_rule_order_complete.php
     * @magentoApiDataFixture Amasty_RewardsGraphQl::Test/GraphQl/_files/customer/add_points_by_rule_every_spent.php
     */
    public function testGetCustomerRewardsBalance()
    {
        $query = $this->getQueryBalance();
        $headerMap = $this->getHeader('rewardspoints@amasty.com', 'rewardspassword');
        $response = $this->graphQlQuery($query, [], '', $headerMap);
        $this->assertEquals('35', $response['rewards']['balance']);
    }

    /**
     * @magentoApiDataFixture Amasty_RewardsGraphQl::Test/GraphQl/_files/customer/rewards_customer.php
     */
    public function testGetCustomerRewardsZeroBalance()
    {
        $query = $this->getQueryBalance();
        $headerMap = $this->getHeader('rewardspoints@amasty.com', 'rewardspassword');
        $response = $this->graphQlQuery($query, [], '', $headerMap);
        $this->assertEquals('0', $response['rewards']['balance']);
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
     * @return string
     */
    private function getQueryBalance(): string
    {
        $query = <<<QUERY
{
    rewards {
        balance
    }
}
QUERY;
        return $query;
    }
}
