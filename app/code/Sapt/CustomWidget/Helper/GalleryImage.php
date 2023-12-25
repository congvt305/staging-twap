<?php


namespace Sapt\CustomWidget\Helper;


use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Helper\Image;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Catalog\Model\Product\Gallery\ReadHandler as GalleryReadHandler;
use Magento\Framework\Data\Collection;

class GalleryImage extends AbstractHelper
{
    /**
     * @var Image
     */
    protected $imageHelper;
    /**
     * @var GalleryReadHandler
     */
    protected $galleryReadHandler;

    public function __construct(
        GalleryReadHandler $galleryReadHandler,
        Image $imageHelper,
        Context $context
    ) {
        $this->imageHelper = $imageHelper;
        $this->galleryReadHandler = $galleryReadHandler;
        parent::__construct($context);
    }

    public function addGallery($product) {
        $this->galleryReadHandler->execute($product);
    }

    public function getGalleryImages(ProductInterface $product)
    {
        $images = $product->getMediaGalleryImages();
        if ($images instanceof Collection) {
            foreach ($images as $image) {
                $image->setData(
                    'medium_image_url',
                    $this->imageHelper->init($product, 'product_page_image_medium')
                        ->constrainOnly(true)->keepAspectRatio(true)->keepFrame(false)
                        ->setImageFile($image->getFile())
                        ->getUrl()
                );
            }
        }
        return $images;
    }
}
