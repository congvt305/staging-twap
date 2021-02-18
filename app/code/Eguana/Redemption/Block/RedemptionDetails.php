<?php
/**
 * @author Eguana Team
 * @copyriht Copyright (c) 2020 Eguana {http://eguanacommerce.com}
 * Created by PhpStorm
 * User: arslan
 * Date: 22/10/20
 * Time: 4:00 PM
 */
namespace Eguana\Redemption\Block;

use Eguana\Redemption\Api\RedemptionRepositoryInterface;
use Eguana\Redemption\Model\Redemption;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\DataObject\IdentityInterface;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Magento\Store\Model\StoreManagerInterface;
use Psr\Log\LoggerInterface;

/**
 * class RedemptionDetails
 *
 * block for details.phtml
 */
class RedemptionDetails extends Template implements IdentityInterface
{
    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var RedemptionRepositoryInterface
     */
    private $redemptionRepository;

    /**
     * @var RequestInterface
     */
    private $requestInterface;

    /**
     * Redemption constructor.
     *
     * @param Context $context
     * @param RedemptionRepositoryInterface $redemptionRepository
     * @param StoreManagerInterface $storeManager
     * @param RequestInterface $requestInterface
     * @param LoggerInterface $logger
     * @param array $data
     */
    public function __construct(
        Context $context,
        RedemptionRepositoryInterface $redemptionRepository,
        StoreManagerInterface $storeManager,
        RequestInterface $requestInterface,
        LoggerInterface $logger,
        array $data = []
    ) {
        $this->storeManager = $storeManager;
        $this->redemptionRepository = $redemptionRepository;
        $this->requestInterface = $requestInterface;
        $this->logger = $logger;
        parent::__construct($context, $data);
    }

    /**
     * @return array
     */
    public function getIdentities()
    {
        return [Redemption::CACHE_TAG];
    }

    /**
     * get Redemption Method
     *
     * @return Redemption
     */
    public function getRedemption()
    {
        /** @var Redemption $redemption */
        $id = $this->requestInterface->getParam('redemption_id');
        $redemption = $this->redemptionRepository->getById($id);
        return $redemption;
    }

    /**
     * To set page title and breadcrumb
     * This function will set the page title according to the current redemption title
     * and it will also set the breadcrumb
     * @return $this|RedemptionDetails
     */

    public function _prepareLayout()
    {

        parent::_prepareLayout();
        if ($this->getRequest()->getParam('redemption_id')) {
            $redemptionData = $this->getRedemption();
            if (!empty($redemptionData->getData())) {
                $metaTitle = $redemptionData->getMetaTitle();
                $metaKeywords = $redemptionData->getMetaKeywords();
                $metaDescription = $redemptionData->getMetaDescription();
                $title = $metaTitle ? $metaTitle : $redemptionData->getTitle();
                $this->pageConfig->getTitle()->set($title);
                $this->pageConfig->setMetaTitle($metaTitle);
                $this->pageConfig->setKeywords($metaKeywords);
                $this->pageConfig->setDescription($metaDescription);
            }
        }
        try {
            if ($breadcrumbsBlock = $this->getLayout()->getBlock('breadcrumbs')) {
                $breadcrumbsBlock->addCrumb(
                    'home',
                    [
                        'label' => __('Home'),
                        'title' => __('Go to Home Page'),
                        'link' => $this->storeManager->getStore()->getBaseUrl()
                    ]
                );
                $breadcrumbsBlock->addCrumb(
                    'redemption',
                    [
                        'label' => __('Brand activity'),
                        'title' => __('Brand activity'),
                    ]
                );
                if (!empty($this->getRedemption()->getData())) {
                    $breadcrumbsBlock->addCrumb(
                        'main_title',
                        [
                            'label' => __($this->getRedemption()->getTitle()),
                            'title' => __($this->getRedemption()->getTitle())
                        ]
                    );
                }
            }
        } catch (\Exception $exception) {
            $this->logger->debug($exception->getMessage());
        }
        return $this;
    }
}
