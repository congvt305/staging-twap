<?php
/**
 * @author Eguana Team
 * @copyriht Copyright (c) 2020 Eguana {http://eguanacommerce.com}
 * Created by PhpStorm
 * User: arslan
 * Date: 15/9/20
 * Time: 7:33 PM
 */
namespace Eguana\CustomCatalog\Plugin\Controller\Index;

use Magento\Framework\App\Response\RedirectInterface as RedirectInterfaceAlias;
use Magento\Wishlist\Controller\Index\Add as AddAlias;

/**
 * This class is used to change the URL when add product to wish list
 * Class Add
 */
class Add
{
    /**
     * @var RedirectInterfaceAlias
     */
    private $redirect;

    /**
     * Add constructor.
     * @param RedirectInterfaceAlias $redirect
     */
    public function __construct(RedirectInterfaceAlias $redirect)
    {
        $this->redirect = $redirect;
    }

    /**
     * After Execute Plugin
     * @param AddAlias $subject
     * @param $result
     * @return mixed
     */
    public function afterExecute(AddAlias $subject, $result)
    {
        $result->setPath($this->redirect->getRefererUrl());
        return $result;
    }
}
