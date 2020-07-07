<?php
/**
 * Created by Eguana Team.
 * User: sonia
 * Date: 6/29/20
 * Time: 2:16 PM
 */

namespace Eguana\GWLogistics\Controller\Test;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;

class SaveCvsLocation extends Action
{
    /**
     * @var \Eguana\GWLogistics\Model\Service\SaveQuoteCvsLocation
     */
    private $saveQuoteCvsLocation;

    public function __construct(
        \Eguana\GWLogistics\Model\Service\SaveQuoteCvsLocation $saveQuoteCvsLocation,
        Context $context
    ) {
        parent::__construct($context);
        $this->saveQuoteCvsLocation = $saveQuoteCvsLocation;
    }
    public function execute()
    {
        $cvsStoreData = [
            'MerchantID' => '2000132',
            'MerchantTradeNo' => date('YmdHis'),
            'LogisticsSubType' => 'UNIMART',
            'CVSStoreID' => '991182',
            'CVSStoreName' => '馥樺門市',
            'CVSAddress' => '台北市南港區三重路23號1樓',
            'CVSTelephone' => '',
            'CVSOutSide' => '0',
            'ExtraData' => '118' //quote id
        ];
        /** @var \Eguana\GWLogistics\Model\QuoteCvsLocation $cvsLocation */
        $cvsLocation = $this->saveQuoteCvsLocation->process($cvsStoreData);
        print_r($cvsLocation->getId());
    }

}
