<?php

namespace Eguana\Directory\Model;

use Magento\Checkout\Model\ConfigProviderInterface;

class PostCodeAutoFill implements ConfigProviderInterface
{
    /**
     * @var \Eguana\Directory\Helper\Data
     */
    protected $data;

    /**
     * @param \Eguana\Directory\Helper\Data $data
     */
    public function __construct(\Eguana\Directory\Helper\Data $data)
    {
        $this->data = $data;
    }

    /**
     * @return array
     */
    public function getConfig()
    {
        $result['postcode_auto_fill'] = $this->data->isZipCodeAutofilled();
        return $result;
    }
}
