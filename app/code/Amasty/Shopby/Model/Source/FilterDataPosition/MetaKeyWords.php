<?php

namespace Amasty\Shopby\Model\Source\FilterDataPosition;

use Amasty\Shopby\Model\Source;

class MetaKeyWords extends Source\AbstractFilterDataPosition implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * @return mixed|void
     */
    protected function _setLabel()
    {
        $this->_label = __('Meta-Keywords');
    }
}
