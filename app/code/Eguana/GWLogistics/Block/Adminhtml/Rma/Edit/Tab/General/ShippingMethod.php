<?php
/**
 * Created by Eguana Team.
 * User: sonia
 * Date: 7/14/20
 * Time: 6:27 AM
 */

namespace Eguana\GWLogistics\Block\Adminhtml\Rma\Edit\Tab\General;

use Eguana\GWLogistics\Helper\Data;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Backend\Block\Template\Context;
use Magento\Framework\Registry;
use Magento\Rma\Helper\Data as DataAlias1;
use Magento\Tax\Helper\Data as DataAlias;
use Magento\Rma\Model\ShippingFactory;

class ShippingMethod extends \Magento\Rma\Block\Adminhtml\Rma\Edit\Tab\General\Shippingmethod
{
    /**
     * @var Data
     */
    private $helper;

    /**
     * ShippingMethod constructor.
     * @param Context $context
     * @param Registry $registry
     * @param DataAlias $taxData
     * @param DataAlias1 $rmaData
     * @param ShippingFactory $shippingFactory
     * @param PriceCurrencyInterface $priceCurrency
     * @param array $data
     * @param Json|null $json
     * @param Data $helper
     */
    public function __construct(
        Context $context,
        Registry $registry,
        DataAlias $taxData,
        DataAlias1 $rmaData,
        ShippingFactory $shippingFactory,
        PriceCurrencyInterface $priceCurrency,
        array $data = [],
        Json $json = null,
        Data $helper
    ) {
        parent::__construct(
            $context,
            $registry,
            $taxData,
            $rmaData,
            $shippingFactory,
            $priceCurrency,
            $data,
            $json
        );
        $this->helper = $helper;
    }

    /**
     * @param \Magento\Rma\Api\Data\RmaInterface $rma
     * @return bool
     */
    public function canShowReverseLogisticsOrderGenerationButton($rma)
    {
        $shipments = $rma->getTracks();
        return $shipments ? false : true;
    }

    /**
     * @param \Magento\Rma\Api\Data\RmaInterface $rma
     * @return string
     */
    public function getReverseOrderUrl($rma)
    {
        return $this->getUrl('eguana_gwlogistics/reverseorder/create', ['rma_id' => $rma->getEntityId()]);
    }

    /**
     * This method is used to get the module active status
     * @param $rma
     * @return mixed
     */
    public function getIsEnabled($rma)
    {
        return $this->helper->isActive($rma->getStoreId());
    }
}
