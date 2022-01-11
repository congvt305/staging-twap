<?php

namespace CJ\LineShopping\Controller\Product;

use CJ\LineShopping\Controller\MasterDownload;

class PartialDownload extends MasterDownload
{
    /**
     * @var string
     */
    protected $type = 'partial_product';

    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface|void
     */
    public function execute()
    {
        return $this->getResponse();
    }
}
