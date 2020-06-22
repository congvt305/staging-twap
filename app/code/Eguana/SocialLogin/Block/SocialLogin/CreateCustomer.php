<?php
/**
 * @author Eguana Team
 * @copyriht Copyright (c) 2020 Eguana {http://eguanacommerce.com}
 * Created by PhpStorm
 * User: umer
 * Date: 21/4/20
 * Time: 2:15 PM
 */
namespace Eguana\SocialLogin\Block\SocialLogin;

use Magento\Framework\App\RequestInterface;
use Magento\Framework\View\Element\Template\Context;
use Magento\Framework\View\Element\Template;

/**
 * Class CreateCustomer
 *
 * Class for creating customer form template
 */
class CreateCustomer extends Template
{

    /**
     * @var RequestInterface
     */
    protected $request;

    /**
     * CreateCustomer constructor.
     * @param RequestInterface $request
     * @param Context $context
     */
    public function __construct(
        RequestInterface $request,
        Context $context
    ) {
        $this->request                          = $request;
        parent::__construct($context);
    }

    /**
     * Get post params
     * @return array
     */
    public function getPost()
    {
        return $this->request->getParams();
    }

    /**
     * Get form action
     * @return string
     */
    public function getFormAction()
    {
        return $this->getUrl('customer/account/loginPost/', ['_secure' => true]);
    }
}
