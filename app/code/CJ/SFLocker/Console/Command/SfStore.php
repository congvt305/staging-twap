<?php

namespace CJ\SFLocker\Console\Command;

use CJ\SFLocker\Model\Config\Source\StoreType;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem\DirectoryList as Dir;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Magento\Framework\Filesystem\Driver\File;
use Magento\Framework\File\Csv;
use CJ\SFLocker\Model\ResourceModel\SFLocker as SFLockerResource;

/**
 * Class SfStore
 */
class SfStore extends Command
{
    protected $filename = 'sfstore.csv';
    protected $storeType = StoreType::SF_STORE;
    protected $dir;
    /**
     * @var File
     */
    protected $file;
    /**
     * @var Csv
     */
    protected $csv;
    protected $sfLockerResource;

    public function __construct(
        Dir              $dir,
        File             $file,
        Csv              $csv,
        SFLockerResource $sfLockerResource
    )
    {
        $this->dir = $dir;
        $this->file = $file;
        $this->csv = $csv;
        $this->sfLockerResource = $sfLockerResource;
        parent::__construct();
    }


    protected function configure()
    {
        $this->setName('import:sf:store');
        $this->setDescription('This is import sf stores console command.');

        parent::configure();
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $varDir = $this->dir->getPath(DirectoryList::VAR_DIR);
        $file = $varDir . '/sf/' . $this->filename;
        if ($this->file->isExists($file)) {
            $data = $this->csv->getData($file);
            unset($data[0]);
            if ($data) {
                $this->sfLockerResource->importSFLockers($data, $this->storeType);
            }
        } else {
            $output->writeln('<error>csv file does not exist.</error>');
            return false;
        }
        $output->writeln('<info>import sf stores success.</info>');
    }
}
