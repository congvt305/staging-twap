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
use Magento\Framework\View\Element\Block\ArgumentInterface;
use Magento\Framework\App\RequestInterface;

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
     * Magazine constructor.
     * @param \Eguana\Magazine\Helper\Data $helperData
     */
    public function __construct(
        MagazineRepositoryInterface $magazineRepository,
        RequestInterface $requestInterface
    ) {
        $this->magazineRepository = $magazineRepository;
        $this->requestInterface = $requestInterface;
    }

    /**
     * SHORT DESCRIPTION
     * LONG DESCRIPTION LINE BY LINE
     * @return \Eguana\Magazine\Model\Magazine
     */
    public function getMagazine()
    {
        $id = $this->requestInterface->getParam('id');
        $magazine = $this->magazineRepository->getById($id);
        return $magazine;
    }
}
