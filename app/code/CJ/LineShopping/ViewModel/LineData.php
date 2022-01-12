<?php

namespace CJ\LineShopping\ViewModel;

use Magento\Framework\App\Request\Http;
use Magento\Framework\View\Element\Block\ArgumentInterface;
use Magento\Framework\Serialize\Serializer\Json;
use CJ\LineShopping\Helper\Data as DataHelper;
use CJ\LineShopping\Helper\Config;

class LineData implements ArgumentInterface
{
    /**
     * @var Http
     */
    protected Http $request;

    /**
     * @var Json
     */
    protected Json $json;

    /**
     * @var Config
     */
    protected Config $config;

    /**
     * @param Config $config
     * @param Json $json
     * @param Http $request
     */
    public function __construct(
        Config $config,
        Json $json,
        Http $request
    ) {
        $this->config = $config;
        $this->json = $json;
        $this->request = $request;
    }

    /**
     * @return mixed
     */
    public function getParamEcidValue()
    {
        return $this->request->getParam('ecid');
    }

    /**
     * @return mixed
     */
    public function isEnable()
    {
        return $this->config->isEnable();
    }

    /**
     * @return mixed
     */
    public function getCookieLifeTime()
    {
        return $this->config->getCookieLifeTime();
    }
    /**
     * @return mixed
     */
    public function getParamLineInfoValue()
    {
        $data = $this->request->getParams();
        $dataLine = [];
        foreach (DataHelper::LINE_INFO as $item) {
            if(isset($data[$item])) {
                $dataLine[$item] = $data[$item];
            }
        }
        if($dataLine) {
            $dataLine = $this->json->serialize($dataLine);
        }
        return $dataLine;
    }
}
