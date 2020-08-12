<?php
/**
 * @author Eguana Team
 * @copyright Copyright (c) 2020 Eguana {http://eguanacommerce.com}
 * Created by PhpStorm
 * User: umer
 * Date: 12/8/20
 * Time: 2:15 PM
 */
namespace Amore\CustomerRegistration\Plugin;

use Magento\Customer\Controller\Account\CreatePost;
use Magento\Customer\Model\Session;
use Magento\Framework\App\Response\RedirectInterface;
use Magento\Framework\Controller\Result\RedirectFactory;
use Magento\Framework\UrlFactory;
use Magento\Framework\Controller\Result\Redirect;

/**
 * Class AddReferrer
 *
 * Add barcode params
 */
class AddReferrer
{
    /**
     * @var Session
     */
    protected $session;

    /**
     * @var RedirectFactory
     */
    protected $resultRedirectFactory;

    /**
     * @var RedirectInterface
     */
    protected $redirect;

    /**
     * @var \Magento\Framework\UrlInterface
     */
    protected $urlModel;

    /**
     * AddReferrer constructor.
     * @param Session $customerSession
     * @param RedirectFactory $resultRedirectFactory
     * @param RedirectInterface $redirect
     * @param UrlFactory $urlFactory
     */
    public function __construct(
        Session $customerSession,
        RedirectFactory $resultRedirectFactory,
        RedirectInterface $redirect,
        UrlFactory $urlFactory
    ) {
        $this->session = $customerSession;
        $this->resultRedirectFactory = $resultRedirectFactory;
        $this->redirect = $redirect;
        $this->urlModel = $urlFactory->create();
    }

    /**
     * Add referrer and partner id to param if exists
     * Only execute plugin if customer is not logged in
     * @param CreatePost $subject
     * @param $result
     * @return Redirect
     */
    public function afterExecute(
        CreatePost $subject,
        $result
    ) {
        if (!$this->session->isLoggedIn()) {
            $params = $subject->getRequest()->getParams();
            $referrerCode = $params['referrer_code'];
            $partnerId = $params['partner_id'];
            $redirectParams = ['referrer_code' => $referrerCode, 'favorite_store' => $partnerId];
            if (!empty($referrerCode) && !empty($partnerId)) {
                $url = $this->urlModel->getUrl('customer/account/create/referrer_code/' . $referrerCode . '/favorite_store/' . $partnerId, ['_secure' => true]);
                $result = $this->resultRedirectFactory->create()
                    ->setUrl($url);
            }
        }
        return $result;
    }
}
