<?php

declare(strict_types=1);

namespace Amasty\RewardsGraphQl\Test\GraphQl;

use Magento\Integration\Api\CustomerTokenServiceInterface;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\TestCase\GraphQlAbstract;

class ApplyRewardsTest extends GraphQlAbstract
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
     * @magentoApiDataFixture Amasty_RewardsGraphQl::Test/GraphQl/_files/customer/add_points_by_admin.php
     * @magentoApiDataFixture Amasty_RewardsGraphQl::Test/GraphQl/_files/category_product.php
     * @magentoApiDataFixture Amasty_RewardsGraphQl::Test/GraphQl/_files/customer/create_empty_cart.php
     * @magentoApiDataFixture Amasty_RewardsGraphQl::Test/GraphQl/_files/customer/add_product_to_cart.php
     * @magentoConfigFixture default_store amrewards/points/rate 1
     */
    public function testApplyRewardPointsValid()
    {
        $use_points = 5;
        $query = $this->getMutationUseRewards($use_points);
        $headerMap = $this->getHeader('rewardspoints@amasty.com', 'rewardspassword');
        $response = $this->graphQlMutation($query, [], '', $headerMap);
        $this->assertStringContainsString("You used $use_points point(s).", $response['useRewardPoints']);
    }

    /**
     * @magentoApiDataFixture Amasty_RewardsGraphQl::Test/GraphQl/_files/customer/rewards_customer.php
     * @magentoApiDataFixture Amasty_RewardsGraphQl::Test/GraphQl/_files/customer/add_points_by_admin.php
     * @magentoApiDataFixture Amasty_RewardsGraphQl::Test/GraphQl/_files/category_product.php
     * @magentoApiDataFixture Amasty_RewardsGraphQl::Test/GraphQl/_files/customer/create_empty_cart.php
     * @magentoApiDataFixture Amasty_RewardsGraphQl::Test/GraphQl/_files/customer/add_product_to_cart.php
     * @magentoConfigFixture default_store amrewards/points/rate 1
     */
    public function testApplyRewardPointsTooMuch()
    {
        $use_points = 11;
        $query = $this->getMutationUseRewards($use_points);
        $headerMap = $this->getHeader('rewardspoints@amasty.com', 'rewardspassword');
        $response = $this->graphQlMutation($query, [], '', $headerMap);
        $this->assertStringContainsString("Too much point(s) used.", $response['useRewardPoints']);
    }

    /**
     * @magentoApiDataFixture Amasty_RewardsGraphQl::Test/GraphQl/_files/customer/rewards_customer.php
     * @magentoApiDataFixture Amasty_RewardsGraphQl::Test/GraphQl/_files/customer/add_points_by_admin.php
     * @magentoApiDataFixture Amasty_RewardsGraphQl::Test/GraphQl/_files/category_product.php
     * @magentoApiDataFixture Amasty_RewardsGraphQl::Test/GraphQl/_files/customer/create_empty_cart.php
     * @magentoApiDataFixture Amasty_RewardsGraphQl::Test/GraphQl/_files/customer/add_product_to_cart.php
     * @magentoConfigFixture default_store amrewards/points/rate 1
     */
    public function testApplyRewardPointsRemoved()
    {
        $use_points = 0;
        $query = $this->getMutationUseRewards($use_points);
        $headerMap = $this->getHeader('rewardspoints@amasty.com', 'rewardspassword');
        $response = $this->graphQlMutation($query, [], '', $headerMap);
        $this->assertStringContainsString("Removed.", $response['useRewardPoints']);
    }

    /**
     * @magentoApiDataFixture Amasty_RewardsGraphQl::Test/GraphQl/_files/customer/rewards_customer.php
     * @magentoApiDataFixture Amasty_RewardsGraphQl::Test/GraphQl/_files/customer/add_points_by_admin.php
     * @magentoApiDataFixture Amasty_RewardsGraphQl::Test/GraphQl/_files/category_product.php
     * @magentoApiDataFixture Amasty_RewardsGraphQl::Test/GraphQl/_files/customer/create_empty_cart.php
     * @magentoApiDataFixture Amasty_RewardsGraphQl::Test/GraphQl/_files/customer/add_product_to_cart.php
     * @magentoConfigFixture default_store amrewards/points/rate 1
     */
    public function testApplyRewardPointsInvalid()
    {
        $use_points = -1;
        $query = $this->getMutationUseRewards($use_points);
        $headerMap = $this->getHeader('rewardspoints@amasty.com', 'rewardspassword');
        $response = $this->graphQlMutation($query, [], '', $headerMap);
        $this->assertStringContainsString("Points \"$use_points\" not valid.", $response['useRewardPoints']);
    }

    /**
     * @param float $points_amount
     *
     * @return string
     */
    private function getMutationUseRewards(float $points_amount): string
    {
        $query = <<<QUERY
mutation {
  useRewardPoints(points: $points_amount)
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
}
