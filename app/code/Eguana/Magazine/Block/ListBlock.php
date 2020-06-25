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

use Magento\Framework\View\Element\Template;
use Eguana\Magazine\Api\MagazineRepositoryInterface;
use Magento\Store\Model\StoreManagerInterface;

/**
 * This class is used for breadcrumps for detail page
 *
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
     * @param Template\Context $context
     * @param MagazineRepositoryInterface $magazineRepository
     * @param array $data
     */
    public function __construct(
        Template\Context $context,
        StoreManagerInterface $storeManager,
        MagazineRepositoryInterface $magazineRepository,
        StoreManagerInterface $storeManagerInterface,
        array $data = []
    ) {
        $this->magazineRepository = $magazineRepository;
        $this->storeManagerInterface = $storeManagerInterface;
        parent::__construct($context, $data);
    }

    /**
     * This class is used for breadcrumps for detail page
     * @return $this|ListBlock
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function _prepareLayout()
    {
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
            $this->pageConfig->getTitle()->set(__('LANEIGE'));

            $breadcrumbsBlock->addCrumb(
                'explore',
                [
                    'label' => __('Magazine'),
                    'title' => __('Magazine')
                ]
            );
        }

        return $this;
    }
}
