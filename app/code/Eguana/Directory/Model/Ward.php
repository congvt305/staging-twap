<?php

namespace Eguana\Directory\Model;

/**
 * Class Ward
 *
 */
class Ward extends \Magento\Framework\Model\AbstractModel
{
    /**
     * @return void
     */
    protected function _construct()
    {
        $this->_init(\Eguana\Directory\Model\ResourceModel\Ward::class);
    }

    /**
     * Retrieve Ward Name
     *
     * @return string
     */
    public function getName()
    {
        $name = $this->getData('name');
        if ($name === null) {
            $name = $this->getData('default_name');
        }
        return $name;
    }
}
