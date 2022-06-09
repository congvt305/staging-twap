<?php

namespace CJ\ImportExport\Command;

use Magento\Framework\App\Area;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\State;
use Magento\Framework\File\Csv;
use Magento\Framework\Validation\ValidationException;
use Magento\Setup\Model\AdminAccount;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Magento\Catalog\Model\CategoryFactory;
use Magento\Catalog\Model\CategoryRepository;
use Magento\Store\Model\StoreManagerInterface;
use Symfony\Component\Console\Question\Question;

class ImportCategories extends Command
{
    const CATEGORIES_CSV   = 'categories.csv';
    const ROOT_CATEGORY_ID = 'store_id';

    /**
     * @var Csv
     */
    private Csv $csv;

    /**
     * @var CategoryFactory
     */
    private CategoryFactory $categoryFactory;

    /**
     * @var CategoryRepository
     */
    private CategoryRepository $categoryRepository;

    /**
     * @var DirectoryList
     */
    private DirectoryList $directoryList;

    /**
     * @var State
     */
    private State $state;

    /**
     * @var StoreManagerInterface
     */
    private StoreManagerInterface $storeManager;

    /**
     * @param Csv $csv
     * @param CategoryFactory $categoryFactory
     * @param CategoryRepository $categoryRepository
     * @param DirectoryList $directoryList
     * @param State $state
     * @param StoreManagerInterface $storeManager
     * @param string|null $name
     */
    public function __construct(
        Csv                   $csv,
        CategoryFactory       $categoryFactory,
        CategoryRepository    $categoryRepository,
        DirectoryList         $directoryList,
        State                 $state,
        StoreManagerInterface $storeManager,
        string                $name = null
    )
    {
        parent::__construct($name);
        $this->csv                = $csv;
        $this->categoryFactory    = $categoryFactory;
        $this->categoryRepository = $categoryRepository;
        $this->directoryList      = $directoryList;
        $this->state              = $state;
        $this->storeManager       = $storeManager;
    }

    protected function configure()
    {
        $this->setName('import:categories')
            ->setDescription('Import categories by CSV file. File must be located in pub/media/import/' . self::CATEGORIES_CSV);

        parent::configure();
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int|void
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        try {
            $this->state->setAreaCode(Area::AREA_ADMINHTML);
            $file = $this->directoryList->getPath(DirectoryList::MEDIA) .
                DIRECTORY_SEPARATOR . 'import' .
                DIRECTORY_SEPARATOR . self::CATEGORIES_CSV;
            $csvData = $this->csv->getData($file);
            foreach ($csvData as $row => $data) {
                if ($row > 0) {
                    foreach ($data as $key => $value) {
                        if ($value === '') {
                            $data[$key] = null;
                        }
                    }
                    $this->creatingCategory($data, $output);
                }
            }
        } catch (\Exception $e) {
            $output->writeln('<comment>Something went wrong when importing categories.</comment>');
            $output->writeln($e->getMessage());
        }
    }

    /**
     * Data header in csv
     * 0 'level'
     * 1 'parent_id'
     * 2 'name'
     * 3 'is_active'
     * 4 'include_in_menu'
     * 5 'is_anchor'
     * 6 'meta_title'
     * 7 'meta_keywords'
     * 8 'meta_description'
     * 9 'description'
     * 10 'url_key'
     * 11 'root_category'
     *
     * @param array $data
     * @param OutputInterface $output
     * @throws \Exception
     */
    private function creatingCategory(array $data, OutputInterface $output)
    {
        /**
         * Check parent category
         * in case the Parent Category Name or ID is null and root_category is null, will use $parentCategory = 2
         */
        $parentCategory = $this->categoryRepository->get($this->storeManager->getStore()->getRootCategoryId());

        if (isset($data[11]) && $data[11]) {
            $parentCategory = $this->categoryRepository->get($data[11]);
        }

        if (is_string($data[1])) {
            $categoryFactory = $this->categoryFactory;
            $collection = $categoryFactory->create()->getCollection()
                ->addFieldToFilter('level', ['eq' => $data[0] - 1])
                ->addFieldToFilter('name', ['in' => $data[1]])
                ->addFieldToFilter('path', ['like' => '1/'. $data[11] .'%']);
            if ($collection->getSize()) {
                $parentCategory = $collection->getFirstItem();
            } else {
                $output->writeln('Parent category: ' . $data[1] .
                    ' not exists. Switch to root category as the parent category.');
            }
        } elseif (is_numeric(($data[2]))) {
            $parentCategory = $this->categoryRepository->get($data[1]);
        }

        $parentCategoryId = $parentCategory->getId();

        // check category is existed
        $categoryFactory = $this->categoryFactory;
        $checkCategory = $categoryFactory->create()->getCollection()
            ->addFieldToFilter('name', ['in' => $data[2]])
            ->addFieldToFilter('parent_id', ['in' => $parentCategoryId]);
        if (!$checkCategory->getData()) {
            $categoryData = [
                'level' => $data[0],
                'name' => $data[2],
                'parent_id' => $parentCategoryId,
                'is_active' => $data[3] ?? 0,
                'include_in_menu' => $data[4] ?? 0,
                'is_anchor' => $data[5] ?? 0,
                'meta_title' => $data[6] ?? $data[1],
                'meta_keywords' => $data[7] ?? $data[1],
                'meta_description' => $data[8] ?? $data[1],
                'description' => $data[9] ?? $data[1],
                'url_key' => $data[10] ??
                    $this->formatUrl(strtolower($data[2])),
                'path' => $parentCategory->getPath(),
                'store_id' => 0
            ];

            $category = $this->categoryFactory->create();
            $category->addData($categoryData)->save();
            if ($category->getParentId() == $this->storeManager->getStore()->getRootCategoryId()) {
                $output->writeln('');
            }
            $output->writeln(
                'Created category: ' . $parentCategory->getName() . '/' . $category->getName()
            );
        } else {
            $output->writeln('Category: ' . $data[2] . ' exists');
        }
    }

    /**
     * @param $string
     * @return string
     */
    private function formatUrl($string): string
    {
        $string = str_replace(array('[\', \']'), '', $string);
        $string = preg_replace('/\[.*\]/U', '', $string);
        $string = preg_replace('/&(amp;)?#?[a-z0-9]+;/i', '-', $string);
        $string = htmlentities($string, ENT_COMPAT, 'utf-8');
        $string = preg_replace('/&([a-z])(acute|uml|circ|grave|ring|cedil|slash|tilde|caron|lig|quot|rsquo);/i', '\\1', $string);
        $string = preg_replace(array('/[^a-z0-9]/i', '/[-]+/'), '-', $string);
        $urlPrefix = explode('.', microtime(true));
        return end($urlPrefix) . '-' . trim($string, '-');
    }
}
