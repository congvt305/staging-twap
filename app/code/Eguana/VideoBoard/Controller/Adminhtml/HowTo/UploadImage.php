<?php
/**
 * @author Eguana Team
 * @copyriht Copyright (c) 2020 Eguana {http://eguanacommerce.com}
 * Created by PhpStorm
 * User: arslan
 * Date: 18/6/20
 * Time: 7:13 PM
 */

namespace Eguana\VideoBoard\Controller\Adminhtml\HowTo;

use Eguana\VideoBoard\Model\VideoBoard\ImageUploader;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Controller\ResultInterface;

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
     * @return ResultInterface
     */
    public function execute()
    {
        $result = $this->imageUploader->saveImageToMediaFolder('thumbnail_image');
        return $this->resultFactory->create(ResultFactory::TYPE_JSON)->setData($result);
    }
}
