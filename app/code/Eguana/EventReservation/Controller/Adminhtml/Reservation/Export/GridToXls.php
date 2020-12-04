<?php
/**
 * @author Eguana Team
 * @copyriht Copyright (c) 2020 Eguana {http://eguanacommerce.com}
 * Created by PhpStorm
 * User: raheel
 * Date: 28/10/20
 * Time: 2:12 PM
 */
namespace Eguana\EventReservation\Controller\Adminhtml\Reservation\Export;

use Eguana\EventReservation\Model\UserReservation\Export\ConvertToXls;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\App\Response\Http\FileFactory;
use Magento\Framework\Controller\Result\Redirect;

/**
 * To export grid data to xls
 *
 * Class Render
 */
class GridToXls extends Action
{
    /**
     * @var ConvertToXls
     */
    private $converter;

    /**
     * @var FileFactory
     */
    private $fileFactory;

    /**
     * @param Context $context
     * @param ConvertToXls $converter
     * @param FileFactory $fileFactory
     */
    public function __construct(
        Context $context,
        ConvertToXls $converter,
        FileFactory $fileFactory
    ) {
        parent::__construct($context);
        $this->converter    = $converter;
        $this->fileFactory  = $fileFactory;
    }

    /**
     * Export data provider to XML
     *
     * @return ResponseInterface|Redirect|ResultInterface
     */
    public function execute()
    {
        try {
            return $this->fileFactory->create(
                'export.xls',
                $this->converter->getXlsFile(),
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
