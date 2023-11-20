<?php
/**
 * @author Eguana Commerce
 * @copyright Copyright (c) 2020 Eguana {https://eguanacommerce.com}
 *  Created by PhpStorm
 *  User: Sonia Park
 *  Date: 3/14/20, 5:46 PM
 *
 */

namespace Eguana\BizConnect\Ui\Component\Listing\Column\OperationLog\TopicName;

use Magento\Framework\Data\OptionSourceInterface;
use Eguana\BizConnect\Model\ResourceModel\LoggedOperation\CollectionFactory as OperationCollectionFactory;

class Options implements OptionSourceInterface
{
    /**
     * @var \Eguana\BizConnect\Model\OperationLogRepository
     */
    private $operationLogRepository;

    public function __construct(\Eguana\BizConnect\Model\OperationLogRepository $operationLogRepository)
    {
        $this->operationLogRepository = $operationLogRepository;
    }

    public function toOptionArray()
    {
        $topicNames = $this->operationLogRepository->getAllTopicNames();
        $options = [];
        foreach ($topicNames as $topicName) {
            $options[] = [
                'label' => $topicName,
                'value' => $topicName
            ];
        }
        return $options;
    }
}

