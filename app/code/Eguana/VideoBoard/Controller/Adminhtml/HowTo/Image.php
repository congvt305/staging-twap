<?php
/**
 * @author Eguana Team
 * @copyriht Copyright (c) 2020 Eguana {http://eguanacommerce.com}
 * Created by PhpStorm
 * User: arslan
 * Date: 18/6/20
 * Time: 7:01 PM
 */

namespace Eguana\VideoBoard\Controller\Adminhtml\HowTo;

/**
 * Class for image information
 *
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
