<?php
namespace Sapt\GeoTarget\Controller\Adminhtml\Index;

use Sapt\GeoTarget\Model\GeoTarget;


class Delete extends \Magento\Backend\App\Action
{

    const ADMIN_RESOURCE = 'Index';

    protected $resultPageFactory;
    protected $geoTargetFactory;

    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Sapt\GeoTarget\Model\GeoTargetFactory $geoTargetFactory
    )
    {
        $this->resultPageFactory = $resultPageFactory;
        $this->geoTargetFactory = $geoTargetFactory;
        parent::__construct($context);
    }

    public function execute()
    {
        $id = $this->getRequest()->getParam('id');

        $geoTarget = $this->geoTargetFactory->create()->load($id);

        if(!$geoTarget)
        {
            $this->messageManager->addError(__('Unable to process. please, try again.'));
            $resultRedirect = $this->resultRedirectFactory->create();
            return $resultRedirect->setPath('*/*/', array('_current' => true));
        }

        try{
            $geoTarget->delete();
            $this->messageManager->addSuccess(__('Your contact has been deleted !'));
        }
        catch(\Exception $e)
        {
            $this->messageManager->addError(__('Error while trying to delete contact'));
            $resultRedirect = $this->resultRedirectFactory->create();
            return $resultRedirect->setPath('*/*/index', array('_current' => true));
        }

        $resultRedirect = $this->resultRedirectFactory->create();
        return $resultRedirect->setPath('*/*/index', array('_current' => true));
    }
}
