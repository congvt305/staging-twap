<?php
/**
 * @author Eguana Commerce
 * @copyright Copyright (c) 2020 Eguana {https://eguanacommerce.com}
 *  Created by PhpStorm
 *  User: Sonia Park
 *  Date: 3/14/20, 6:38 PM
 *
 */

namespace Eguana\BizConnect\Console\Command;

use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\Exception\AlreadyExistsException;
use Magento\Framework\Serialize\Serializer\Json;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class LogTestCommand extends Command
{
    /**
     * @var \Eguana\BizConnect\Model\OperationLogRepository
     */
    private $operationLogRepository;
    /**
     * @var Json
     */
    private $json;
    /**
     * @var ManagerInterface
     */
    private $eventManager;

    public function __construct(
        ManagerInterface $eventManager,
        \Eguana\BizConnect\Model\OperationLogRepository $operationLogRepository,
        \Magento\Framework\Serialize\Serializer\Json $json,
        $name = null
    ) {
        parent::__construct($name);

        $this->operationLogRepository = $operationLogRepository;
        $this->json = $json;
        $this->eventManager = $eventManager;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        /** @var \Eguana\BizConnect\Model\LoggedOperation $loggedOperation */
        //event firing
        $data = [
            'order_id' => '1000014',
            'order_item' => 'KAGCSG308',
            'qty' => 4,
            'track_number' => '12345sdfes2gd'
        ];
        $this->eventManager->dispatch(
            'eguana_bizconnect_operation_processed',
            [
                'topic_name' => 'shipment.track.create',
                'direction' => 'incoming', //incoming or outgoing
                'to' => 'tempostar', //from or to
                'serialized_data' => $this->json->serialize($data),
                'status' => 1,
                'result_message' => 'Shipment track saved'
            ]
        );

        $output->writeln('test log saved');
        return \Magento\Framework\Console\Cli::RETURN_SUCCESS;
    }

    protected function configure()
    {
        $this->setName('bizconnect:log:test');
        $this->setDescription('log test');
        parent::configure();
    }

}
