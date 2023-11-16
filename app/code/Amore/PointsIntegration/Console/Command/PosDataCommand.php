<?php

namespace Amore\PointsIntegration\Console\Command;

use Amore\PointsIntegration\Model\PosCustomerData;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\File\Csv;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class PosDataCommand
 * @package Amore\PointsIntegration\Console\Command
 */
class PosDataCommand extends Command
{
    const file = 'pos_customer.csv';

    const CUSTOMER_EMAIL = 'customer_email';

    /**
     * @param Csv $csv
     * @param DirectoryList $directoryList
     * @param PosCustomerData $posCustomerData
     * @param LoggerInterface $logger
     * @param string|null $name
     */
    public  function __construct(
        protected Csv $csv,
        protected DirectoryList $directoryList,
        protected PosCustomerData $posCustomerData,
        protected LoggerInterface $logger,
        string $name = null
    ) {
        parent::__construct($name);
    }

    /**
     * Initialization of the command.
     */
    protected function configure()
    {
        $this->setName('pos:join:api');
        $this->setDescription('POS data missing re-run on command (002 join api call)');

        $this->addOption(
            self::CUSTOMER_EMAIL,
            null,
            InputOption::VALUE_REQUIRED,
            'CUSTOMER EMAIL'
        );

        parent::configure();
    }

    /**
     * CLI command description.
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     *
     * @return void
     */
    protected function execute(InputInterface $input, OutputInterface $output): void
    {
        $customerEmail = $input->getOption(self::CUSTOMER_EMAIL) ?? 0;
        $file = $this->directoryList->getPath(DirectoryList::MEDIA) . DIRECTORY_SEPARATOR . self::file;
        $csvData = $this->csv->getData($file);
        foreach ($csvData as $row => $data) {
            if ($row > 0) {
                foreach ($data as $key => $value) {
                    if (!empty($value)) {
                        $output->writeln('<info>Sending ' . ($customerEmail ? 'Email' : 'Integration Number') . ' ' . $value . '...</info>');
                        if ($customerEmail) {
                            $responses = $this->posCustomerData->sendJoinMemberByEmail($value);
                        } else {
                            $responses = $this->posCustomerData->sendJoinMemberByIntegrationNumber($value);
                        }
                        foreach ($responses as $response) {
                            $output->writeln('<info>Response: ' . json_encode($response) . '</info>');
                        }
                        $output->writeln('<info>Done!</info>');
                    }
                    break;
                }

            }
        }
    }
}
