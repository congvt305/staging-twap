<?php
/**
 * Created by Eguana Team.
 * User: sonia
 * Date: 10/9/20
 * Time: 8:05 AM
 */
declare(strict_types=1);

namespace Eguana\GWLogistics\Controller\SelectCvs;

use Eguana\GWLogistics\Model\Carrier\CvsStorePickup;
use Eguana\GWLogistics\Model\Gateway\Command\SelectCvsCommand;
use Eguana\GWLogistics\Model\Lib\EcpayLogistics;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Action\HttpPostActionInterface;

class Index extends Action
{
    /**
     * @var CheckoutSession
     */
    private $checkoutSession;
    /**
     * @var SelectCvsCommand
     */
    private $selectCvsCommand;

    public function __construct(
        CheckoutSession $checkoutSession,
        SelectCvsCommand $selectCvsCommand,
        Context $context
    ) {
        parent::__construct($context);
        $this->checkoutSession = $checkoutSession;
        $this->selectCvsCommand = $selectCvsCommand;
    }

    public function execute()
    {
        $response = $this->getResponse();

        $cvsType = $this->getRequest()->getParam('cvs_type');
        $errorHtml = '<script>window.close();</script>';


        if (!in_array($cvsType, [CvsStorePickup::SEVEN_ELEVEN_CODE, CvsStorePickup::FAMILY_MART_CODE])) {
            $response->setBody($errorHtml);
            return $response->sendResponse();
        }
        try {
            $html = $this->selectCvsCommand->execute($this->getRequestArray($cvsType));
            $response->setBody($html);
            return $response->sendResponse();
        } catch (\Exception $e) {
            $response->setBody($errorHtml);
            return $response->sendResponse();
        }
    }
    private function getRequestArray(string $cvsType): array
    {
        return [
           'cvs_type' => $cvsType,
            'quote_id' => $this->checkoutSession->getQuoteId()
        ];
    }

}
