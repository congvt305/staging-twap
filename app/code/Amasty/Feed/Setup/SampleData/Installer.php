<?php
declare(strict_types=1);

namespace Amasty\Feed\Setup\SampleData;

use Amasty\Feed\Model\Import;
use Magento\Framework\Setup;

class Installer implements Setup\SampleData\InstallerInterface
{
    /**
     * @var Import
     */
    private $import;

    public function __construct(
        Import $import
    ) {
        $this->import = $import;
    }

    public function install()
    {
        $this->import->install();
    }
}
