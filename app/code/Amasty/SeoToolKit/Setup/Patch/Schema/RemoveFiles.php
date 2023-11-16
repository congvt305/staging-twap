<?php
/**
* @author Amasty Team
* @copyright Copyright (c) 2022 Amasty (https://www.amasty.com)
* @package SEO Toolkit for Magento 2 (System)
*/

declare(strict_types=1);

namespace Amasty\SeoToolKit\Setup\Patch\Schema;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Filesystem;
use Magento\Framework\Setup\Patch\SchemaPatchInterface;

class RemoveFiles implements SchemaPatchInterface
{
    /**
     * @var Filesystem
     */
    private $filesystem;

    public function __construct(Filesystem $filesystem)
    {
        $this->filesystem = $filesystem;
    }

    public function apply()
    {
        $directory = $this->filesystem->getDirectoryRead(DirectoryList::APP);
        if ($directory->isExist('code/Amasty/SeoToolKit/etc/config.xml')) {
            throw new LocalizedException(
                __("\nWARNING: This update requires removing folder app/code/Amasty/SeoToolKit.\n"
                    . "Remove this folder and unpack new version of package into app/code/Amasty/SeoToolKit.\n"
                    . "Run `php bin/magento setup:upgrade` again")
            );
        }
    }

    public function getAliases()
    {
        return [];
    }

    public static function getDependencies()
    {
        return [];
    }
}
