<?php
namespace Magenest\Popup\Controller\Popup;

use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\Result\Raw;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Controller\ResultInterface;

class ClosePopup extends Popup
{
    /**
     * Execute
     *
     * @return ResponseInterface|Raw|ResultInterface
     * @throws \Exception
     */
    public function execute()
    {
        $params = $this->getRequest()->getParams();
        if (!empty($params['popup_id']) && isset($params['flag']) && $params['flag'] == 0) {
            $popupModel = $this->_popupFactory->create();
            $this->popupResources->load($popupModel, $params['popup_id']);

            if ($popupModel->getId()) {
                $popup_click = (int)$popupModel->getClick() + 1;
                $popup_view = (int)$popupModel->getView();
                $ctr = (float)($popup_click/$popup_view) * 100;

                $popupModel->setClick($popup_click);
                $popupModel->setView($popup_view);
                $popupModel->setCtr($ctr);
                $this->popupResources->save($popupModel);
            }
        }
        $data = $this->json->serialize([]);
        return $this->resultFactory->create(ResultFactory::TYPE_RAW)
            ->setHeader('Content-type', 'text/plain')
            ->setContents($data);
    }
}
