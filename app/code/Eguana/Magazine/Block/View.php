<?php
/**
 * @author Eguana Team
 * @copyriht Copyright (c) 2020 Eguana {http://eguanacommerce.com}
 * Created by PhpStorm
 * User: yasir
 * Date: 6/23/20
 * Time: 11:17 PM
 */
namespace Eguana\Magazine\Block;

use Eguana\Magazine\Api\Data\MagazineInterface as MagazineInterfaceAlias;
use Eguana\Magazine\Api\MagazineRepositoryInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\View\Element\Template;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\View\Element\Template\Context;
use Psr\Log\LoggerInterface;

/**
 * This class is used for breadcrumbs
 * Class View
 */
class View extends Template
{
    /**
     * @var MagazineRepositoryInterface
     */
    private $magazineRepository;

    /**
     * @var StoreManagerInterface
     */
    private $storeManagerInterface;

    /**
     * @var RequestInterface
     */
    private $requestInterface;

    /**
     * @var LoggerInterface;
     */
    private $logger;

    /**
     * View constructor.
     * @param Context $context
     * @param MagazineRepositoryInterface $magazineRepository
     * @param StoreManagerInterface $storeManagerInterface
     * @param array $data
     * @param LoggerInterface $logger

     */
    public function __construct(
        Context $context,
        MagazineRepositoryInterface $magazineRepository,
        StoreManagerInterface $storeManagerInterface,
        RequestInterface $requestInterface,
        LoggerInterface $logger,
        array $data = []
    ) {
        $this->magazineRepository = $magazineRepository;
        $this->storeManagerInterface = $storeManagerInterface;
        $this->requestInterface = $requestInterface;
        $this->logger = $logger;
        parent::__construct($context, $data);
    }
    /**
     * This method is used to get Magazine
     * @return MagazineInterfaceAlias|MagazineAlias
     */
    public function getMagazine()
    {
        try {
            /** @var MagazineAlias $magazine */
            $id = $this->requestInterface->getParam('id');
            $magazine = $this->magazineRepository->getById($id);
            return $magazine;
        } catch (\Exception $exception) {
            $this->logger->debug($exception->getMessage());
        }
    }
    /**
     * This function is used to set pagetitle and breadcrumbs
     * @return $this|View
     */
    public function _prepareLayout()
    {
        parent::_prepareLayout();
        try {
            $this->pageConfig->getTitle()->set($this->getMagazine()->getTitle());
            if ($breadcrumbsBlock = $this->getLayout()->getBlock('breadcrumbs')) {
                $breadcrumbsBlock->addCrumb(
                    'home',
                    [
                    'label' => __('Home'),
                    'title' => __('Go to Home Page'),
                    'link' => $this->storeManagerInterface->getStore()->getBaseUrl()
                    ]
                );
                $breadcrumbsBlock->addCrumb(
                    'Magazine',
                    [
                    'label' => __('Magazine'),
                    'title' => __('Magazine'),
                    'link' => $this->storeManagerInterface->getStore()->getBaseUrl() . 'magazine'
                    ]
                );
                if (!empty($this->getMagazine()->getData())) {
                    $this->pageConfig->getTitle()->set(__($this->getMagazine()->getTitle()));
                    $breadcrumbsBlock->addCrumb(
                        'main_title',
                        [
                        'label' => __($this->getMagazine()->getTitle()),
                        'title' => __($this->getMagazine()->getTitle())
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
