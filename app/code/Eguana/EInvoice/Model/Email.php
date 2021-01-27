<?php
/**
 * @author Eguana Team
 * @copyright Copyright (c) 2021 Eguana {http://eguanacommerce.com}
 * Created by PhpStorm
 * User: david
 * Date: 2021/01/21
 * Time: 10:01 AM
 */

namespace Eguana\EInvoice\Model;

use Exception;
use Magento\Framework\App\Area;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\Escaper;
use Magento\Framework\Mail\Template\TransportBuilder;
use Magento\Framework\Translate\Inline\StateInterface;
use Magento\Store\Model\StoreManagerInterface;

class Email extends AbstractHelper
{
    /**
     * @var StateInterface
     */
    private $inlineTranslation;
    /**
     * @var Escaper
     */
    private $escaper;
    /**
     * @var TransportBuilder
     */
    private $transportBuilder;
    /**
     * @var StoreManagerInterface
     */
    private $storeManager;
    /**
     * @var \Magento\Email\Model\BackendTemplate
     */
    private $emailTemplate;
    /**
     * @var Source\Config
     */
    private $helper;

    /**
     * Email constructor.
     * @param Context $context
     * @param StateInterface $inlineTranslation
     * @param Escaper $escaper
     * @param TransportBuilder $transportBuilder
     * @param StoreManagerInterface $storeManager
     * @param \Magento\Email\Model\BackendTemplate $emailTemplate
     * @param Source\Config $helper
     */
    public function __construct(
        Context $context,
        StateInterface $inlineTranslation,
        Escaper $escaper,
        TransportBuilder $transportBuilder,
        StoreManagerInterface $storeManager,
        \Magento\Email\Model\BackendTemplate $emailTemplate,
        \Eguana\EInvoice\Model\Source\Config $helper
    ) {
        parent::__construct($context);
        $this->inlineTranslation = $inlineTranslation;
        $this->escaper = $escaper;
        $this->transportBuilder = $transportBuilder;
        $this->storeManager = $storeManager;
        $this->emailTemplate = $emailTemplate;
        $this->helper = $helper;
    }

    /**
     * @param $order
     * @param $message
     */
    public function sendEmail($order, $message)
    {
        try {
            $templateId = $this->emailTemplate->load("E-Invoice Fail", "template_code")->getId();
            $templateVars = [
                "increment_id" => $order->getIncrementId(),
                "message" => $message,
            ];

            $storeId = $order->getStoreId();

            $from = [
                "email" => $this->helper->getSenderEmail($storeId),
                "name" => $this->helper->getSenderName($storeId),
            ];
            $this->inlineTranslation->suspend();

            $templateOptions = [
                "area" => Area::AREA_FRONTEND,
                "store" => $storeId
            ];
            $transport = $this->transportBuilder->setTemplateIdentifier($templateId)
                ->setTemplateOptions($templateOptions)
                ->setTemplateVars($templateVars)
                ->setFrom($from)
                ->addTo($this->helper->getReceiverEmail($storeId))
                ->getTransport();
            $transport->sendMessage();
            $this->inlineTranslation->resume();
        } catch (Exception $e) {
            $this->_logger->debug($e->getMessage());
        }
    }
}
