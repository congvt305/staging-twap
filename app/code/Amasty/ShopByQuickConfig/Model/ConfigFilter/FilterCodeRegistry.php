<?php

declare(strict_types=1);

namespace Amasty\ShopByQuickConfig\Model\ConfigFilter;

class FilterCodeRegistry
{
    /**
     * @var array
     */
    private $customFilterCodes;

    /**
     * @param string[] $customFilterCodes
     */
    public function __construct(array $customFilterCodes = [])
    {
        $this->customFilterCodes = $customFilterCodes;
    }

    /**
     * @return string[]
     */
    public function getCustomFilterCodes(): array
    {
        return $this->customFilterCodes;
    }
}
