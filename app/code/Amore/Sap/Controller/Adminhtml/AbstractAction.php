<?php
/**
 * Created by Eguana.
 * User: Brian
 * Date: 2020-06-30
 * Time: 오전 10:36
 */

namespace Amore\Sap\Controller\Adminhtml;

use Amore\Sap\Logger\Logger;
use Amore\Sap\Model\Connection\Request;
use Amore\Sap\Model\Source\Config;
use Magento\Backend\App\Action;
use Magento\Backend\Model\View\Result\Redirect;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Serialize\Serializer\Json;

abstract class AbstractAction extends Action
{
    /**
     * @var Json
     */
    protected $json;
    /**
     * @var Request
     */
    protected $request;
    /**
     * @var Logger
     */
    protected $logger;
    /**
     * @var Config
     */
    protected $config;

    /**
     * AbstractAction constructor.
     * @param Action\Context $context
     * @param Json $json
     * @param Request $request
     * @param Logger $logger
     * @param Config $config
     */
    public function __construct(
        Action\Context $context,
        Json $json,
        Request $request,
        Logger $logger,
        Config $config
    ) {
        parent::__construct($context);
        $this->json = $json;
        $this->request = $request;
        $this->logger = $logger;
        $this->config = $config;
    }

    public function execute()
    {
        /** @var Redirect $resultRedirect */
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        return $resultRedirect->setPath('sales/order/index');
    }
}
