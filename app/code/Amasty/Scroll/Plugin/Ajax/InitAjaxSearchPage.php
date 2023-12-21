<?php

namespace Amasty\Scroll\Plugin\Ajax;

use Magento\Framework\View\Result\Page;

class InitAjaxSearchPage extends AjaxAbstract
{
    /**
     * @param \Magento\Framework\App\View $subject
     * @param \Closure $proceed
     * @param string $output
     *
     * @return \Magento\Framework\App\View|mixed
     */
    public function aroundRenderLayout(
        \Magento\Framework\App\View $subject,
        \Closure $proceed,
        $output = ''
    ) {
        $page = $subject->getPage();

        if (!($page instanceof Page) || !$this->isAjax() || $this->request->getRouteName() !== 'catalogsearch') {
            return $proceed($output);
        }

        $this->setPage($page);
        $responseData = $this->getAjaxResponseData();
        $this->response->setBody($this->jsonEncoder->encode($responseData));
        $this->updateHeaders($this->response);

        return $subject;
    }
}
