<?php
/**
 * @author Eguana Team
 * @copyriht Copyright (c) 2020 Eguana {http://eguanacommerce.com}
 * Created by PhpStorm
 * User: umer
 * Date: 21/4/20
 * Time: 2:15 PM
 */
namespace Eguana\SocialLogin\Block\SocialLogin;

use Magento\Framework\App\RequestInterface;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Magento\Store\Model\StoreManagerInterface;
use Psr\Log\LoggerInterface;

/**
 * Class CreateCustomer
 *
 * Class for creating customer form template
 */
class CreateCustomer extends Template
{

    /**
     * @var RequestInterface
     */
    protected $request;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * CreateCustomer constructor.
     * @param RequestInterface $request
     * @param StoreManagerInterface $storeManager
     * @param Context $context
     * @param LoggerInterface $logger
     */
    public function __construct(
        RequestInterface $request,
        StoreManagerInterface $storeManager,
        Context $context,
        LoggerInterface $logger
    ) {
        $this->request                          = $request;
        $this->storeManager                     = $storeManager;
        $this->logger                           = $logger;
        parent::__construct($context);
    }

    /**
     * Get post params
     * @return array
     */
    public function getPost()
    {
        return $this->request->getParams();
    }

    /**
     * Get form action
     * @return string
     */
    public function getFormAction()
    {
        return $this->getUrl('customer/account/loginPost/', ['_secure' => true]);
    }

    /**
     * Get current store name
     * @return string
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getCurrentStoreName()
    {
        $storeName = null;
        try {
            $storeName = $this->storeManager->getStore()->getName();
        } catch (\Exception $e) {
            $this->logger->error($e->getMessage());
        }
        return $storeName;
    }
}
