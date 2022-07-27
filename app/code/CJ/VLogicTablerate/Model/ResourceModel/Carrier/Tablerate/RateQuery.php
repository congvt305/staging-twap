<?php

namespace CJ\VLogicTablerate\Model\ResourceModel\Carrier\Tablerate;
use Magento\Checkout\Model\Session;

class RateQuery
{
    const MACAU_REGION_CODE = 'M';
    const MACAU_REGION_ID = 3825;

    const HONG_KONG_REGION_CODE = 'H';
    const HONG_KONG_REGION_ID = 3823;

    /**
     * @var \Magento\Quote\Model\Quote\Address\RateRequest
     */
    private $request;

    /**
     * @var Session
     */
    private $_checkoutSession;

    /**
     * RateQuery constructor.
     * @param \Magento\Quote\Model\Quote\Address\RateRequest $request
     */
    public function __construct(
        \Magento\Quote\Model\Quote\Address\RateRequest $request,
        Session                                            $session
    ){
        $this->request = $request;
        $this->_checkoutSession = $session;
    }

    /**
     * Prepare select
     *
     * @param \Magento\Framework\DB\Select $select
     * @return \Magento\Framework\DB\Select
     */
    public function prepareSelect(\Magento\Framework\DB\Select $select)
    {
        $select->where(
            'website_id = :website_id'
        )->where(
            'dest_country_id = :country_id'
        )->where(
            'dest_region_id = :region_id'
        )->order(
            ['dest_region_id DESC']
        )->limit(
            1
        );

        return $select;
    }

    /**
     * Returns query bindings
     *
     * @return array
     */
    public function getBindings()
    {
        $bind = [
            ':website_id' => $this->request->getWebsiteId(),
            ':country_id' => $this->request->getDestCountryId(),
            ':region_id' => !empty($this->request->getDestRegionId()) ? $this->request->getDestRegionId()
                : ($this->_checkoutSession->getQuote()->getShippingAddress()->getRegionCode() == self::MACAU_REGION_CODE ? self::MACAU_REGION_ID : self::HONG_KONG_REGION_ID)
        ];

        return $bind;
    }

    /**
     * Returns rate request
     *
     * @return \Magento\Quote\Model\Quote\Address\RateRequest
     */
    public function getRequest()
    {
        return $this->request;
    }

    /**
     * Returns the entire postcode if it contains no dash or the part of it prior to the dash in the other case
     *
     * @return string
     */
    private function getDestPostcodePrefix()
    {
        if (!preg_match("/^(.+)-(.+)$/", $this->request->getDestPostcode(), $zipParts)) {
            return $this->request->getDestPostcode();
        }

        return $zipParts[1];
    }
}
