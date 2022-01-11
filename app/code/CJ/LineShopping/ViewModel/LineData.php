<?php

namespace CJ\LineShopping\ViewModel;

use Magento\Framework\App\Request\Http;
use Magento\Framework\View\Element\Block\ArgumentInterface;
use Magento\Framework\Serialize\Serializer\Json;
use CJ\LineShopping\Helper\Data as DataHelper;

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
     * @param Json $json
     * @param Http $request
     */
    public function __construct(
        Json $json,
        Http $request
    ) {
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
    public function getParamLineInfoalue()
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
