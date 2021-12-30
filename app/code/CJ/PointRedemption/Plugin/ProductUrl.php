<?php

namespace CJ\PointRedemption\Plugin;

use CJ\PointRedemption\Setup\Patch\Data\AddIsMembershipCategoryAttribute as MembershipCategory;
use Magento\Framework\App\RequestInterface;

class ProductUrl
{
    /**
     * @var RequestInterface
     */
    protected $request;

    public function __construct(RequestInterface $request)
    {
        $this->request = $request;
    }

    /**
     * Add query ?point=1 to product url in membership category
     * @param $subject
     * @param $product
     * @param $params
     * @return array
     */
    public function beforeGetUrl($subject, $product, $params = [])
    {
        $isMembershipCategory = $this->isMembershipCategory($product);
        $isPointRedemptionRequest = $this->isPointRedemptionRequest();
        if ($isMembershipCategory || $isPointRedemptionRequest) {
            if (!isset($params['_query'])) {
                $params['_query'] = [];
            }
            $params['_query']['point'] = 'true';
        }

        return [$product, $params];
    }

    /**
     * @param $product
     * @return false|mixed
     */
    private function isMembershipCategory($product)
    {
        $currentCategory = $product->getCategory();
        return $currentCategory
            ? $currentCategory->getData(MembershipCategory::CATEGORY_ATTRIBUTE_CODE_IS_MEMBERSHIP)
            : false;
    }

    /**
     * @return false|mixed
     */
    private function isPointRedemptionRequest()
    {
        $postData = $this->request->getPostValue();
        return $postData['is_point_redemption'] ?? false;
    }
}
