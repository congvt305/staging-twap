<?php
/**
 * @author Eguana Team
 * @copyriht Copyright (c) 2020 Eguana {http://eguanacommerce.com}
 * Created by PhpStorm
 * User: yasir
 * Date: 6/24/20
 * Time: 1:24 AM
 */
namespace Eguana\Magazine\Block\Monthly;

use Magento\Framework\App\RequestInterface;
use Magento\Framework\View\Element\Template;
use Eguana\Magazine\Api\MagazineRepositoryInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\Stdlib\DateTime\DateTime;

/**
 * This is used for Monthly Magazine details
 *
 * Class Detail
 */
class Detail extends Template
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
     * @var DateTime
     */
    private $dateTime;
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
        DateTime $dateTime,
        array $data = []
    ) {
        $this->magazineRepository = $magazineRepository;
        $this->storeManagerInterface = $storeManagerInterface;
        $this->requestInterface = $requestInterface;
        $this->dateTime = $dateTime;
        parent::__construct($context, $data);
    }

    /**
     * This function is used for breadcrumbs
     * @return $this|View
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function _prepareLayout()
    {
        $month = $this->requestInterface->getParam('month');
        $year = $this->requestInterface->getParam('year');
        $date = $this->dateTime->gmtDate('Y-m-d H:i:s', $year . '-' . $month . '-' . 1 . ' 00:00:00');
        $month = $this->dateTime->gmtDate('F', $date);
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
            if (isset($month)) {
                $breadcrumbsBlock->addCrumb(
                    'main_title',
                    [
                        'label' => __($month),
                        'title' => __($month)
                    ]
                );
            }
        }

        return $this;
    }
}
