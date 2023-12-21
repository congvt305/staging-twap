<?php

namespace Amasty\Scroll\Plugin\Ajax;

use Magento\Framework\Controller\Result\Raw;
use Magento\Framework\View\Result\Page;

class InitAjaxResponse extends AjaxAbstract
{
    /**
     * @param $controller
     * @param null $page
     *
     * @return Raw|null
     */
    public function afterExecute($controller, $page = null)
    {
        if (!$this->isAjax() || !$page instanceof Page) {
            return $page;
        }

        $this->setPage($page);
        $responseData = $this->getAjaxResponseData();
        $response = $this->prepareResponse($responseData);
        $this->updateHeaders($response);

        return $response;
    }

    /**
     * @param array $data
     *
     * @return Raw
     */
    protected function prepareResponse(array $data)
    {
        $response = $this->resultRawFactory->create();
        $response->setContents($this->jsonEncoder->encode($data));

        return $response;
    }
}
