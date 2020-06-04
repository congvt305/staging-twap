<?php
/**
 * Created by Eguana Team.
 * User: sonia
 * Date: 6/3/20
 * Time: 1:02 PM
 */

namespace Eguana\Directory\Model;

/**
 * Class City
 *
 * @method string getCode()
 * @method \Eguana\Directory\Model\City setCode(string $value)
 * @method string getRegionId()
 * @method \Eguana\Directory\Model\City setRegionId(string $value)
 */
class City extends \Magento\Framework\Model\AbstractModel
{
    /**
     * @return void
     */
    protected function _construct()
    {
        $this->_init(\Eguana\Directory\Model\ResourceModel\City::class);
    }

    /**
     * Retrieve City Name
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
