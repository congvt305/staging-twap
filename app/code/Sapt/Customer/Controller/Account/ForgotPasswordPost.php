<?php


namespace Sapt\Customer\Controller\Account;


use Magento\Customer\Api\AccountManagementInterface;
use Magento\Customer\Model\AccountManagement;
use Magento\Customer\Model\Session;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Escaper;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\SecurityViolationException;
use Magento\Store\Model\StoreManagerInterface;

class ForgotPasswordPost extends \Magento\Customer\Controller\Account\ForgotPasswordPost
{
    const MY_SWS_STORE_CODE = 'my_sulwhasoo';
    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * ForgotPasswordPost constructor.
     * @param StoreManagerInterface $storeManager
     * @param Context $context
     * @param Session $customerSession
     * @param AccountManagementInterface $customerAccountManagement
     * @param Escaper $escaper
     */
    public function __construct(
        StoreManagerInterface $storeManager,
        Context $context,
        Session $customerSession,
        AccountManagementInterface $customerAccountManagement,
        Escaper $escaper
    )
    {
        parent::__construct($context, $customerSession, $customerAccountManagement, $escaper);
        $this->storeManager = $storeManager;
    }

    /**
     * Forgot customer password action
     *
     * @return \Magento\Framework\Controller\Result\Redirect
     * @throws \Zend_Validate_Exception
     */
    public function execute()
    {
        if ($this->storeManager->getStore()->getCode() != self::MY_SWS_STORE_CODE) {
            return parent::execute();
        }

        /** @var \Magento\Framework\Controller\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();
        $email = (string)$this->getRequest()->getPost('email');
        if ($email) {
            if (!\Zend_Validate::is($email, \Magento\Framework\Validator\EmailAddress::class)) {
                $this->session->setForgottenEmail($email);
                $this->messageManager->addErrorMessage(
                    __('The email address is incorrect. Verify the email address and try again.')
                );
                return $resultRedirect->setPath('*/*/forgotpassword');
            }

            try {
                $this->customerAccountManagement->initiatePasswordReset(
                    $email,
                    AccountManagement::EMAIL_RESET
                );
            } catch (NoSuchEntityException $exception) {
                // Do nothing, we don't want anyone to use this action to determine which email accounts are registered.
            } catch (SecurityViolationException $exception) {
                $this->messageManager->addErrorMessage($exception->getMessage());
                return $resultRedirect->setPath('*/*/forgotpassword');
            } catch (\Exception $exception) {
                $this->messageManager->addExceptionMessage(
                    $exception,
                    __('We\'re unable to send the password reset email.')
                );
                return $resultRedirect->setPath('*/*/forgotpassword');
            }
            $this->messageManager->addSuccessMessage($this->getSuccessMessage($email));
            return $resultRedirect->setPath('*/*/findpasswordcomplete');
        } else {
            $this->messageManager->addErrorMessage(__('Please enter your email.'));
            return $resultRedirect->setPath('*/*/forgotpassword');
        }
    }
}
