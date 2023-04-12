<?php
declare(strict_types=1);

namespace CJ\Ipay88\Gateway\Config;

/**
 * Class Config
 */
class Config extends \Ipay88\Payment\Gateway\Config\Config
{
    /**
     * @param $field
     * @param $storeId
     * @return mixed|string|null
     */
    public function getValue($field, $storeId = null)
    {
        $value = parent::getValue($field, $storeId);
        if (is_null($value)) {
            $value = '';
        }

        return $value;
    }
}
