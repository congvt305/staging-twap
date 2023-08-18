<?php
/**
 * Created by Eguana.
 * User: Brian
 * Date: 2020-06-30
 * Time: 오전 10:36
 */

namespace Amore\Sap\Controller\Adminhtml;

use Amore\Sap\Logger\Logger;
use CJ\Middleware\Model\Sap\Connection\Request;
use Amore\Sap\Model\Source\Config;
use Magento\Backend\App\Action;
use Magento\Backend\Model\View\Result\Redirect;
use Magento\Framework\Controller\ResultFactory;
use CJ\Middleware\Helper\Data as MiddlewareHelper;

abstract class AbstractAction extends Action
{
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
     * @var MiddlewareHelper
     */
    protected $middlewareHelper;

    /**
     * AbstractAction constructor.
     * @param Action\Context $context
     * @param Request $request
     * @param Logger $logger
     * @param Config $config
     */
    public function __construct(
        Action\Context $context,
        Request $request,
        Logger $logger,
        Config $config,
        MiddlewareHelper $middlewareHelper
    ) {
        parent::__construct($context);
        $this->request = $request;
        $this->logger = $logger;
        $this->config = $config;
        $this->middlewareHelper = $middlewareHelper;
    }

    public function execute()
    {
        /** @var Redirect $resultRedirect */
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        return $resultRedirect->setPath('sales/order/index');
    }
}
