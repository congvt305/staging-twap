<?php
/**
 * @author Eguana Team
 * @copyriht Copyright (c) 2020 Eguana {http://eguanacommerce.com}
 * Created by PhpStorm
 * User: raheel
 * Date: 7/1/21
 * Time: 2:00 PM
 */
declare(strict_types=1);

namespace Eguana\ImportCoupon\Controller\Adminhtml\ImportCsv;

use Eguana\ImportCoupon\Model\ImportCsv\FileUploader;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Controller\ResultInterface;
use Psr\Log\LoggerInterface;

/**
 * To upload the csv file
 *
 * Class UploadFile
 */
class UploadFile extends Action implements HttpPostActionInterface
{
    /**
     * @var FileUploader
     */
    private $fileUploader;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @param Context $context
     * @param FileUploader $fileUploader
     * @param LoggerInterface $logger
     */
    public function __construct(
        Context $context,
        FileUploader $fileUploader,
        LoggerInterface $logger
    ) {
        parent::__construct($context);
        $this->logger = $logger;
        $this->fileUploader = $fileUploader;
    }

    /**
     * Method used to upload the csv file
     *
     * @return ResponseInterface|ResultInterface
     */
    public function execute()
    {
        $imageId = $this->_request->getParam('param_name', 'documents');
        try {
            $result = $this->fileUploader->saveFileToMediaFolder($imageId);
        } catch (\Exception $exception) {
            $this->logger->debug($exception->getMessage());
        }
        return $this->resultFactory->create(ResultFactory::TYPE_JSON)->setData($result);
    }
}
