<?php
declare(strict_types=1);

namespace Amasty\CheckoutGraphQl\Model\Utils;

class DDTimeDisplayValueGetter
{
    /**
     * @param int|string|null $time
     * @return string|null
     */
    public function getDisplayValue($time): ?string
    {
        if ($time !== null && $time >= 0) {
            return $time . ':00 - ' . (($time) + 1) . ':00';
        }

        return null;
    }
}
