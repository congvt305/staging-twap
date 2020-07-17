<?php
/**
 * Created by Eguana Team.
 * User: sonia
 * Date: 7/17/20
 * Time: 5:20 PM
 */

namespace Eguana\GWLogistics\Plugin\Rma;


use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Rma\Api\Data\TrackInterface;

class TrackRepositoryPlugin
{
    /**
     * @param \Magento\Rma\Api\TrackRepositoryInterface $subject
     * @param $result
     * @param int $id
     */
    public function afterGet(\Magento\Rma\Api\TrackRepositoryInterface $subject, $result)
    {
        if ($result->getData('rtn_merchant_trade_no')) {
            $extensionAttributes = $result->getExtensionAttributes();
            try {
                $extensionAttributes->setRtnMerchantTradeNo($result->getData('rtn_merchant_trade_no'));
            } catch (\Exception $e) {
                $extensionAttributes->setRtnMerchantTradeNo(null);
            }
            $result->setExtensionAttributes($extensionAttributes);
        }
        return $result;
    }

    /**
     * @param \Magento\Rma\Api\TrackRepositoryInterface $subject
     * @param $result
     * @param SearchCriteriaInterface $criteria
     */
    public function afterGetList(\Magento\Rma\Api\TrackRepositoryInterface $subject, $result, SearchCriteriaInterface $criteria)
    {
        foreach ($result->getItems() as $track) {
            $this->afterGet($subject, $track);
        }
        return $result;
    }

    /**
     * @param \Magento\Rma\Api\TrackRepositoryInterface $subject
     * @param TrackInterface $entity
     * @return array
     */
    public function beforeSave(\Magento\Rma\Api\TrackRepositoryInterface $subject, TrackInterface $entity)
    {
        $extensionAttributes = $entity->getExtensionAttributes();
        $rtnMerchantTradeNo = $extensionAttributes->getRtnMerchantTradeNo();
        if ($rtnMerchantTradeNo) {
            $entity->setData('rtn_merchant_trade_no', $rtnMerchantTradeNo);
        }
        return [$entity];
    }

}
