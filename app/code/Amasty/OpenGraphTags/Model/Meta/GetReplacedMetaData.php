<?php

declare(strict_types=1);

namespace Amasty\OpenGraphTags\Model\Meta;

use Amasty\OpenGraphTags\Model\Di\Wrapper;

class GetReplacedMetaData
{
    /**
     * @var \Amasty\Meta\Model\Meta\ReplacedData
     */
    public $replacedMetaData;

    public function __construct(
        Wrapper $replacedMetaData
    ) {
        $this->replacedMetaData = $replacedMetaData;
    }

    /**
     * Getting replaced meta data from Amasty_Meta module
     *
     * @param string $identifier
     * @return string|null
     */
    public function execute(string $identifier): ?string
    {
        $value = null;

        if ($replacedData = $this->replacedMetaData->getReplacedData()) {
            $value = $replacedData[$identifier] ?? null;
        }

        return $value;
    }
}
