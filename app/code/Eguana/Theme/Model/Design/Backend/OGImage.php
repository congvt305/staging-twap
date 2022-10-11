<?php

namespace Eguana\Theme\Model\Design\Backend;

use Magento\Theme\Model\Design\Backend\Image;
use Magento\Framework\Filesystem;

/**
 * Class OGImage
 */
class OGImage extends Image
{
    /**
     * The tail part of directory path for uploading
     *
     */
    const UPLOAD_DIR = 'ogimage';

    /**
     * Return path to directory for upload file
     *
     * @return string
     * @throw \Magento\Framework\Exception\LocalizedException
     */
    protected function _getUploadDir()
    {
        return $this->_mediaDirectory->getRelativePath($this->_appendScopeInfo(self::UPLOAD_DIR));
    }

    /**
     * Makes a decision about whether to add info about the scope.
     *
     * @return boolean
     */
    protected function _addWhetherScopeInfo()
    {
        return true;
    }

    /**
     * Getter for allowed extensions of uploaded files.
     *
     * @return string[]
     */
    public function getAllowedExtensions()
    {
        return ['ico', 'png', 'gif', 'jpg', 'jpeg', 'apng'];
    }
}
