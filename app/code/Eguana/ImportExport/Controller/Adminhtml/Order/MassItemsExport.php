<?php
/**
 * @author Eguana Team
 * @copyriht Copyright (c) 2020 Eguana {http://eguanacommerce.com}
 * Created by PhpStorm
 * User: raheel
 * Date: 30/11/20
 * Time: 2:02 PM
 */
namespace Eguana\ImportExport\Controller\Adminhtml\Order;

use Eguana\ImportExport\Model\Sales\Order\Export\ConvertToCsv;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\App\Response\Http\FileFactory;
use Magento\Framework\Controller\Result\Redirect;

/**
 * To export multiple order's item in csv form
 *
 * Class MassItemsExport
 */
class MassItemsExport extends Action implements HttpPostActionInterface
{
    /**
     * @var ConvertToCsv
     */
    private $converter;

    /**
     * @var FileFactory
     */
    private $fileFactory;

    /**
     * @param Context $context
     * @param FileFactory $fileFactory
     * @param ConvertToCsv $converter
     */
    public function __construct(
        Context $context,
        FileFactory $fileFactory,
        ConvertToCsv $converter
    ) {
        $this->converter = $converter;
        $this->fileFactory = $fileFactory;
        parent::__construct($context);
    }

    /**
     * Execute action to delete events
     *
     * @return ResponseInterface|Redirect|ResultInterface
     */
    public function execute()
    {
        try {
            return $this->fileFactory->create(
                'items-report.csv',
                $this->converter->getCsvFile(),
                'var'
            );
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage(
                $e->getMessage()
            );
            $resultRedirect = $this->resultRedirectFactory->create();
            return $resultRedirect->setUrl($this->_redirect->getRefererUrl());
        }
    }
}
