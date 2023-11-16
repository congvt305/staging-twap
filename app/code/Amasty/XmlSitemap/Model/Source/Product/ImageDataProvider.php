<?php

declare(strict_types=1);

namespace Amasty\XmlSitemap\Model\Source\Product;

use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Gallery\ReadHandler as GalleryReadHandler;
use Magento\Framework\Data\Collection;

class ImageDataProvider
{
    /**
     * @var GalleryReadHandler
     */
    private $galleryReadHandler;

    public function __construct(
        GalleryReadHandler $galleryReadHandler
    ) {
        $this->galleryReadHandler = $galleryReadHandler;
    }

    public function getData(Product $product): array
    {
        $this->galleryReadHandler->execute($product);
        $images = $product->getMediaGalleryImages();

        if ($images instanceof Collection) {
            $image = $images->getFirstItem();
            if ($image->getFile()) {
                $imagesData = [
                    'loc' => $image->getUrl(),
                    'title' => $image->getLabel()
                ];
            }
        }

        return $imagesData ?? [];
    }
}
