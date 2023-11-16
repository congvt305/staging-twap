<?php

declare(strict_types=1);

namespace Amasty\RewardsGraphQl\Test\GraphQl;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Integration\Api\CustomerTokenServiceInterface;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\TestCase\GraphQlAbstract;

class GetRewardsHighlightTest extends GraphQlAbstract
{
    /**
     * @var CustomerTokenServiceInterface
     */
    private $customerTokenService;

    /**
     * @var ProductRepositoryInterface
     */
    protected $productRepository;

    protected function setUp(): void
    {
        parent::setUp();

        $this->customerTokenService = Bootstrap::getObjectManager()->get(CustomerTokenServiceInterface::class);
        $this->productRepository = Bootstrap::getObjectManager()->get(ProductRepositoryInterface::class);
    }

    /**
     * @magentoApiDataFixture Amasty_RewardsGraphQl::Test/GraphQl/_files/rules/rewards_rule_order_complete.php
     * @magentoApiDataFixture Amasty_RewardsGraphQl::Test/GraphQl/_files/customer/rewards_customer.php
     * @magentoApiDataFixture Amasty_RewardsGraphQl::Test/GraphQl/_files/category_product.php
     * @magentoConfigFixture default_store amrewards/highlight/product 1
     * @magentoConfigFixture default_store amrewards/highlight/category 1
     * @magentoConfigFixture default_store amrewards/highlight/color #2577cf
     */
    public function testGetRewardsHighlightProductCategoryOrderRule()
    {
        $product = $this->productRepository->get('rew333SimPro');
        $productId = $product->getId();

        $queryProduct = $this->getQueryHighlightProduct("$productId");
        $queryCategory = $this->getQueryHighlightCategory("$productId");
        $headerMap = $this->getHeader('rewardspoints@amasty.com', 'rewardspassword');
        $responseProduct = $this->graphQlQuery($queryProduct, [], '', $headerMap);
        $responseCategory = $this->graphQlQuery($queryCategory, [], '', $headerMap);
        $this->assertHighlight($responseProduct['rewards']['highlight']['product'], false, '#2577cf', '0 points');
        $this->assertHighlight($responseCategory['rewards']['highlight']['category'], false, '#2577cf', '0 points');
    }

    /**
     * @magentoApiDataFixture Amasty_RewardsGraphQl::Test/GraphQl/_files/rules/rewards_rule_order_complete.php
     * @magentoApiDataFixture Amasty_RewardsGraphQl::Test/GraphQl/_files/rules/rewards_rule_every_spent_highlight.php
     * @magentoApiDataFixture Amasty_RewardsGraphQl::Test/GraphQl/_files/customer/rewards_customer.php
     * @magentoApiDataFixture Amasty_RewardsGraphQl::Test/GraphQl/_files/category_product.php
     * @magentoConfigFixture default_store amrewards/highlight/product 1
     * @magentoConfigFixture default_store amrewards/highlight/category 1
     * @magentoConfigFixture default_store amrewards/highlight/color #4b11b8
     */
    public function testGetRewardsHighlightProductCategorySpentRule()
    {
        $product = $this->productRepository->get('rew333SimPro');
        $productId = $product->getId();

        $queryProduct = $this->getQueryHighlightProduct("$productId");
        $queryCategory = $this->getQueryHighlightCategory("$productId");
        $headerMap = $this->getHeader('rewardspoints@amasty.com', 'rewardspassword');
        $responseProduct = $this->graphQlQuery($queryProduct, [], '', $headerMap);
        $responseCategory = $this->graphQlQuery($queryCategory, [], '', $headerMap);
        $this->assertHighlight($responseProduct['rewards']['highlight']['product'], true, '#4b11b8', '10 points');
        $this->assertHighlight($responseCategory['rewards']['highlight']['category'], true, '#4b11b8', '10 points');
    }

    /**
     * @magentoApiDataFixture Amasty_RewardsGraphQl::Test/GraphQl/_files/rules/rewards_rule_every_spent_highlight.php
     * @magentoApiDataFixture Amasty_RewardsGraphQl::Test/GraphQl/_files/customer/rewards_customer.php
     * @magentoApiDataFixture Amasty_RewardsGraphQl::Test/GraphQl/_files/category_product.php
     * @magentoConfigFixture default_store amrewards/highlight/product 1
     * @magentoConfigFixture default_store amrewards/highlight/category 1
     * @magentoConfigFixture default_store amrewards/highlight/color #0e7d15
     */
    public function testGetRewardsHighlightProductCategoryTwoRules()
    {
        $product = $this->productRepository->get('rew333SimPro');
        $productId = $product->getId();

        $queryProduct = $this->getQueryHighlightProduct("$productId");
        $queryCategory = $this->getQueryHighlightCategory("$productId");
        $headerMap = $this->getHeader('rewardspoints@amasty.com', 'rewardspassword');
        $responseProduct = $this->graphQlQuery($queryProduct, [], '', $headerMap);
        $responseCategory = $this->graphQlQuery($queryCategory, [], '', $headerMap);
        $this->assertHighlight($responseProduct['rewards']['highlight']['product'], true, '#0e7d15', '10 points');
        $this->assertHighlight($responseCategory['rewards']['highlight']['category'], true, '#0e7d15', '10 points');
    }

    /**
     * @magentoApiDataFixture Amasty_RewardsGraphQl::Test/GraphQl/_files/customer/rewards_customer.php
     * @magentoApiDataFixture Amasty_RewardsGraphQl::Test/GraphQl/_files/category_product.php
     * @magentoConfigFixture default_store amrewards/highlight/product 1
     * @magentoConfigFixture default_store amrewards/highlight/category 1
     * @magentoConfigFixture default_store amrewards/highlight/color #ffffff
     */
    public function testGetRewardsHighlightProductCategoryWithoutRules()
    {
        $product = $this->productRepository->get('rew333SimPro');
        $productId = $product->getId();

        $queryProduct = $this->getQueryHighlightProduct("$productId");
        $queryCategory = $this->getQueryHighlightCategory("$productId");
        $headerMap = $this->getHeader('rewardspoints@amasty.com', 'rewardspassword');
        $responseProduct = $this->graphQlQuery($queryProduct, [], '', $headerMap);
        $responseCategory = $this->graphQlQuery($queryCategory, [], '', $headerMap);
        $this->assertHighlight($responseProduct['rewards']['highlight']['product'], false, '#ffffff', '0 points');
        $this->assertHighlight($responseCategory['rewards']['highlight']['category'], false, '#ffffff', '0 points');
    }

    /**
     * @magentoApiDataFixture Amasty_RewardsGraphQl::Test/GraphQl/_files/rules/rewards_rule_order_complete.php
     * @magentoApiDataFixture Amasty_RewardsGraphQl::Test/GraphQl/_files/customer/rewards_customer.php
     * @magentoApiDataFixture Amasty_RewardsGraphQl::Test/GraphQl/_files/category_product.php
     * @magentoApiDataFixture Amasty_RewardsGraphQl::Test/GraphQl/_files/customer/create_empty_cart.php
     * @magentoApiDataFixture Amasty_RewardsGraphQl::Test/GraphQl/_files/customer/add_product_to_cart.php
     * @magentoConfigFixture default_store amrewards/highlight/cart 1
     * @magentoConfigFixture default_store amrewards/highlight/color #b56f24
     */
    public function testGetRewardsHighlightCartOrderRule()
    {
        $query = $this->getQueryHighlightCart();
        $headerMap = $this->getHeader('rewardspoints@amasty.com', 'rewardspassword');
        $response = $this->graphQlQuery($query, [], '', $headerMap);
        $this->assertHighlight($response['rewards']['highlight']['cart'], true, '#b56f24', '10 Reward Points');
    }

    /**
     * @magentoApiDataFixture Amasty_RewardsGraphQl::Test/GraphQl/_files/rules/rewards_rule_every_spent_highlight.php
     * @magentoApiDataFixture Amasty_RewardsGraphQl::Test/GraphQl/_files/customer/rewards_customer.php
     * @magentoApiDataFixture Amasty_RewardsGraphQl::Test/GraphQl/_files/category_product.php
     * @magentoApiDataFixture Amasty_RewardsGraphQl::Test/GraphQl/_files/customer/create_empty_cart.php
     * @magentoApiDataFixture Amasty_RewardsGraphQl::Test/GraphQl/_files/customer/add_product_to_cart.php
     * @magentoConfigFixture default_store amrewards/highlight/cart 1
     * @magentoConfigFixture default_store amrewards/highlight/color #49118a
     */
    public function testGetRewardsHighlightCartSpentRule()
    {
        $query = $this->getQueryHighlightCart();
        $headerMap = $this->getHeader('rewardspoints@amasty.com', 'rewardspassword');
        $response = $this->graphQlQuery($query, [], '', $headerMap);
        $this->assertHighlight($response['rewards']['highlight']['cart'], true, '#49118a', '10 Reward Points');
    }

    /**
     * @magentoApiDataFixture Amasty_RewardsGraphQl::Test/GraphQl/_files/rules/rewards_rule_order_complete.php
     * @magentoApiDataFixture Amasty_RewardsGraphQl::Test/GraphQl/_files/rules/rewards_rule_every_spent_highlight.php
     * @magentoApiDataFixture Amasty_RewardsGraphQl::Test/GraphQl/_files/customer/rewards_customer.php
     * @magentoApiDataFixture Amasty_RewardsGraphQl::Test/GraphQl/_files/category_product.php
     * @magentoApiDataFixture Amasty_RewardsGraphQl::Test/GraphQl/_files/customer/create_empty_cart.php
     * @magentoApiDataFixture Amasty_RewardsGraphQl::Test/GraphQl/_files/customer/add_product_to_cart.php
     * @magentoConfigFixture default_store amrewards/highlight/cart 1
     * @magentoConfigFixture default_store amrewards/highlight/color #000000
     */
    public function testGetRewardsHighlightCartTwoRules()
    {
        $query = $this->getQueryHighlightCart();
        $headerMap = $this->getHeader('rewardspoints@amasty.com', 'rewardspassword');
        $response = $this->graphQlQuery($query, [], '', $headerMap);
        $this->assertHighlight($response['rewards']['highlight']['cart'], true, '#000000', '20 Reward Points');
    }

    /**
     * @magentoApiDataFixture Amasty_RewardsGraphQl::Test/GraphQl/_files/customer/rewards_customer.php
     * @magentoApiDataFixture Amasty_RewardsGraphQl::Test/GraphQl/_files/category_product.php
     * @magentoApiDataFixture Amasty_RewardsGraphQl::Test/GraphQl/_files/customer/create_empty_cart.php
     * @magentoApiDataFixture Amasty_RewardsGraphQl::Test/GraphQl/_files/customer/add_product_to_cart.php
     * @magentoConfigFixture default_store amrewards/highlight/cart 1
     * @magentoConfigFixture default_store amrewards/highlight/color #000000
     */
    public function testGetRewardsHighlightCartWithoutRules()
    {
        $query = $this->getQueryHighlightCart();
        $headerMap = $this->getHeader('rewardspoints@amasty.com', 'rewardspassword');
        $response = $this->graphQlQuery($query, [], '', $headerMap);
        $this->assertHighlight($response['rewards']['highlight']['cart'], false, '#000000', '0 Reward Points');
    }

    /**
     * @param array $response
     * @param bool $visible
     * @param string $color
     * @param string $text
     *
     * @return void
     */
    public function assertHighlight(array $response, bool $visible, string $color, string $text)
    {
        if ($visible) {
            $this->assertTrue($response['visible']);
        } else {
            $this->assertFalse($response['visible']);
        }
        $this->assertEquals($color, $response['caption_color']);
        $this->assertEquals($text, $response['caption_text']);
    }

    /**
     * @param string $product_id
     *
     * @return string
     */
    private function getQueryHighlightProduct(string $product_id): string
    {
        $query = <<<QUERY
{
    rewards {
        highlight {
            product (productId:$product_id) {
                visible
                caption_color
                caption_text
            }
        }
    }
}
QUERY;
        return $query;
    }

    /**
     * @param string $product_id
     *
     * @return string
     */
    private function getQueryHighlightCategory(string $product_id): string
    {
        $query = <<<QUERY
{
    rewards {
        highlight {
            category (productId:$product_id) {
                visible
                caption_color
                caption_text
            }
        }
    }
}
QUERY;
        return $query;
    }

    /**
     * @return string
     */
    private function getQueryHighlightCart(): string
    {
        $query = <<<QUERY
{
    rewards {
        highlight {
            cart {
                visible
                caption_color
                caption_text
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
}
