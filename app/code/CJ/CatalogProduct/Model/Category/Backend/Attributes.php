<?php
declare(strict_types=1);

namespace CJ\CatalogProduct\Model\Category\Backend;

use Magento\Eav\Model\Entity\Attribute\Backend\AbstractBackend;

/**
 * Class Attributes
 */
class Attributes extends AbstractBackend
{
    /**
     * Before Attribute Save Process
     *
     * @param \Magento\Framework\DataObject $object
     * @return $this
     */
    public function beforeSave($object)
    {
        $attributeCode = $this->getAttribute()->getName();
        if ('visible_filter_attributes' == $attributeCode) {
            $data = $object->getData($attributeCode);
            if (!is_array($data)) {
                $data = [];
            }
            $object->setData($attributeCode, implode(',', $data) ?: null);
        }

        return $this;
    }

    /**
     * After Load Attribute Process
     *
     * @param \Magento\Framework\DataObject $object
     * @return $this
     */
    public function afterLoad($object)
    {
        $attributeCode = $this->getAttribute()->getName();
        if ('visible_filter_attributes' == $attributeCode) {
            $data = $object->getData($attributeCode);
            if ($data) {
                if (!is_array($data)) {
                    $object->setData($attributeCode, explode(',', $data));
                } else {
                    $object->setData($attributeCode, $data);
                }
            }
        }

        return $this;
    }
}
