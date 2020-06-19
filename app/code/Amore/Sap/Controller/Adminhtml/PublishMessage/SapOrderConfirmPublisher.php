<?php
/**
 * Created by Eguana.
 * User: Brian
 * Date: 2020-06-05
 * Time: 오후 2:23
 */

namespace Amore\Sap\Controller\Adminhtml\PublishMessage;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\MessageQueue\PublisherInterface;
use Magento\Framework\App\ResponseInterface;

class SapOrderConfirmPublisher extends Action
{
    const SAP_ORDER_CONFIRM_TOPIC_NAME = 'sap.order.confirm';

    /**
     * @var JsonFactory
     */
    private $jsonFactory;
    /**
     * @var Json
     */
    private $json;
    /**
     * @var PublisherInterface
     */
    private $publisher;
    /**
     * @var \Amore\Sap\Model\SapOrder\SapOrderConfirm
     */
    private $sapOrderConfirm;

    /**
     * SapOrderConfirmPublisher constructor.
     * @param Context $context
     * @param JsonFactory $jsonFactory
     * @param Json $json
     * @param PublisherInterface $publisher
     */
    public function __construct(
        Context $context,
        JsonFactory $jsonFactory,
        Json $json,
        PublisherInterface $publisher,
        \Amore\Sap\Model\SapOrder\SapOrderConfirm $sapOrderConfirm
    ) {
        parent::__construct($context);
        $this->jsonFactory = $jsonFactory;
        $this->json = $json;
        $this->publisher = $publisher;
        $this->sapOrderConfirm = $sapOrderConfirm;
    }

    public function execute()
    {
        $writer = new \Zend\Log\Writer\Stream(BP . sprintf('/var/log/test_%s.log',date('Ymd')));
        $logger = new \Zend\Log\Logger();
        $logger->addWriter($writer);
        $logger->info(__METHOD__);

        $resultJson = $this->jsonFactory->create();

        try {
//            $publishData = ['test' => '11'];
            $publishData = $this->sapOrderConfirm->orderDataForMiddleware('000000006');

            $this->publisher->publish(self::SAP_ORDER_CONFIRM_TOPIC_NAME, $this->json->serialize($publishData));
            $result = ['result' => 'SUCCESS'];
            return $resultJson->setData($result);
        } catch (\Exception $e) {
            $result = ['error' => $e->getMessage()];
            return $resultJson->setData($result);
        }
    }

    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Amore_Sap::sap');
    }
}
