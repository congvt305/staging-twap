<?php

declare(strict_types=1);

namespace Amasty\Scroll\Setup\Patch\Data;

use Amasty\Base\Helper\Deploy;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Component\ComponentRegistrar;
use Magento\Framework\Filesystem;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Framework\Validation\ValidationException;

class DeployJasmineTest implements DataPatchInterface
{
    /**
     * @var Deploy
     */
    private $devDeployer;

    /**
     * @var Filesystem
     */
    private $filesystem;

    public function __construct(
        Deploy $devDeployer,
        Filesystem $filesystem
    ) {
        $this->devDeployer = $devDeployer;
        $this->filesystem = $filesystem;
    }

    public static function getDependencies(): array
    {
        return [];
    }

    public function getAliases(): array
    {
        return [];
    }

    public function apply(): self
    {
        $this->deployModuleFiles();

        return $this;
    }

    private function deployModuleFiles(): void
    {
        $path = $this->filesystem->getDirectoryRead(DirectoryList::ROOT)->getAbsolutePath('dev');
        if ($this->filesystem->getDirectoryWrite(DirectoryList::ROOT)->isWritable($path)) {
            $this->devDeployer->deployFolder(__DIR__ . '/../../../dev');
        }
    }
}
