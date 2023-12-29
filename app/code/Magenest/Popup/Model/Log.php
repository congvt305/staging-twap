<?php
namespace Magenest\Popup\Model;

use Magento\Framework\Model\AbstractModel;

class Log extends AbstractModel
{
    /**
     * @inheritDoc
     */
    public function _construct()
    {
        $this->_init(ResourceModel\Log::class);
    }
}
