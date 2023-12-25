<?php
namespace Sapt\GeoTarget\Controller\Adminhtml\Index;

use Magento\Framework\Exception\LocalizedException;

class Save extends \Magento\Backend\App\Action
{

    const ADMIN_RESOURCE = 'Index';

    protected $dataPersistor;
    protected $resultPageFactory;
    protected $geoTargetFactory;

    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Sapt\GeoTarget\Model\geoTargetFactory $geoTargetFactory,
        \Magento\Framework\App\Request\DataPersistorInterface $dataPersistor
    )
    {
        $this->resultPageFactory = $resultPageFactory;
        $this->geoTargetFactory = $geoTargetFactory;
        $this->dataPersistor = $dataPersistor;
        parent::__construct($context);
    }

    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();
        $data = $this->getRequest()->getPostValue();

        if($data)
        {
            try{
                $id = $data['entity_id'];

                $geoTarget = $this->geoTargetFactory->create()->load($id);

                $data = array_filter($data, function($value) {return $value !== ''; });

                $geoTarget->setData($data);
                $geoTarget->save();
                $this->messageManager->addSuccess(__('Successfully saved the item.'));
                $this->_objectManager->get('Magento\Backend\Model\Session')->setFormData(false);
                return $resultRedirect->setPath('*/*/');
            }
            catch(\Exception $e)
            {
                $this->messageManager->addError($e->getMessage());
                $this->_objectManager->get('Magento\Backend\Model\Session')->setFormData($data);
                return $resultRedirect->setPath('*/*/edit', ['id' => $geoTarget->getId()]);
            }
        }

         return $resultRedirect->setPath('*/*/');
    }
}
