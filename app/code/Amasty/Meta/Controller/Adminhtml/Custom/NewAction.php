<?php
namespace Amasty\Meta\Controller\Adminhtml\Custom;
use Magento\Framework\App\ResponseInterface;

class NewAction extends \Amasty\Meta\Controller\Adminhtml\Custom
{

    /**
     * Dispatch request
     *
     * @return \Magento\Framework\Controller\ResultInterface|ResponseInterface
     * @throws \Magento\Framework\Exception\NotFoundException
     */
    public function execute()
    {
        $this->_forward('edit');
    }
}