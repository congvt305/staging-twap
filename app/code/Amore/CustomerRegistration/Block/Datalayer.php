<?php

/**
 * Created by PhpStorm
 * User: Phat Pham
 * Date:  23.06.2021
 */

namespace Amore\CustomerRegistration\Block;

use Magento\Framework\View\Element\Template;
use Magento\Customer\Model\Session;

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
     * @var Session
     */
    protected $session;

    /**
     * Datalayer constructor.
     * @param \Magento\Framework\App\Response\RedirectInterface $redirect
     * @param Session $customerSession
     * @param Template\Context $context
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\App\Response\RedirectInterface $redirect,
        Session $customerSession,
        Template\Context $context,
        array $data = [])
    {
        $this->redirect = $redirect;
        $this->session = $customerSession;
        parent::__construct($context, $data);
    }

    /**
     * @return string
     */
    public function getRefererUrl() {
        return $this->redirect->getRefererUrl();
    }

    /**
     * @return mixed
     */
    public function getEventRegisterSuccess() {
        return $this->session->getEventRegisterSuccess();
    }

    /**
     * @param $value
     * @return mixed
     */
    public function setEventRegisterSuccess($value) {
        return $this->session->setEventRegisterSuccess($value);
    }

}
