<?php

declare(strict_types=1);

namespace Amasty\XmlSitemap\Console\Command;

use Amasty\XmlSitemap\Api\SitemapInterface;
use Amasty\XmlSitemap\Model\ResourceModel\Sitemap\CollectionFactory;
use Amasty\XmlSitemap\Model\GenerateAndSave;
use Magento\Framework\App\Area;
use Magento\Framework\App\State;
use Magento\Setup\Console\Command\AbstractSetupCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Magento\Framework\App\ObjectManager;

class GenerateXmlSiteMap extends AbstractSetupCommand
{
    public const MESSAGE_SUCCESS = '<info>Sitemap has been generated successfully</info>';
    public const MESSAGE_NOT_FOUND = '<error>We can\'t find Sitemap</error>';
    public const MESSAGE_ERROR = '<error>%s</error>';

    /**
     * @var CollectionFactory
     */
    private $collectionFactory;

    /**
     * @var State
     */
    private $state;

    /**
     * @var GenerateAndSave
     */
    private $generateAndSave;

    public function __construct(
        CollectionFactory $collectionFactory,
        State $state,
        $name = null
    ) {
        $this->collectionFactory = $collectionFactory;
        $this->state = $state;
        parent::__construct($name);
    }

    protected function configure(): void
    {
        $this->setName('amasty:xmlsitemap:generate');
        $this->setDescription('Generate Amasty Xml Sitemap');

        $this->setDefinition([
            new InputArgument(
                'id',
                InputArgument::OPTIONAL,
                'Sitemap Id. Default: All'
            )
        ]);

        parent::configure();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->state->emulateAreaCode(
            Area::AREA_FRONTEND,
            [$this, 'generate'],
            [$input, $output]
        );

        return 0;
    }

    public function generate(InputInterface $input, OutputInterface $output)
    {
        try {
            $sitemapId = (int) $input->getArgument('id');
            $collection = $this->collectionFactory->create();

            if ($sitemapId) {
                $collection->addFieldToFilter(SitemapInterface::SITEMAP_ID, $sitemapId);
            }

            if ($collection->getSize()) {
                foreach ($collection as $sitemap) {
                    $this->getGenerateAndSave()->execute($sitemap);
                }
                $output->writeln(self::MESSAGE_SUCCESS);
            } else {
                $output->writeln(self::MESSAGE_NOT_FOUND);
            }
        } catch (\Exception $e) {
            $output->writeln(sprintf(self::MESSAGE_ERROR, $e->getMessage()));
        }
    }

    /**
     * use object manager because we can't change count of constructor params because of error on setup
     * @return GenerateAndSave
     */
    private function getGenerateAndSave(): GenerateAndSave
    {
        if ($this->generateAndSave === null) {
            $this->generateAndSave = ObjectManager::getInstance()->get(GenerateAndSave::class);
        }

        return $this->generateAndSave;
    }
}
