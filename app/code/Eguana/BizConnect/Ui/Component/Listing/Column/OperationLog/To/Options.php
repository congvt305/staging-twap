<?php
/**
 * @author Eguana Commerce
 * @copyright Copyright (c) 2020 Eguana {https://eguanacommerce.com}
 *  Created by PhpStorm
 *  User: Sonia Park
 *  Date: 3/17/20, 12:54 PM
 *
 */

namespace Eguana\BizConnect\Ui\Component\Listing\Column\OperationLog\To;

use Magento\Framework\Data\OptionSourceInterface;

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
        $toFroms = $this->operationLogRepository->getAllTo();
        $options = [];
        foreach ($toFroms as $toFrom) {
            $options[] = [
                'value' => $toFrom,
                'label' => $toFrom
            ];
        }
        return $options;
    }
}
