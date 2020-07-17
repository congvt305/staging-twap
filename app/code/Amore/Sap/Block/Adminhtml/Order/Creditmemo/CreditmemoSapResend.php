<?php
/**
 * Created by Eguana.
 * User: Brian
 * Date: 2020-07-17
 * Time: 오후 2:04
 */

namespace Amore\Sap\Block\Adminhtml\Order\Creditmemo;


use Amore\Sap\Logger\Logger;
use Amore\Sap\Model\Source\Config;
use Magento\Framework\Serialize\Serializer\Json;

class CreditmemoSapResend extends \Magento\Sales\Block\Adminhtml\Order\Creditmemo\View
{
    /**
     * @var Json
     */
    private $json;
    /**
     * @var Logger
     */
    private $logger;
    /**
     * @var Config
     */
    private $config;

    /**
     * CreditmemoSapResend constructor.
     * @param \Magento\Backend\Block\Widget\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param Json $json
     * @param Logger $logger
     * @param Config $config
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Widget\Context $context,
        \Magento\Framework\Registry $registry,
        Json $json,
        Logger $logger,
        Config $config,
        array $data = []
    ) {
        $this->json = $json;
        $this->logger = $logger;
        $this->config = $config;
        parent::__construct($context, $registry, $data);
    }

    /**
     * Add & remove control buttons
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_objectId = 'creditmemo_id';
        $this->_controller = 'adminhtml_order_creditmemo';
        $this->_mode = 'view';

        parent::_construct();

        $this->buttonList->remove('save');
        $this->buttonList->remove('reset');
        $this->buttonList->remove('delete');

        if (!$this->getCreditmemo()) {
            return;
        }

        if ($this->getCreditmemo()->canCancel()) {
            $this->buttonList->add(
                'cancel',
                [
                    'label' => __('Cancel'),
                    'class' => 'delete',
                    'onclick' => 'setLocation(\'' . $this->getCancelUrl() . '\')'
                ]
            );
        }

        if ($this->_isAllowedAction('Magento_Sales::emails')) {
            $this->addButton(
                'send_notification',
                [
                    'label' => __('Send Email'),
                    'class' => 'send-email',
                    'onclick' => 'confirmSetLocation(\'' . __(
                            'Are you sure you want to send a credit memo email to customer?'
                        ) . '\', \'' . $this->getEmailUrl() . '\')'
                ]
            );
        }

        if ($this->getCreditmemo()->canRefund()) {
            $this->buttonList->add(
                'refund',
                [
                    'label' => __('Refund'),
                    'class' => 'refund',
                    'onclick' => 'setLocation(\'' . $this->getRefundUrl() . '\')'
                ]
            );
        }

        if ($this->getCreditmemo()->canVoid()) {
            $this->buttonList->add(
                'void',
                [
                    'label' => __('Void'),
                    'class' => 'void',
                    'onclick' => 'setLocation(\'' . $this->getVoidUrl() . '\')'
                ]
            );
        }

        if ($this->getCreditmemo()->getId()) {
            $this->buttonList->add(
                'print',
                [
                    'label' => __('Print'),
                    'class' => 'print',
                    'onclick' => 'setLocation(\'' . $this->getPrintUrl() . '\')'
                ]
            );
        }

        if ($this->config->getActiveCheck('store', $this->getCreditmemo()->getStoreId()) && !$this->config->checkTestMode()) {
            if ($this->getCreditmemo()->getOrder()->getData('sap_creditmemo_send_check') == 2) {
                $this->buttonList->add(
                    'sap_refund_send',
                    [
                        'label' => __('SAP Send'),
                        'class' => 'sap-send',
                        'onclick' => 'confirmSetLocation(\'' . __(
                                'Are you sure you want to resend a Order Cancel Data to SAP?'
                            ) . '\', \'' . $this->getResendToSapUrl() . '\')'
                    ]
                );
            }
        }
    }

    /**
     * @param $order \Magento\Sales\Model\Order
     */
    public function getResendToSapUrl()
    {
        return $this->getUrl(
            'sap/saporder/creditmemoresend',
          [
              'creditmemo_id' => $this->getCreditmemo()->getId(),
              'order_id' => $this->getCreditmemo()->getOrder()->getId()
          ]
        );
    }
}
