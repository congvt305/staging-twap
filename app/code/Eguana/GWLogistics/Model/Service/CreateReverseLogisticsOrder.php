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
     * @var \Eguana\GWLogistics\Model\Request\CvsCreateReverseShipmentOrder
     */
    private $createReverseShipmentOrderRequest;
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

    public function __construct(
        \Eguana\GWLogistics\Model\Request\CvsCreateReverseShipmentOrder $createReverseShipmentOrderRequest,
        \Magento\Rma\Api\Data\TrackInterfaceFactory $trackFactory,
        \Magento\Rma\Api\TrackRepositoryInterface $trackRepository,
        \Magento\Rma\Api\Data\CommentInterfaceFactory $commentInterfaceFactory,
        \Magento\Rma\Api\CommentRepositoryInterface $commentRepository,
        \Eguana\GWLogistics\Helper\Data $helper
    ) {
        $this->createReverseShipmentOrderRequest = $createReverseShipmentOrderRequest;
        $this->trackFactory = $trackFactory;
        $this->trackRepository = $trackRepository;
        $this->helper = $helper;
        $this->commentInterfaceFactory = $commentInterfaceFactory;
        $this->commentRepository = $commentRepository;
    }
    /**
     * @param \Magento\Rma\Api\Data\RmaInterface $rma
     * @return array|string[]
     */
    public function process($rma)
    {
        try {
            $result = $this->createReverseShipmentOrderRequest->sendRequest($rma);
            if (isset($result['RtnMerchantTradeNo']) && isset($result['RtnOrderNo'])) {
                $this->saveTrack($rma, $result);
            } elseif (isset($result['ErrorMessage']) && $result['ErrorMessage'] === '找不到加密金鑰，請確認是否有申請開通此物流方式!') { //need to remove when go live
                $result = [
                    'RtnMerchantTradeNo' => time(),
                    'RtnOrderNo' => time()
                ];
                $this->saveTrack($rma, $result);
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
        $track->setCarrierTitle($this->helper->getCarrierTitle());
        $this->trackRepository->save($track);
        /** @var \Magento\Rma\Api\Data\CommentInterface $comment */
        $comment = $this->commentInterfaceFactory->create();
        $comment->setRmaEntityId($rma->getEntityId());
        $comment->setComment('Reverse Logistics Order Created');
        $comment->setIsAdmin(true);
        $comment->setIsVisibleOnFront(true);
        $this->commentRepository->save($comment);

        return;
    }
    /**
     * @param \Magento\Rma\Api\Data\RmaInterface $rma
     * @throws \Exception
     */
    private function saveComment($rma)
    {
        /** @var \Magento\Rma\Api\Data\CommentInterface $comment */
        $comment = $this->commentInterfaceFactory->create();
        $comment->setRmaEntityId($rma->getEntityId());
        $comment->setComment('Reverse Logistics Order Created');
        $comment->setIsAdmin(true);
        $comment->setIsVisibleOnFront(true);
        $this->commentRepository->save($comment);
        return;
    }

}

