<?php

/**
 * Created by PhpStorm
 * User: Phat Pham
 * Date:  23.06.2021
 */

namespace Amore\CustomerRegistration\Block;

use Magento\Framework\View\Element\Template;

/**
 * Class Datalayer
 * @package Amore\CustomerRegistration\Block
 */
class Datalayer extends Template
{
    /**
     * @var \Magento\Framework\App\Response\RedirectInterface
     */
    protected $redirect;

    /**
     * Datalayer constructor.
     * @param \Magento\Framework\App\Response\RedirectInterface $redirect
     * @param Template\Context $context
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\App\Response\RedirectInterface $redirect,
        Template\Context $context,
        array $data = [])
    {
        $this->redirect = $redirect;
        parent::__construct($context, $data);
    }

    public function getRefererUrl() {
        return $this->redirect->getRefererUrl();
    }

}
