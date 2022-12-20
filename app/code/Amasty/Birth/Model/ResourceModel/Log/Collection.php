<?php
/**
* @author Amasty Team
* @copyright Copyright (c) 2022 Amasty (https://www.amasty.com)
* @package Special Occasion Coupons for Magento 2
*/
namespace Amasty\Birth\Model\ResourceModel\Log;

class Collection
    extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    public function _construct()
    {
        $this->_init('Amasty\Birth\Model\Log',
            'Amasty\Birth\Model\ResourceModel\Log'
        );
    }
}
