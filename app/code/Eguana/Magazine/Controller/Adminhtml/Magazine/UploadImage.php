<?php
/**
 * @author Eguana Team
 * @copyriht Copyright (c) 2020 Eguana {http://eguanacommerce.com}
 * Created by PhpStorm
 * User: yasir
 * Date: 6/17/20
 * Time: 7:36 AM
 */

namespace Eguana\Magazine\Controller\Adminhtml\Magazine;

use Eguana\Magazine\Model\Magazine\ImageUploader;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\ResultFactory;

/**
 * Action for Uploading files
 * Class UploadImage
 */
class UploadImage extends \Magento\Backend\App\Action
{

    /**
     * @var ImageUploader
     */
    private $imageUploader;

    /**
     * @param Context $context
     * @param ImageUploader $imageUploader
     */
    public function __construct(
        Context $context,
        ImageUploader $imageUploader
    ) {
        parent::__construct($context);
        $this->imageUploader = $imageUploader;
    }

    /**
     * Image upload action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $result = $this->imageUploader->saveImageToMediaFolder('thumbnail_image');
        return $this->resultFactory->create(ResultFactory::TYPE_JSON)->setData($result);
    }
}
