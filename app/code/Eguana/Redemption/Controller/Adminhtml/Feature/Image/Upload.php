<?php
/**
 * @author Eguana Team
 * @copyriht Copyright (c) 2020 Eguana {http://eguanacommerce.com}
 * Created by PhpStorm
 * User: arslan
 * Date: 16/10/20
 * Time: 1:00 PM
 */
declare(strict_types=1);

namespace Eguana\Redemption\Controller\Adminhtml\Feature\Image;

use Eguana\Redemption\Model\Redemption\ImageUploader;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Controller\ResultInterface;
use Psr\Log\LoggerInterface;

/**
 * Use that class to upload the image
 *
 * Class Upload
 */
class Upload extends Action implements HttpPostActionInterface
{
    /**
     * @var ImageUploader
     */
    private $imageUploader;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * Upload constructor.
     *
     * @param Context $context
     * @param ImageUploader $imageUploader
     * @param LoggerInterface $logger
     */
    public function __construct(
        Context $context,
        ImageUploader $imageUploader,
        LoggerInterface $logger
    ) {
        parent::__construct($context);
        $this->imageUploader = $imageUploader;
        $this->logger = $logger;
    }

    /**
     * Upload file controller action
     *
     * @return bool
     */
    protected function _isAllowed() : bool
    {
        return $this->_authorization->isAllowed('Eguana_Redemption::redemption');
    }

    /**
     * execute method this method is used to upload the image
     *
     * @return ResponseInterface|ResultInterface
     */
    public function execute()
    {
        $imageId = $this->_request->getParam('param_name', 'image');
        try {
            $result = $this->imageUploader->saveFileToTmpDir($imageId);
        } catch (\Exception $exception) {
            $this->logger->debug($exception->getMessage());
        }
        return $this->resultFactory->create(ResultFactory::TYPE_JSON)->setData($result);
    }
}
