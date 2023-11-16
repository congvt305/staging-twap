<?php

declare(strict_types=1);

namespace Amasty\Shopby\Model\Layer;

use Amasty\Shopby\Model\Request;

class IsBrandPage
{
    public const AMBRAND_INDEX_INDEX = 'ambrand_index_index';

    /**
     * @var Request
     */
    private $request;

    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    public function execute(): bool
    {
        return $this->request->getFullActionName() === self::AMBRAND_INDEX_INDEX;
    }
}
