<?php

namespace CJ\Cms\Console;

use CJ\Cms\Helper\Request as RequestHelper;
use Magento\Cms\Model\ResourceModel\Page as PageResourceModel;
use Magento\Cms\Model\PageFactory;
use Magento\Cms\Model\ResourceModel\Block as BlockResourceModel;
use Magento\Cms\Model\BlockFactory;
use Magento\Framework\Exception\NotFoundException;
use Magento\Store\Api\StoreRepositoryInterface;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class AbstractMigrateData
 * @package CJ\Cms\Console
 */
abstract class AbstractMigrateData extends \Symfony\Component\Console\Command\Command
{
    const STORE_ID = 'store_id';
    const TYPE_PAGE = 'page';
    const TYPE_BLOCK = 'block';
    const TYPE_WIDGET = 'widget';
    const MY_SWS_STORE_CODE = 'my_sulwhasoo';
    /**
     * @var RequestHelper
     */
    private $requestHelper;
    /**
     * @var PageFactory
     */
    private $pageFactory;
    /**
     * @var BlockFactory
     */
    private $blockFactory;
    /**
     * @var StoreRepositoryInterface
     */
    private $storeRepository;
    /**
     * @var PageResourceModel
     */
    private $pageResourceModel;
    /**
     * @var BlockResourceModel
     */
    private $blockResourceModel;

    /**
     * AbstractMigrateData constructor.
     * @param BlockResourceModel $blockResourceModel
     * @param PageResourceModel $pageResourceModel
     * @param StoreRepositoryInterface $storeRepository
     * @param BlockFactory $blockFactory
     * @param PageFactory $pageFactory
     * @param RequestHelper $requestHelper
     * @param string|null $name
     */
    public function __construct(
        BlockResourceModel $blockResourceModel,
        PageResourceModel $pageResourceModel,
        StoreRepositoryInterface $storeRepository,
        BlockFactory $blockFactory,
        PageFactory $pageFactory,
        RequestHelper $requestHelper,
        string $name = null
    )
    {
        parent::__construct($name);
        $this->requestHelper = $requestHelper;
        $this->pageFactory = $pageFactory;
        $this->blockFactory = $blockFactory;
        $this->storeRepository = $storeRepository;
        $this->pageResourceModel = $pageResourceModel;
        $this->blockResourceModel = $blockResourceModel;
    }

    /**
     * @return string
     */
    protected abstract function getNameConsole(): string;

    /**
     * @return string
     */
    protected abstract function getSearchPath(): string;

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
            ->setDescription('Migrate cms data.');

        parent::configure();
    }

    /**
     * {@inheritDoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        try {
            $cmsData = $this->requestHelper->getCmsData($this->getSearchPath());
            if ($cmsData->isEmpty()) {
                throw new NotFoundException(__('Cms page data not found!'));
            }
            $totalRecord = 0;
            $output->writeln(__('Found %1 page', $cmsData->getTotalCount()));
            $storeMYSWSId = $this->storeRepository->get(self::MY_SWS_STORE_CODE)->getId();
            foreach ($cmsData->getItems() as $item) {
                try {
                    if ($this->getType() == self::TYPE_PAGE) {
                        $this->migratePage($item, $storeMYSWSId);
                    } elseif ($this->getType() == self::TYPE_BLOCK) {
                        $this->migrateBlock($item, $storeMYSWSId);
                    }
                    $totalRecord++;
                } catch (\Exception $e) {
                    $output->writeln($e->getMessage());
                    continue;
                }
            }
            $output->writeln(__('Total pages affected: %1', $totalRecord));
        } catch (\Exception $exception) {
            $output->writeln($exception->getMessage());
        }
    }

    /**
     * @param $item
     * @param $storeMYSWSId
     * @throws \Exception
     */
    protected function migratePage($item, $storeMYSWSId)
    {
        $page = $this->pageFactory->create();
        $identifier = '';
        $pageIdOld = $item['id'] ?? 0;
        if (isset($item['identifier']) && !empty($item['identifier'])) {
            $identifier = $item['identifier'] . $pageIdOld;
        }
        $data = [
            'title' => $item['title'] ?? '',
            'page_layout' => $item['page_layout'] ?? '',
            'meta_keywords' => $item['meta_keywords'] ?? '',
            'meta_description' => $item['meta_description'] ?? '',
            'identifier' => $identifier,
            'content_heading' => $item['content_heading'] ?? '',
            'content' => $item['content'] ?? '',
            'is_active' => $item['active'] ?? 1,
            'stores' => [$storeMYSWSId],
            'sort_order' => $item['sort_order'] ?? 0
        ];
        $page->setData($data);
        $this->pageResourceModel->save($page);
    }

    /**
     * @param $item
     * @param $storeMYSWSId
     * @throws \Exception
     */
    protected function migrateBlock($item, $storeMYSWSId)
    {
        $block = $this->blockFactory->create();
        $identifier = '';
        $blockIdOld = $item['id'] ?? 0;
        if (isset($item['identifier']) && !empty($item['identifier'])) {
            $identifier = $item['identifier'] . $blockIdOld;
        }
        $data = [
            'title' => $item['title'] ?? '',
            'identifier' => $identifier,
            'content' => $item['content'] ?? '',
            'is_active' => $item['active'] ?? 1,
            'stores' => [$storeMYSWSId]
        ];
        $block->setData($data);
        $this->blockResourceModel->save($block);
    }
}
