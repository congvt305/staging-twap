<?php
/**
 * Created by Eguana Team.
 * User: sonia
 * Date: 7/2/20
 * Time: 3:28 PM
 */

namespace Amore\GaTagging\Controller\Test;


use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\ResponseInterface;

class Age extends Action
{
    /**
     * @var \Amore\GaTagging\ViewModel\CommonData
     */
    private $commonData;

    public function __construct(
        \Amore\GaTagging\ViewModel\CommonData $commonData,
        Context $context
    ) {
        parent::__construct($context);
        $this->commonData = $commonData;
    }

    /**
     * Execute action based on request and return result
     *
     * Note: Request will be added as operation argument in future
     *
     * @return \Magento\Framework\Controller\ResultInterface|ResponseInterface
     * @throws \Magento\Framework\Exception\NotFoundException
     */
    public function execute()
    {
        $age = $this->commonData->getApDataCa();
        echo $age;
    }
}
