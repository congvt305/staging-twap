<?php

namespace CJ\LineShopping\Controller\Category;

use CJ\LineShopping\Controller\MasterDownload;

class FullDownload extends MasterDownload
{
    /**
     * @var string
     */
    protected $type = 'full_category';

    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface|void
     */
    public function execute()
    {
        return $this->getResponse();
    }
}
