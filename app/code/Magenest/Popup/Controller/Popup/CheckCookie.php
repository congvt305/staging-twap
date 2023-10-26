<?php
namespace Magenest\Popup\Controller\Popup;

use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\Result\Raw;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Controller\ResultInterface;

class CheckCookie extends Popup
{
    /**
     * Execute
     *
     * @return ResponseInterface|Raw|ResultInterface
     * @throws \Exception
     */
    public function execute()
    {
        $out = ['message' => 'Magenest'];
        $params = $this->getRequest()->getParams();
        if (isset($params['popup_id']) && $params['popup_id']) {
            $popupModel = $this->_popupFactory->create();
            $this->popupResources->load($popupModel, $params['popup_id']);

            if ($popupModel->getId()) {
                $popup_click = (int)$popupModel->getClick();
                $popup_view = (int)$popupModel->getView() + 1;
                $ctr = (float)($popup_click/$popup_view) * 100;
                $ctr = round($ctr, 2);

                $popupModel->setClick($popup_click);
                $popupModel->setView($popup_view);
                $popupModel->setCtr($ctr);
                $this->popupResources->save($popupModel);
            }
        }

        $data = $this->json->serialize($out);
        return $this->resultFactory->create(ResultFactory::TYPE_RAW)
            ->setHeader('Content-type', 'text/plain')
            ->setContents($data);
    }
}
