<?php
/**
 * @author Eguana Team
 * @copyriht Copyright (c) 2020 Eguana {http://eguanacommerce.com}
 * Created by PhpStorm
 * User: yasir
 * Date: 6/17/20
 * Time: 7:12 AM
 */
namespace Eguana\Magazine\Controller\Adminhtml\Magazine;

/**
 * Class for image information*
 * Class Image
 */
class Image extends AbstractUpload
{
    /**
     * return the uploaded file info
     * @return array|null
     */

    public function getUploaderInfo()
    {
        return [
            'area' => 'general',
            'name' => 'thumbnail_image'
        ];
    }
}
