<?php
/**
 * Created by PhpStorm.
 * User: jason
 * Date: 2016-09-21
 * Time: 오후 4:43
 */

namespace Eguana\StoreLocator\Controller\Info;

use Eguana\StoreLocator\Helper\ConfigData;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\Result\ForwardFactory;
use Magento\Framework\Controller\Result\RedirectFactory;
use Magento\Framework\View\Result\PageFactory;

/**
 * Abstract controller class for front action controllers
 *
 * Class AbstractStores
 *  Eguana\StoreLocator\Controller\Info
 */
abstract class AbstractStores extends Action
{
    /**
     * @var PageFactory
     */
    protected $_resultPageFactory;

    /**
     * @var ForwardFactory
     */
    protected $_resultForwardFactory;

    /**
     * @var RedirectFactory
     */
    protected $_resultRedirectFactory;

    /**
     * @var ConfigData
     */
    protected $_storesHelper;

    /**
     * AbstractStores constructor.
     * @param Context $context
     * @param ForwardFactory $resultForwardFactory
     * @param PageFactory $resultPageFactory
     * @param ConfigData $storesHelper
     */
    public function __construct(
        Context $context,
        ForwardFactory $resultForwardFactory,
        PageFactory $resultPageFactory,
        ConfigData $storesHelper
    ) {
        $this->_resultForwardFactory = $resultForwardFactory;
        $this->_resultPageFactory = $resultPageFactory;
        $this->_resultRedirectFactory = $context->getResultRedirectFactory();
        $this->_storesHelper = $storesHelper;
        parent::__construct($context);
    }
}
