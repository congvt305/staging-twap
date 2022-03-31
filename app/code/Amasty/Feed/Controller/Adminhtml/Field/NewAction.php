<?php

namespace Amasty\Feed\Controller\Adminhtml\Field;

use Amasty\Feed\Controller\Adminhtml\AbstractField;
use Magento\Framework\Controller\ResultFactory;

class NewAction extends AbstractField
{
    public function execute()
    {
        $result = $this->resultFactory->create(ResultFactory::TYPE_FORWARD);

        return $result->forward('edit');
    }
}
