<?php

declare(strict_types=1);

namespace Amasty\RewardsGraphQl\Test\GraphQl;

use Magento\TestFramework\TestCase\GraphQlAbstract;

class GetGuestRewardsTest extends GraphQlAbstract
{
    public const PRODUCT_PAGE = 0;
    public const CART_PAGE = 1;
    public const CHECKOUT_PAGE = 2;

    /**
     * @magentoApiDataFixture Amasty_RewardsGraphQl::Test/GraphQl/_files/category_product.php
     * @magentoApiDataFixture Amasty_RewardsGraphQl::Test/GraphQl/_files/rules/rewards_rule_registration.php
     * @magentoConfigFixture default_store amrewards/highlight/guest 1
     * @magentoConfigFixture default_store amrewards/highlight/product 1
     * @magentoConfigFixture default_store amrewards/highlight/color #2577cf
     */
    public function testGetGuestProductPageHighlight()
    {
        $query = $this->getQuery(self::PRODUCT_PAGE);
        $response = $this->graphQlQuery($query);
        $this->assertValidGuestRewards($response, '#2577cf');
    }

    /**
     * @magentoApiDataFixture Amasty_RewardsGraphQl::Test/GraphQl/_files/rules/rewards_rule_registration.php
     * @magentoApiDataFixture Amasty_RewardsGraphQl::Test/GraphQl/_files/category_product.php
     * @magentoApiDataFixture Amasty_RewardsGraphQl::Test/GraphQl/_files/guest/create_empty_cart.php
     * @magentoApiDataFixture Amasty_RewardsGraphQl::Test/GraphQl/_files/guest/add_product_to_cart.php
     * @magentoConfigFixture default_store amrewards/highlight/guest 1
     * @magentoConfigFixture default_store amrewards/highlight/cart 1
     * @magentoConfigFixture default_store amrewards/highlight/color #bced37
     */
    public function testGetGuestCartPageHighlight()
    {
        $query = $this->getQuery(self::CART_PAGE);
        $response = $this->graphQlQuery($query);
        $this->assertValidGuestRewards($response, '#bced37');
    }

    /**
     * @magentoApiDataFixture Amasty_RewardsGraphQl::Test/GraphQl/_files/rules/rewards_rule_registration.php
     * @magentoApiDataFixture Amasty_RewardsGraphQl::Test/GraphQl/_files/category_product.php
     * @magentoApiDataFixture Amasty_RewardsGraphQl::Test/GraphQl/_files/guest/create_empty_cart.php
     * @magentoApiDataFixture Amasty_RewardsGraphQl::Test/GraphQl/_files/guest/add_product_to_cart.php
     * @magentoConfigFixture default_store amrewards/highlight/guest 1
     * @magentoConfigFixture default_store amrewards/highlight/checkout 1
     * @magentoConfigFixture default_store amrewards/highlight/color #db8a44
     */
    public function testGetGuestCheckoutPageHighlight()
    {
        $query = $this->getQuery(self::CHECKOUT_PAGE);
        $response = $this->graphQlQuery($query);
        $this->assertValidGuestRewards($response, '#db8a44');
    }

    /**
     * @magentoApiDataFixture Amasty_RewardsGraphQl::Test/GraphQl/_files/category_product.php
     * @magentoApiDataFixture Amasty_RewardsGraphQl::Test/GraphQl/_files/rules/rewards_rule_registration.php
     * @magentoConfigFixture default_store amrewards/highlight/guest 0
     * @magentoConfigFixture default_store amrewards/highlight/product 1
     * @magentoConfigFixture default_store amrewards/highlight/cart 1
     * @magentoConfigFixture default_store amrewards/highlight/checkout 1
     * @magentoConfigFixture default_store amrewards/highlight/color #000000
     */
    public function testDisableGuestHighlight()
    {
        $query = $this->getQuery(self::PRODUCT_PAGE);
        $response = $this->graphQlQuery($query);
        $this->assertNull($response['guestRewards']);
    }

    /**
     * @param int $page
     *
     * @return string
     */
    public function getQuery(int $page): string
    {
        $query = <<<QUERY
{
    guestRewards (page:$page) {
        visible
        caption_color
        caption_text
    }
}
QUERY;
        return $query;
    }

    /**
     * @param $response
     * @param $color
     *
     * @return void
     */
    public function assertValidGuestRewards($response, $color)
    {
        $this->assertTrue($response['guestRewards']['visible']);
        $this->assertEquals('15 Reward Points', $response['guestRewards']['caption_text']);
        $this->assertEquals($color, $response['guestRewards']['caption_color']);
    }
}
