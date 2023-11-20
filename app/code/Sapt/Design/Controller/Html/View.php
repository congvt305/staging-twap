<?php
namespace Sapt\Design\Controller\Html;

class View extends \Magento\Framework\App\Action\Action
{
    const MENU_TEMPALTE = 'menu.phtml';

	public function execute()
    {
        $project = $this->getRequest()->getParam('project', 'hksulhwasu');
        $viewMode = $this->getRequest()->getParam('mode', 'html');
        $template = $this->getRequest()->getParam('template', 'index');

        $templateFilePath = $this->_getTemplateFilePath($project, $viewMode, $template);
        $menuTemplateFilePath = $this->_getMenuTemplateFilePath($project, $viewMode);

        $this->_view->loadLayout();

        $this->_view->getLayout()
                    ->getBlock('sapt.design.index')
                    ->setTemplate($templateFilePath);

        $this->_view->getLayout()
            ->getBlock('sapt.design.menu')
            ->setTemplate($menuTemplateFilePath);

        $this->_view->getLayout()->initMessages();
        $this->_view->renderLayout();
    }

    private function _getTemplateFilePath($project, $viewMode, $template) {
        return 'Sapt_Design::'.$project.'/'.$viewMode.'/'.$template.'.phtml';
    }

    private function _getMenuTemplateFilePath($project, $viewMode) {
        return 'Sapt_Design::'.$project.'/'.$viewMode.'/'.self::MENU_TEMPALTE;
    }

}
