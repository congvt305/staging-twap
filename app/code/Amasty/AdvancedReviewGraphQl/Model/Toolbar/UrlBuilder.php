<?php

declare(strict_types=1);

namespace Amasty\AdvancedReviewGraphQl\Model\Toolbar;

use Amasty\AdvancedReview\Helper\Config as ConfigHelper;
use Amasty\AdvancedReview\Model\Sources\Filter;
use Magento\Framework\App\RequestInterface;

class UrlBuilder extends \Amasty\AdvancedReview\Model\Toolbar\UrlBuilder
{
    public const GRAPHQL_PARAMS = [
        'amreviewDir' => self::DIRECTION_PARAM_NAME,
        'amreviewSort' => self::SORT_PARAM_NAME,
        'stars' => self::STARS_PARAM_NAME,
        'withImages' => Filter::WITH_IMAGES,
        'verifiedBuyer' => Filter::VERIFIED,
        'isRecommended' => Filter::RECOMMENDED
    ];

    /**
     * @var RequestInterface
     */
    private $request;

    /**
     * @var ConfigHelper
     */
    private $configHelper;

    /**
     * @var array
     */
    private $params = [];

    public function __construct(
        RequestInterface $request,
        ConfigHelper $configHelper
    ) {
        $this->request = $request;
        $this->configHelper = $configHelper;
    }

    /**
     * @return array
     */
    public function collectParams()
    {
        $result = [];

        $defaultSorting = $this->configHelper->getSortOrder();
        if ($defaultSorting && is_array($defaultSorting)) {
            $defaultSorting = array_keys($defaultSorting);
            $first = array_shift($defaultSorting);
            $result[static::SORT_PARAM_NAME] = $first;
        }

        foreach ($this->getParams() as $param => $value) {
            if (in_array($param, $this->availableParams)) {
                $result[$param] = $value;
            }
        }

        return $result;
    }

    public function setParams(array $args): void
    {
        $paginationParams = [];
        foreach ($args as $param => $value) {
            if (isset(static::GRAPHQL_PARAMS[$param])) {
                $paginationParams[static::GRAPHQL_PARAMS[$param]] = $value;
            }
        }
        $this->params = $paginationParams;
    }

    public function getParams(): array
    {
        return $this->params;
    }
}
