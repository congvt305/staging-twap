<?php

namespace Amore\PersistentInCart\Console\Command;

use Magento\Framework\App\ObjectManager;
use Magento\Quote\Model\ResourceModel\Quote\CollectionFactory as QuoteCollectionFactory;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class CleanPersistentInCartCommand
 */
class CleanPersistentInCartCommand extends Command
{
    /**
     * @var QuoteCollectionFactory
     */
    private $quoteCollectionFactory;

    /** @var \Magento\Framework\App\State **/
    private $state;

    /**
     * @param QuoteCollectionFactory|null $quoteCollectionFactory
     * @param \Magento\Framework\App\State $state
     * @param string|null $name
     */
    public function __construct(
        QuoteCollectionFactory $quoteCollectionFactory = null,
        \Magento\Framework\App\State $state,
        string  $name = null
    )
    {
        $this->state = $state;
        $this->quoteCollectionFactory = $quoteCollectionFactory ?: ObjectManager::getInstance()->get(QuoteCollectionFactory::class);
        parent::__construct($name);
    }

    /**
     * Define command
     */
    protected function configure()
    {
        $this->setName('persistent:quote:clean');
        $this->setDescription('This command is used to clean is_persistent in quote into 0');

        parent::configure();
    }


    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int|void
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->state->setAreaCode(\Magento\Framework\App\Area::AREA_CRONTAB);
        $collection = $this->quoteCollectionFactory->create();
        $collection->addFieldToFilter('is_persistent', 1);
        foreach ($collection as $quote) {
            $quote->setIsPersistent(false);
            $quote->save();
            $output->write("quote Id: " . $quote->getId(). PHP_EOL);
        }
        $output->writeln("<info>Finished</info>");
    }
}
