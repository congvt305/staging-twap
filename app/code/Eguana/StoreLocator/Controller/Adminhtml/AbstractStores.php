<?php
/**
 * Created by PhpStorm.
 * User: jason
 * Date: 2016-09-19
 * Time: 오전 11:40
 */

namespace Eguana\StoreLocator\Controller\Adminhtml;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\Controller\Result\JsonFactory;
use Eguana\StoreLocator\Model\StoreInfo;
use Psr\Log\LoggerInterface;
use Magento\Framework\Filesystem\Driver\File;
use Magento\Framework\Filesystem;
use Eguana\StoreLocator\Helper\ConfigData;
use Magento\Backend\Model\SessionFactory;
use Eguana\StoreLocator\Api\StoreInfoRepositoryInterface;
use Eguana\StoreLocator\Model\ResourceModel\StoreInfo as StoreInfoResource;

/**
 * Abstract class for controller
 *
 * Class AbstractStores
 *  Eguana\StoreLocator\Controller\Adminhtml
 */
abstract class AbstractStores extends Action
{
    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    const ADMIN_RESOURCE = 'Eguana_StoreLocator::stores_info';

    /**
     * @var PageFactory
     */
    protected $_resultPageFactory;

    /**
     * @var StoreInfo
     */
    protected $_storeInfo;

    /**
     * @var JsonFactory
     */
    protected $_resultJsonFactory;

    /**
     * @var LoggerInterface
     */
    protected $_logger;

    /**
     * @var Filesystem
     */
    protected $_fileSystem;

    /**
     * @var File
     */
    protected $_fileDriver;

    /**
     * @var ConfigData
     */
    protected $_storesHelper;

    /**
     * @var SessionFactory
     */
    protected $_session;

    /**
     * @var StoreInfoRepositoryInterface
     */
    protected $_storeInfoRepo;

    /**
     * @var StoreInfoResource
     */
    protected $_storeInfoResource;

    /**
     * AbstractStores constructor.
     * @param Context $context
     * @param PageFactory $resultPageFactory
     * @param JsonFactory $resultJsonFactory
     * @param StoreInfo $storeInfo
     * @param LoggerInterface $logger
     * @param File $fileDriver
     * @param Filesystem $filesystem
     * @param ConfigData $storeHelper
     * @param SessionFactory $session
     * @param StoreInfoRepositoryInterface $storeInfoRepository
     * @param StoreInfoResource $storeInfoResource
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        JsonFactory $resultJsonFactory,
        StoreInfo $storeInfo,
        LoggerInterface $logger,
        File $fileDriver,
        Filesystem $filesystem,
        ConfigData $storeHelper,
        SessionFactory $session,
        StoreInfoRepositoryInterface $storeInfoRepository,
        StoreInfoResource $storeInfoResource
    ) {
        parent::__construct($context);
        $this->_resultPageFactory = $resultPageFactory;
        $this->_resultJsonFactory = $resultJsonFactory;
        $this->_storeInfo = $storeInfo;
        $this->_logger = $logger;
        $this->_fileSystem = $filesystem;
        $this->_fileDriver = $fileDriver;
        $this->_storesHelper = $storeHelper;
        $this->_session = $session->create();
        $this->_storeInfoRepo = $storeInfoRepository;
        $this->_storeInfoResource = $storeInfoResource;
    }

    /**
     * init page
     * @param $resultPage
     * @return mixed
     */
    protected function initPage($resultPage)
    {
        $resultPage->getConfig()->getTitle()->prepend(__('Stores'));
        return $resultPage;
    }
}
