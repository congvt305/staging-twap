<?php

namespace CJ\Migrate\Console;

use Magento\Catalog\Setup\CategorySetup;
use Magento\Framework\Filesystem\DirectoryList;
use Magento\Framework\Exception\NotFoundException;
use Magento\Framework\Filesystem\Driver\File;
use Magento\Framework\Serialize\Serializer\Json;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use CJ\Migrate\Helper\Logger as MigrateLogger;
use Magento\Catalog\Setup\CategorySetupFactory;
use Magento\Catalog\Model\Product;

/**
 * Class AbstractMigrateData
 * @package CJ\Cms\Console
 */
abstract class AbstractMigrateData extends \Symfony\Component\Console\Command\Command
{
    const STORE_ID = 'store_id';
    const TYPE_PRODUCT = 'product';
    const GROUP_SULWHASOO = 'Sulwhasoo';
    const ATTRIBUTE_SET_MY_SULWHASOO = 'MY Sulwhasoo';
    /**
     * @var MigrateLogger
     */
    private $migrateLogger;
    /**
     * @var DirectoryList
     */
    private $directoryList;
    /**
     * @var File
     */
    private $driverFile;
    /**
     * @var Json
     */
    private $json;
    /**
     * @var CategorySetupFactory
     */
    private $categorySetupFactory;

    /**
     * @var int
     */
    private $index = 0;

    /**
     * AbstractMigrateData constructor.
     * @param CategorySetupFactory $categorySetupFactory
     * @param Json $json
     * @param File $driverFile
     * @param DirectoryList $directoryList
     * @param MigrateLogger $migrateLogger
     * @param string|null $name
     */
    public function __construct(
        CategorySetupFactory $categorySetupFactory,
        Json $json,
        File $driverFile,
        DirectoryList $directoryList,
        MigrateLogger $migrateLogger,
        string $name = null
    )
    {
        parent::__construct($name);
        $this->migrateLogger = $migrateLogger;
        $this->directoryList = $directoryList;
        $this->driverFile = $driverFile;
        $this->json = $json;
        $this->categorySetupFactory = $categorySetupFactory;
    }

    /**
     * @return string
     */
    protected abstract function getNameConsole(): string;

    /**
     * @return string
     */
    protected abstract function getType(): string;

    /**
     * {@inheritDoc}
     */
    protected function configure()
    {
        $this->setName($this->getNameConsole())
            ->addOption(self::STORE_ID, null, InputOption::VALUE_OPTIONAL, 'Store Id')
            ->setDescription(__('Migrate data %1.', $this->getType()));

        parent::configure();
    }

    /**
     * {@inheritDoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        try {
            if ($this->getType() == self::TYPE_PRODUCT) {
                $attributeProducts = $this->getFileContents('attribute_products.text');
                $size = count($attributeProducts);
                $output->writeln(__('Found %1 data', $size));
                foreach ($attributeProducts as $attributeProduct) {
                    try {
                        $this->migrateAttributeProduct($attributeProduct);
                    } catch (\Exception $e) {
                        $output->writeln($e->getMessage());
                        $this->migrateLogger->logException($e, $this->getType());
                        continue;
                    }
                }
            }
            $output->writeln(__('Total new data affected: %1/%2', $this->index, $size));
        } catch (\Exception $exception) {
            $output->writeln($exception->getMessage());
        }
    }

    /**
     * @param $name
     * @return array|null
     * @throws \Magento\Framework\Exception\FileSystemException
     */
    private function getFileContents($name)
    {
        $path = $this->directoryList->getPath(\Magento\Framework\App\Filesystem\DirectoryList::MEDIA) . '/' . $name;
        $attributeProduct = $this->json->unserialize($this->driverFile->fileGetContents($path));

        return $attributeProduct;
    }

    /**
     * @param $attributeProduct
     * @throws NotFoundException
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Zend_Validate_Exception
     */
    private function migrateAttributeProduct($attributeProduct)
    {
        /** @var CategorySetup $categorySetup */
        $categorySetup = $this->categorySetupFactory->create();
        $entityTypeId = $categorySetup->getEntityTypeId(Product::ENTITY);
        if (empty($attributeProduct['attribute_code'])) {
            throw new NotFoundException(__('Attribute Code is not specified!'));
        }
        $attribute = $categorySetup->getAttribute($entityTypeId, $attributeProduct['attribute_code']);
        $attributeSetId = $categorySetup->getAttributeSetId(Product::ENTITY, self::ATTRIBUTE_SET_MY_SULWHASOO);
        if (!$attribute) {
            if (isset($attributeProduct['attribute_id'])) {
                unset($attributeProduct['attribute_id']);
            }
            if (isset($attributeProduct['attribute_set_id'])) {
                unset($attributeProduct['attribute_set_id']);
            }
            if (isset($attributeProduct['entity_attribute_id'])) {
                unset($attributeProduct['entity_attribute_id']);
            }
            if (isset($attributeProduct['attribute_group_id'])) {
                unset($attributeProduct['attribute_group_id']);
            }
            if (isset($attributeProduct['frontend_label'])) {
                $attributeProduct['label'] = $attributeProduct['frontend_label'];
            }
            $categorySetup->addAttribute(
                Product::ENTITY,
                $attributeProduct['attribute_code'],
                $attributeProduct
            );
            $categorySetup->addAttributeToGroup(
                Product::ENTITY,
                $attributeSetId,
                self::GROUP_SULWHASOO,
                $attributeProduct['attribute_code']
            );
        } else {
            $categorySetup->addAttributeToGroup(
                Product::ENTITY,
                $attributeSetId,
                self::GROUP_SULWHASOO,
                $attributeProduct['attribute_code']
            );
        }

        $this->index++;
    }
}
