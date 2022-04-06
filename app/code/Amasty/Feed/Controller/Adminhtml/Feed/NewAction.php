<?php

namespace Amasty\Feed\Controller\Adminhtml\Feed;

use Amasty\Feed\Controller\Adminhtml\AbstractFeed;
use Magento\Framework\Controller\ResultFactory;

class NewAction extends AbstractFeed
{
    public function execute()
    {
        $result = $this->resultFactory->create(ResultFactory::TYPE_FORWARD);

        return $result->forward('edit');
    }
}
