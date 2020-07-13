<?php
/**
 * @author Eguana Team
 * @copyriht Copyright (c) 2020 Eguana {http://eguanacommerce.com}
 * Created by PhpStorm
 * User: yasir
 * Date: 6/18/20
 * Time: 3:14 AM
 */
namespace Eguana\Magazine\ViewModel;

use Eguana\Magazine\Api\MagazineRepositoryInterface;
use Eguana\Magazine\Helper\Data as DataAlias;
use Eguana\Magazine\Model\Magazine as MagazineAlias;
use Magento\Cms\Model\Template\FilterProvider;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\View\Element\Block\ArgumentInterface;
use Magento\Store\Model\StoreManagerInterface;

/**
 * ViewModel helper for .phtml file
 *
 * Class Magazine
 */
class Detail implements ArgumentInterface
{
    /**
     * @var MagazineRepositoryInterface
     */
    private $magazineRepository;

    /**
     * @var RequestInterface
     */
    private $requestInterface;

    /**
     * @var StoreManagerInterface
     */
    private $storeManagerInterface;

    /**
     * @var FilterProvider
     */
    private $filterProvider;

    /**
     * Magazine constructor.
     * @param DataAlias $helperData
     */
    public function __construct(
        MagazineRepositoryInterface $magazineRepository,
        RequestInterface $requestInterface,
        StoreManagerInterface $storeManagerInterface,
        FilterProvider $filterProvider
    ) {
        $this->magazineRepository = $magazineRepository;
        $this->requestInterface = $requestInterface;
        $this->storeManagerInterface = $storeManagerInterface;
        $this->filterProvider = $filterProvider;
    }

    /**
     * this function will give content
     * @return MagazineAlias
     */
    public function getMagazine()
    {
        $id = $this->requestInterface->getParam('id');
        $magazine = $this->magazineRepository->getById($id);
        return $magazine;
    }

    /**
     * get filter cintent
     * @param $content
     * @return mixed
     */
    public function contentFiltering($content)
    {
        try {
            $storeId = $this->storeManagerInterface->getStore()->getId();
        } catch (\Exception $exception) {
            $this->_logger->debug($exception->getMessage());
        }
        return $this->filterProvider->getBlockFilter()->setStoreId($storeId)->filter($content);
    }
}
