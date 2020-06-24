<?php
/**
 * Created by PhpStorm.
 * User: jason
 * Date: 2016-09-05
 * Time: 오후 2:38
 */

namespace Eguana\StoreLocator\Controller\Adminhtml\Image;

/**
 * ImageUploader
 *
 * Class Main
 *  Eguana\StoreLocator\Controller\Adminhtml\Image
 */
class Main extends AbstractUpload
{

    /**
     * get info of uploader
     * @return array
     */
    public function getUploaderInfo()
    {
        return [
            'area' => 'storeinfo_data',
            'name' => 'image'
        ];
    }

    /**
     * get path of image
     * @return string
     */
    public function getImagePath()
    {
        return 'stores';
    }
}
