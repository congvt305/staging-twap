<?php
/**
 * Created by Eguana Team.
 * User: sonia
 * Date: 10/30/20
 * Time: 9:20 AM
 */
declare(strict_types=1);

namespace Eguana\GWLogistics\Model\Gateway\Command;


use Eguana\GWLogistics\Model\Lib\EcpayLogistics;

class SelectCvsCommand
{
    /**
     * @var EcpayLogistics
     */
    private $ecpayLogistics;
    /**
     * @var \Eguana\GWLogistics\Helper\Data
     */
    private $dataHelper;

    public function __construct(
        EcpayLogistics $ecpayLogistics,
        \Eguana\GWLogistics\Helper\Data $dataHelper
    ) {
        $this->ecpayLogistics = $ecpayLogistics;
        $this->dataHelper = $dataHelper;
    }

    public function execute(array $request): string
    {
        $errorHtml = '<script>window.close();</script>';

        if (!isset($request['cvs_type'], $request['quote_id']))
        {
            return $errorHtml;
        }

        $this->ecpayLogistics->Send = [
            'MerchantID' => $this->dataHelper->getMerchantId(),
            'MerchantTradeNo' => 'no' . date('YmdHis'),
            'LogisticsSubType' => $request['cvs_type'],
            'IsCollection' => 'NO',
            'ServerReplyURL' => $this->dataHelper->getMapServerReplyUrl(),
            'ExtraData' => (string) $request['quote_id'],
            'Device' => 0
        ];

        return $this->ecpayLogistics->CvsMap(null, '_self');
    }

}