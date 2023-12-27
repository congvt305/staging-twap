<?php
namespace Magenest\Popup\Controller\Popup;

use Magento\Framework\App\ObjectManager;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\Result\Raw;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Validator\EmailAddress;

class Subscriber extends \Magento\Newsletter\Controller\Subscriber
{

    /** @var \Magenest\Popup\Helper\Data $_helperData */
    protected $_helperData;

    /** @var \Magenest\Popup\Model\PopupFactory $_popupFactory */
    protected $_popupFactory;

    /** @var  \Magenest\Popup\Model\LogFactory $_popupLogFactory */
    protected $_popupLogFactory;
    /**
     * @var \Magento\Customer\Api\AccountManagementInterface $customerAccountManagement
     */
    protected $customerAccountManagement;

    /** @var EmailAddress $emailValidator */
    private $emailValidator;

    /** @var \Psr\Log\LoggerInterface $_logger */
    protected $_logger;

    /** @var \Magento\Framework\App\Request\DataPersistorInterface $dataPersistor */
    private $dataPersistor;
    /**
     * @var \Magento\Framework\Mail\Template\TransportBuilder
     */
    protected $_transportBuilder;

    /**
     * @var \Magento\Framework\Translate\Inline\StateInterface
     */
    protected $inlineTranslation;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var \Magento\Framework\Json\Helper\Data
     */
    protected $_jsonHelper;

    /**
     * @var \Magento\Framework\Serialize\Serializer\Json
     */
    protected $_json;

    /**
     * Subscriber constructor.
     * @param \Magenest\Popup\Helper\Data $helperData
     * @param \Magenest\Popup\Model\PopupFactory $popupFactory
     * @param \Magenest\Popup\Model\LogFactory $popupLogFactory
     * @param \Magento\Customer\Api\AccountManagementInterface $customerAccountManagement
     * @param EmailAddress $emailValidator
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\Newsletter\Model\SubscriberFactory $subscriberFactory
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Customer\Model\Url $customerUrl
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Magento\Framework\App\Request\DataPersistorInterface $dataPersistor
     * @param \Magento\Framework\Mail\Template\TransportBuilder $transportBuilder
     * @param \Magento\Framework\Translate\Inline\StateInterface $inlineTranslation
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Framework\Json\Helper\Data $jsonHelper
     * @param \Magento\Framework\Serialize\Serializer\Json $json
     */
    public function __construct(
        \Magenest\Popup\Helper\Data $helperData,
        \Magenest\Popup\Model\PopupFactory $popupFactory,
        \Magenest\Popup\Model\LogFactory $popupLogFactory,
        \Magento\Customer\Api\AccountManagementInterface $customerAccountManagement,
        EmailAddress $emailValidator,
        \Magento\Framework\App\Action\Context $context,
        \Magento\Newsletter\Model\SubscriberFactory $subscriberFactory,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Customer\Model\Url $customerUrl,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\App\Request\DataPersistorInterface $dataPersistor,
        \Magento\Framework\Mail\Template\TransportBuilder $transportBuilder,
        \Magento\Framework\Translate\Inline\StateInterface $inlineTranslation,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Framework\Json\Helper\Data $jsonHelper,
        \Magento\Framework\Serialize\Serializer\Json $json
    ) {
        $this->_helperData = $helperData;
        $this->_popupFactory = $popupFactory;
        $this->_popupLogFactory = $popupLogFactory;
        $this->customerAccountManagement = $customerAccountManagement;
        $this->emailValidator = $emailValidator ?: ObjectManager::getInstance()->get(EmailAddress::class);
        $this->_logger = $logger;
        $this->dataPersistor = $dataPersistor;
        $this->_transportBuilder = $transportBuilder;
        $this->inlineTranslation = $inlineTranslation;
        $this->scopeConfig = $scopeConfig;
        $this->_jsonHelper = $jsonHelper;
        $this->_json = $json;
        parent::__construct($context, $subscriberFactory, $customerSession, $storeManager, $customerUrl);
    }

    /**
     * Validates that the email address isn't being used by a different account.
     *
     * @param string $email
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function validateEmailAvailable($email)
    {
        $out = null;
        $websiteId = $this->_storeManager->getStore()->getWebsiteId();
        if ($this->_customerSession->getCustomerDataObject()->getEmail() !== $email
            && !$this->customerAccountManagement->isEmailAvailable($email, $websiteId)
        ) {
            $out = [
                'status' => 0,
                'message' => __('This email address is already assigned to another user.')
            ];
        }
        return $out;
    }

    /**
     * Validates that if the current user is a guest, that they can subscribe to a newsletter.
     *
     * @return array
     */
    protected function validateGuestSubscription()
    {
        $out = null;
        if (ObjectManager::getInstance()
                ->get(\Magento\Backend\App\ConfigInterface::class)
                ->getValue(
                    \Magento\Newsletter\Model\Subscriber::XML_PATH_ALLOW_GUEST_SUBSCRIBE_FLAG
                ) != 1
            && !$this->_customerSession->isLoggedIn()
        ) {
            $out = [
                'status' => 0,
                'message' => __(
                    'Sorry, but the administrator denied subscription for guests. Please <a href="%1">register</a>.',
                    $this->_customerUrl->getRegisterUrl()
                )
            ];
        }
        return $out;
    }

    /**
     * Validates the format of the email address
     *
     * @param string $email
     * @return array
     */
    protected function validateEmailFormat($email)
    {
        $out = null;
        if (!$this->emailValidator->isValid($email)) {
            $out = [
                'status' => 0,
                'message' => __('Please enter a valid email address.')
            ];
        }
        return $out;
    }

    /**
     * Get Success Message
     *
     * @param int $status
     * @return \Magento\Framework\Phrase
     */
    private function getSuccessMessage($status)
    {
        if ($status === \Magento\Newsletter\Model\Subscriber::STATUS_NOT_ACTIVE) {
            return __('The confirmation request has been sent.');
        }

        return __('Thank you for your subscription.');
    }

    /**
     * Execute
     *
     * @return ResponseInterface|Raw|ResultInterface
     * @throws \Exception
     */
    public function execute()
    {
        $out = [];
        $params = $this->_json->unserialize($this->getRequest()->getContent() ?? 'null');
        if (is_array($params) && !empty($params)) {
            $dataPopup = $params['dataPopup'];
            $popup_type = reset($dataPopup);
            $popup_id = $popup_type['name'];
            if ($popup_type['value'] == \Magenest\Popup\Model\Popup::SUBCRIBE) {
                $out = $this->popupNewsletter($dataPopup);
            } elseif ($popup_type['value'] == \Magenest\Popup\Model\Popup::CONTACT_FORM) {
                $out = $this->popupContactform($dataPopup);
            }

            $popupModel = $this->_popupFactory->create()->load($popup_id);
            $email = '';
            $name = '';
            $comment = '';
            $phone = '';
            foreach ($dataPopup as $data) {
                if (isset($data['name'])) {
                    if ($data['name'] == 'email') {
                        $email = $data['value'];
                    }
                    if ($data['name'] == 'name') {
                        $name = $data['value'];
                    }
                    if ($data['name'] == 'msg') {
                        $comment = $data['value'];
                    }
                    if ($data['name'] == 'comment') {
                        $comment = $data['value'];
                    }
                    if ($data['name'] == 'phone') {
                        $phone = $data['value'];
                    }
                }
            }
            $popupLogModel = $this->_popupLogFactory->create();
            $popupLogModel->setPopupId($popup_id);
            $popupLogModel->setPopupName($popupModel->getPopupName());
            $popupLogModel->setContent(
                'name: ' . $name . '| email: ' . $email . '| phone: ' . $phone . '| comment: ' . $comment . '|'
            );
            $popupLogModel->save();
        }
        $response = $this->resultFactory->create(\Magento\Framework\Controller\ResultFactory::TYPE_RAW);
        $response->setHeader('Content-type', 'text/plain');
        $response->setContents($this->_json->serialize($out));
        return $response;
    }

    /**
     * Get Popup Newsletter
     *
     * @param array $dataPopup
     * @return array|null
     */
    public function popupNewsletter($dataPopup)
    {
        $email = '';
        foreach ($dataPopup as $data) {
            if ($data['name'] == 'email') {
                $email = $data['value'];
                break;
            }
        }
        try {
            if ($this->validateEmailFormat($email) != null) {
                return $this->validateEmailFormat($email);
            }
            if ($this->validateGuestSubscription() != null) {
                return $this->validateGuestSubscription();
            }
            if ($this->validateEmailAvailable($email) != null) {
                return $this->validateEmailAvailable($email);
            }

            $subscriber = $this->_subscriberFactory->create()->loadByEmail($email);
            if ($subscriber->getId()
                && (int)$subscriber->getSubscriberStatus() === \Magento\Newsletter\Model\Subscriber::STATUS_SUBSCRIBED
            ) {
                $out = [
                    'status' => 0,
                    'message' => __('This email address is already subscribed.')
                ];
            } else {
                if ($this->enableMailchimp($dataPopup)) {
                    $this->addEmailToMailChimp($dataPopup);
                }
                $status = (int)$this->_subscriberFactory->create()->subscribe($email);
                $out = [
                    'status' => 1,
                    'message' => $this->getSuccessMessage($status)
                ];
            }
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $out = [
                'status' => 0,
                'message' => __('There was a problem with the subscription: %1', $e->getMessage())
            ];
            $this->_logger->critical($e->getMessage());
        } catch (\Exception $e) {
            $out = [
                'status' => 0,
                'message' => __('Something went wrong with the subscription.')
            ];
            $this->_logger->critical($e->getMessage());
        }
        return $out;
    }

    /**
     * Return Popup Contact Form
     *
     * @param array $dataPopup
     * @return array
     */
    public function popupContactform($dataPopup)
    {
        try {
            if ($this->enableMailchimp($dataPopup)) {
                $this->addEmailToMailChimp($dataPopup);
            }
            $this->sendEmail($this->validatedParams($dataPopup));
            $out = [
                'status' => 1,
                'message' => __(
                    'Thanks for contacting us with your comments and questions. We\'ll respond to you very soon.'
                )
            ];
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $out = [
                'status' => 0,
                'message' => $e->getMessage()
            ];
            $this->dataPersistor->set('contact_us', $this->getRequest()->getParams());
            $this->_logger->critical($e->getMessage());
        } catch (\Exception $e) {
            $this->_logger->critical($e);
            $out = [
                'status' => 0,
                'message' => __('An error occurred while processing your form. Please try again later.')
            ];
            $this->dataPersistor->set('contact_us', $this->getRequest()->getParams());
            $this->_logger->critical($e->getMessage());
        }
        return $out;
    }

    /**
     * Validate Params
     *
     * @param array $dataPopup
     * @return array
     * @throws \Exception
     */
    private function validatedParams($dataPopup)
    {
        $result = [];
        foreach ($dataPopup as $data) {
            if ($data['name'] == 'name') {
                $result['name'] = $data['value'];
                if (trim($data['value']) === '') {
                    throw new \Magento\Framework\Exception\LocalizedException(__('Name is missing'));
                }
            }
            if ($data['name'] == 'comment') {
                $result['comment'] = $data['value'];
                if (trim($data['value']) === '') {
                    throw new \Magento\Framework\Exception\LocalizedException(__('Comment is missing'));
                }
            }
            if ($data['name'] == 'email') {
                $result['email'] = $data['value'];
                if (false === \strpos($data['value'], '@')) {
                    throw new \Magento\Framework\Exception\LocalizedException(__('Email is missing'));
                }
            }
        }

        return $result;
    }

    /**
     * Send Email
     *
     * @param array $post Post data from contact form
     * @return void
     */
    private function sendEmail($post)
    {
        $this->sendMail(
            $post['email'],
            ['data' => new \Magento\Framework\DataObject($post)]
        );
    }

    /**
     * Send Mail
     *
     * @param string $replyTo
     * @param array $variables
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\MailException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    protected function sendMail($replyTo, $variables)
    {
        $replyToName = !empty($variables['data']['name']) ? $variables['data']['name'] : null;
        $this->inlineTranslation->suspend();
        try {
            $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
            $transport = $this->_transportBuilder
                ->setTemplateIdentifier($this->scopeConfig->getValue('contact/email/email_template', $storeScope))
                ->setTemplateOptions(
                    [
                        'area' => \Magento\Framework\App\Area::AREA_FRONTEND,
                        'store' => $this->_storeManager->getStore()->getId()
                    ]
                )
                ->setTemplateVars($variables)
                ->setFrom($this->scopeConfig->getValue('contact/email/sender_email_identity', $storeScope))
                ->addTo($this->scopeConfig->getValue('contact/email/recipient_email', $storeScope))
                ->setReplyTo($replyTo, $replyToName)
                ->getTransport();

            $transport->sendMessage();
        } finally {
            $this->inlineTranslation->resume();
        }
    }

    /**
     * Add To Mailchimp
     *
     * @param array $dataPopup
     */
    public function addEmailToMailChimp($dataPopup)
    {
        $popup = $this->_popupFactory->create()->load(reset($dataPopup)['name']);

        $email = '';
        $name = '';
        foreach ($dataPopup as $data) {
            if ($data['name'] == 'email') {
                $email = $data['value'];
            }
            if ($data['name'] == 'name') {
                $name = $data['value'];
            }
        }
        $api_key = $popup->getApiKey();
        $audience_id = $popup->getAudienceId();

        strstr($api_key, "-");
        [$key, $us] = explode("-", $api_key, 2);
        $subscriber_id = hash('md5', (strtolower($email)));
        $url = 'https://' . $us . '.api.mailchimp.com/3.0/lists/' . $audience_id . '/members/' . $subscriber_id;
        $data = [
            'email_address' => $email,
            'status' => 'subscribed',
            'merge_fields' => [
                'FNAME' => $name,
                'LNAME' => ''
            ]
        ];

        $subscriber = $this->_jsonHelper->jsonEncode($data);

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_USERPWD, 'user:' . $api_key);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $subscriber);
        $result = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
    }

    /**
     * Check Mailchimp Status
     *
     * @param array $dataPopup
     * @return bool
     */
    public function enableMailchimp($dataPopup)
    {
        $enable_mailchimp = $this->_popupFactory->create()
            ->load(reset($dataPopup)['name'])->getEnableMailchimp();
        if ($enable_mailchimp == 1) {
            return true;
        } else {
            return false;
        }
    }
}
