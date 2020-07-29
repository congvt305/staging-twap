<?php
/**
 * @author Eguana Team
 * @copyright Copyright (c) 2019 Eguana {http://eguanacommerce.com}
 * Created by PhpStorm
 * User: brian
 * Date: 2020/07/14
 * Time: 10:17 AM
 */

namespace Eguana\ChangeStatus\Controller\Adminhtml\System\Config;

use Eguana\ChangeStatus\Model\RmaStatusChanger;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\Result\Json;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Rma\Api\RmaRepositoryInterface;

class RmaCheck extends Action
{
    /**
     * @var RmaStatusChanger
     */
    private $rmaStatusChanger;
    /**
     * @var RmaRepositoryInterface
     */
    private $rmaRepository;
    /**
     * @var JsonFactory
     */
    private $jsonFactory;

    /**
     * RmaCheck constructor.
     * @param Context $context
     * @param JsonFactory $jsonFactory
     * @param RmaStatusChanger $rmaStatusChanger
     * @param RmaRepositoryInterface $rmaRepository
     */
    public function __construct(
        Action\Context $context,
        JsonFactory $jsonFactory,
        RmaStatusChanger $rmaStatusChanger,
        RmaRepositoryInterface $rmaRepository
    ) {
        $this->rmaStatusChanger = $rmaStatusChanger;
        $this->rmaRepository = $rmaRepository;
        $this->jsonFactory = $jsonFactory;
        parent::__construct($context);
    }

    public function execute()
    {
        $this->rmaStatusChanger->changeRmaStatus();
        $result = $this->jsonFactory->create();

        return $result->setData(['success' => true]);
    }
}
