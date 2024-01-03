<?php
declare(strict_types=1);

namespace CJ\CustomSales\Plugin;

use Magento\SalesRule\Model\Utility;
use CJ\CustomSales\Helper\Data as Helper;

class ApplyExcludeCartRule
{
    protected $helper;

    public function __construct(Helper $helper)
    {
        $this->helper = $helper;
    }

    /**
     * @param Utility $subject
     * @param $result
     * @param $rule
     * @param $address
     * @return false|mixed
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function afterCanProcessRule(
        Utility $subject,
        $result,
        $rule,
        $address
    ) {
        if (!$this->helper->isValidExcludeSkuRule($rule)) {
            return false;
        }
        return $result;
    }
}
