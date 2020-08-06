<?php
/**
 * Created by Eguana Team.
 * User: sonia
 * Date: 6/20/20
 * Time: 5:37 PM
 */

namespace Eguana\GWLogistics\Model\Service;


class CreateReverseLogisticsOrder
{
    /**
     * @var \Magento\Rma\Api\Data\TrackInterfaceFactory
     */
    private $trackFactory;
    /**
     * @var \Magento\Rma\Api\TrackRepositoryInterface
     */
    private $trackRepository;
    /**
     * @var \Eguana\GWLogistics\Helper\Data
     */
    private $helper;
    /**
     * @var \Magento\Rma\Api\Data\CommentInterfaceFactory
     */
    private $commentInterfaceFactory;
    /**
     * @var \Magento\Rma\Api\CommentRepositoryInterface
     */
    private $commentRepository;
    /**
     * @var SmsSender
     */
    private $smsSender;
    /**
     * @var \Eguana\GWLogistics\Model\Request\UnimartCreateReverseShipmentOrder
     */
    private $unimartCreateReverseShipmentOrder;
    /**
     * @var \Eguana\GWLogistics\Model\Request\FamiCreateReverseShipmentOrder
     */
    private $famiCreateReverseShipmentOrder;

    public function __construct(
        \Eguana\GWLogistics\Model\Request\UnimartCreateReverseShipmentOrder $unimartCreateReverseShipmentOrder,
        \Eguana\GWLogistics\Model\Request\FamiCreateReverseShipmentOrder $famiCreateReverseShipmentOrder,
        \Magento\Rma\Api\Data\TrackInterfaceFactory $trackFactory,
        \Magento\Rma\Api\TrackRepositoryInterface $trackRepository,
        \Magento\Rma\Api\Data\CommentInterfaceFactory $commentInterfaceFactory,
        \Magento\Rma\Api\CommentRepositoryInterface $commentRepository,
        \Eguana\GWLogistics\Helper\Data $helper,
        \Eguana\GWLogistics\Model\Service\SmsSender $smsSender
    ) {
        $this->trackFactory = $trackFactory;
        $this->trackRepository = $trackRepository;
        $this->helper = $helper;
        $this->commentInterfaceFactory = $commentInterfaceFactory;
        $this->commentRepository = $commentRepository;
        $this->smsSender = $smsSender;
        $this->unimartCreateReverseShipmentOrder = $unimartCreateReverseShipmentOrder;
        $this->famiCreateReverseShipmentOrder = $famiCreateReverseShipmentOrder;
    }
    /**
     * @param \Magento\Rma\Api\Data\RmaInterface $rma
     * @return array|string[]
     */
    public function process($rma)
    {
        try {
            $shippingPreference = $rma->getData('shipping_preference');
            switch ($shippingPreference) {
                case 'UNIMART':
                    $result = $this->unimartCreateReverseShipmentOrder->sendRequest($rma);
                    break;
                case 'FAMI':
                    $result = $this->famiCreateReverseShipmentOrder->sendRequest($rma);
                    break;
                default:
                    break;
            }
            if (isset($result['RtnMerchantTradeNo']) && isset($result['RtnOrderNo']) && $result['RtnOrderNo']) {
                $this->saveTrack($rma, $result);
                $this->smsSender->sendSms($rma, $result['RtnOrderNo']);
            }
        } catch (\Exception $e) {
            $result = ['ErrorMessage' => $e->getMessage()];
        }

        return $result;
    }
    /**
     * @param \Magento\Rma\Api\Data\RmaInterface $rma
     * @param $result
     */
    private function saveTrack($rma, $result)
    {
        /** @var \Magento\Rma\Api\Data\TrackInterface $track */
        $track = $this->trackFactory->create();
        $track->setTrackNumber($result['RtnOrderNo']);
        $track->setRmaEntityId($rma->getEntityId());
        $track->setCarrierCode('gwlogistics');
        $track->setCarrierTitle($this->helper->getCarrierTitle($rma->getStoreId()));
        $track->setMethodCode($rma->getData('shipping_preference'));
        $track->setData('rtn_merchant_trade_no', $result['RtnMerchantTradeNo']);
        $this->trackRepository->save($track);

        /** @var \Magento\Rma\Api\Data\CommentInterface $comment */
        $comment = $this->commentInterfaceFactory->create();
        $comment->setRmaEntityId($rma->getEntityId());
        $comment->setComment(__('Reverse Logistics Order Created. Return Order Number is %1.', $result['RtnOrderNo']));
        $comment->setIsAdmin(true);
        $comment->setIsVisibleOnFront(true);
        $this->commentRepository->save($comment);

        return;
    }

}

