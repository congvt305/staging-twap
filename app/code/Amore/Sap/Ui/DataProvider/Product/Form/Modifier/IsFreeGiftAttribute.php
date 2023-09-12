<?php
declare(strict_types=1);

namespace Amore\Sap\Ui\DataProvider\Product\Form\Modifier;

use Magento\Catalog\Ui\DataProvider\Product\Form\Modifier\AbstractModifier;
use Magento\Framework\Stdlib\ArrayManager;
class IsFreeGiftAttribute extends AbstractModifier
{
    /**
     * @var ArrayManager
     */
    private $arrayManager;
    /**
     * @param $arrayManager
     */
    public function __construct(
        ArrayManager $arrayManager
    ) {
        $this->arrayManager = $arrayManager;
    }
    /**
     * modifyData
     *
     * @param array $data
     * @return array
     */
    public function modifyData(array $data)
    {
        return $data;
    }
    /**
     * modifyMeta
     *
     * @param array $data
     * @return array
     */
    public function modifyMeta(array $meta)
    {
        $attribute = 'is_free_gift';
        $path = $this->arrayManager->findPath($attribute, $meta, null, 'children');

        $meta = $this->arrayManager->set(
            "{$path}/arguments/data/config/disabled",
            $meta,
            true
        );
        $meta = $this->arrayManager->set(
            "{$path}/arguments/data/config/service",
            $meta,
            false
        );
        return $meta;
    }
}
