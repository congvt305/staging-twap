<?php

namespace CJ\CustomAmastyReview\Plugin\App\Config;

use Magento\Framework\App\ScopeResolverPool;

/**
 * Class ScopeCodeResolver
 */
class ScopeCodeResolver extends \Amasty\AdvancedReview\Plugin\App\Config\ScopeCodeResolver
{
    /**
     * @var bool
     */
    private $needClean = false;

    /**
     * @var ScopeResolverPool
     */
    private $scopeResolverPool;

    /**
     * @var null|string
     */
    private $scopeType = null;

    /**
     * @var null|string
     */
    private $scopeCode = null;

    /**
     * @param ScopeResolverPool $scopeResolverPool
     */
    public function __construct(ScopeResolverPool $scopeResolverPool)
    {
        parent::__construct($scopeResolverPool);
        $this->scopeResolverPool = $scopeResolverPool;
    }

    /**
     * @param $scopeCodeResolver
     * @param $resolverScopeCode
     * @return \Magento\Framework\App\ScopeInterface|mixed|string
     */
    public function afterResolve($scopeCodeResolver, $resolverScopeCode)
    {
        // Custom here
        if ($resolverScopeCode === 'vn_laneige') {
            return $resolverScopeCode;
        }
        // End custom
        //support old version when clean method not exist
        if ($this->isNeedClean() && $this->scopeType) {
            $scopeResolver = $this->scopeResolverPool->get($this->scopeType);
            $resolverScopeCode = $scopeResolver->getScope($this->scopeCode);
            if ($resolverScopeCode instanceof \Magento\Framework\App\ScopeInterface) {
                $resolverScopeCode = $resolverScopeCode->getCode();
            }
        }

        return $resolverScopeCode;
    }
}
