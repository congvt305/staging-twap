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

use Eguana\Magazine\Api\MagazineRepositoryInterface;
use Magento\Framework\View\Element\Template;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\App\RequestInterface;

/**
 * This class is used for breadcrumps
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
     * View constructor.
     * @param Template\Context $context
     * @param MagazineRepositoryInterface $magazineRepository
     * @param StoreManagerInterface $storeManagerInterface
     * @param array $data
     */
    public function __construct(
        Template\Context $context,
        MagazineRepositoryInterface $magazineRepository,
        StoreManagerInterface $storeManagerInterface,
        RequestInterface $requestInterface,
        array $data = []
    ) {
        $this->magazineRepository = $magazineRepository;
        $this->storeManagerInterface = $storeManagerInterface;
        $this->requestInterface = $requestInterface;
        parent::__construct($context, $data);
    }

    /**
     * This function is used for getMagazine
     * @return MagazineAlias
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getMagazine()
    {
        /** @var MagazineAlias $magazine */
        $id = $this->requestInterface->getParam('id');
        $magazine = $this->magazineRepository->getById($id);
        return $magazine;
    }

    /**
     * This function is used for breadcrumbs
     * @return $this|View
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

        return $this;
    }
}
