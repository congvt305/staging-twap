<?php

namespace CJ\Cms\Console;

use CJ\Cms\Helper\Request as RequestHelper;
use Exception;
use Magento\Cms\Model\BlockFactory;
use Magento\Cms\Model\PageFactory;
use Magento\Cms\Model\ResourceModel\Block as BlockResourceModel;
use Magento\Cms\Model\ResourceModel\Page as PageResourceModel;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Exception\NotFoundException;
use Magento\Framework\Filesystem\Driver\File;
use Magento\Store\Api\StoreRepositoryInterface;
use Magento\Widget\Model\Widget\Instance;
use Magento\Widget\Model\Widget\InstanceFactory;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Theme\Model\View\Design;
use Magento\Framework\Locale\ResolverInterface;

/**
 * Class MigrateWidgetsData
 * @package CJ\Cms\Console
 */
class MigrateWidgetsData extends AbstractMigrateData
{
    const NAME = 'cj:migrate:widgets';
    /**
     * @var InstanceFactory
     */
    protected $instanceFactory;
    /**
     * @var \Magento\Framework\Filesystem\DirectoryList
     */
    protected $directoryList;
    /**
     * @var File
     */
    protected $driverFile;
    /**
     * @var Json
     */
    protected $json;
    /**
     * @var Design
     */
    protected $design;
    /**
     * @var ResolverInterface
     */
    protected $resolver;

    /**
     * @param ResolverInterface $resolver
     * @param Design $design
     * @param Json $json
     * @param InstanceFactory $instanceFactory
     * @param \Magento\Framework\Filesystem\DirectoryList $directoryList
     * @param File $driverFile
     * @param BlockResourceModel $blockResourceModel
     * @param PageResourceModel $pageResourceModel
     * @param StoreRepositoryInterface $storeRepository
     * @param BlockFactory $blockFactory
     * @param PageFactory $pageFactory
     * @param RequestHelper $requestHelper
     * @param string|null $name
     */
    public function __construct(
        ResolverInterface $resolver,
        Design $design,
        Json $json,
        InstanceFactory                             $instanceFactory,
        \Magento\Framework\Filesystem\DirectoryList $directoryList,
        File                                        $driverFile,
        BlockResourceModel                          $blockResourceModel,
        PageResourceModel                           $pageResourceModel,
        StoreRepositoryInterface                    $storeRepository,
        BlockFactory                                $blockFactory,
        PageFactory                                 $pageFactory, RequestHelper $requestHelper,
        string                                      $name = null
    )
    {
        parent::__construct($blockResourceModel, $pageResourceModel, $storeRepository, $blockFactory, $pageFactory, $requestHelper, $name);
        $this->instanceFactory = $instanceFactory;
        $this->directoryList = $directoryList;
        $this->driverFile = $driverFile;
        $this->json = $json;
        $this->design = $design;
        $this->resolver = $resolver;
    }

    /**
     * @return string
     */
    protected function getNameConsole(): string
    {
        return self::NAME;
    }

    /**
     * @return string
     */
    protected function getSearchPath(): string
    {
        return '';
    }

    /**
     * @return string
     */
    protected function getType(): string
    {
        return self::TYPE_WIDGET;
    }

    /**
     * {@inheritDoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        try {
            $path = $this->directoryList->getPath(DirectoryList::MEDIA) . '/widgets.text';
            $cmsData = $this->json->unserialize($this->driverFile->fileGetContents($path));
            if (count($cmsData) == 0) {
                throw new NotFoundException(__('Widget data not found!'));
            }
            $this->design->setArea('adminhtml');
            $this->design->setDesignTheme($this->design->getConfigurationDesignTheme());
            $locale = $this->resolver->setLocale('en_US')->setDefaultLocale('en_US');
            $this->design->setLocale($locale);
            $totalRecord = 0;
            $output->writeln(__('Found %1 widget', count($cmsData)));
            foreach ($cmsData as $data) {
                try {
                    $this->createWidgetInstance()->setData($data)->save();
                    $totalRecord++;
                } catch (Exception $e) {
                    $output->writeln($e->getMessage());
                    continue;
                }
            }
            $output->writeln(__('Total widgets affected: %1', $totalRecord));
        } catch (Exception $exception) {
            $output->writeln($exception->getMessage());
        }
    }

    /**
     * @return Instance
     */
    private function createWidgetInstance()
    {
        return $this->instanceFactory->create();
    }
}
