<?php
/**
 * @author Eguana Team
 * @copyriht Copyright (c) 2020 Eguana {http://eguanacommerce.com}
 * Created by PhpStorm
 * User: arslan
 * Date: 29/6/20
 * Time: 7:46 PM
 */
namespace Eguana\EventManager\Controller\Adminhtml\Manage;

use Eguana\EventManager\Model\EventManager\ImageUploader;
use Magento\Backend\App\Action as ActionAlias;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Controller\ResultInterface;

/**
 * This class is used to upload images
 *
 * Class UploadImage
 */
class UploadImage extends ActionAlias
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
     * @return ResultInterface
     */
    public function execute()
    {
        $result = $this->imageUploader->saveImageToMediaFolder('thumbnail_image');
        return $this->resultFactory->create(ResultFactory::TYPE_JSON)->setData($result);
    }
}
