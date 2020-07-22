<?php
/**
 * @author Eguana Team
 * @copyriht Copyright (c) 2020 Eguana {http://eguanacommerce.com}
 * Created by PhpStorm
 * User: yasir
 * Date: 6/23/20
 * Time: 11:14 PM
 */
namespace Eguana\Magazine\Block;

use Eguana\Magazine\Api\MagazineRepositoryInterface;
use Magento\Framework\View\Element\Template;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\View\Element\Template\Context;
use Psr\Log\LoggerInterface;

/**
 * This class is used for breadcrumps for detail page
 * Class ListBlock
 */
class ListBlock extends Template
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
     * ListBlock constructor.
     * @param Context $context
     * @param MagazineRepositoryInterface $magazineRepository
     * @param LoggerInterface $logger
     * @param array $data
     */
    public function __construct(
        Context $context,
        StoreManagerInterface $storeManager,
        MagazineRepositoryInterface $magazineRepository,
        LoggerInterface $logger,
        StoreManagerInterface $storeManagerInterface,
        array $data = []
    ) {
        $this->magazineRepository = $magazineRepository;
        $this->storeManagerInterface = $storeManagerInterface;
        $this->logger = $logger;
        parent::__construct($context, $data);
    }
    /**
     * @return $this|ListBlock
     */
    public function _prepareLayout()
    {
        try {
            parent::_prepareLayout();

            if ($breadcrumbsBlock = $this->getLayout()->getBlock('breadcrumbs')) {
                $breadcrumbsBlock->addCrumb(
                    'home',
                    [
                    'label' => __('Home'),
                    'title' => __('Go to Home Page'),
                    'link' => $this->storeManagerInterface->getStore()->getBaseUrl()
                    ]
                );
                $this->pageConfig->getTitle()->set(__("I'm LANEIGE"));

                $breadcrumbsBlock->addCrumb(
                    'explore',
                    [
                    'label' => __('Magazine'),
                    'title' => __('Magazine')
                    ]
                );
            }
        } catch (\Exception $exception) {
            $this->logger->debug($exception->getMessage());
        }
        return $this;
    }
}
