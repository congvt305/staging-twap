<?php
/**
 * Created by Eguana Team.
 * User: sonia
 * Date: 7/14/20
 * Time: 6:09 AM
 */

namespace Eguana\GWLogistics\Controller\Adminhtml\Reverseorder;

use Magento\Backend\App\Action;


//class Create extends \Magento\Rma\Controller\Adminhtml\Rma
class Create extends \Magento\Backend\App\Action
{
    /**
     * @var \Eguana\GWLogistics\Model\Service\CreateReverseLogisticsOrder
     */
    private $createReverseLogisticsOrder;
    /**
     * @var \Magento\Rma\Api\RmaRepositoryInterface
     */
    private $rmaRepository;

    public function __construct(
       \Eguana\GWLogistics\Model\Service\CreateReverseLogisticsOrder $createReverseLogisticsOrder,
       \Magento\Rma\Api\RmaRepositoryInterface $rmaRepository,
       Action\Context $context
   )
   {
       parent::__construct($context);
       $this->createReverseLogisticsOrder = $createReverseLogisticsOrder;
       $this->rmaRepository = $rmaRepository;
   }


    /**
     * Authorization level of a basic admin session
     */
    const ADMIN_RESOURCE = 'Eguana_GWLogistics::reverse_order_create';

    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface|void
     * @throws \Exception
     */
    public function execute()
    {
        try {
            $rmaId = $this->getRequest()->getParam('rma_id');
            $rma = $this->rmaRepository->get(intval($rmaId));
            $result = $this->createReverseLogisticsOrder->process($rma);
            if (isset($result['ErrorMessage'])) {
                $this->messageManager->addErrorMessage($result['ErrorMessage']);
            } else {
                $this->messageManager->addSuccessMessage(__('Reverse Logistics Order Created Successfully.'));
            }
        } catch (\Exception $e) {
            $this->messageManager->addExceptionMessage($e);
        }
        $this->_redirect('adminhtml/rma/edit', ['id' => $rmaId]);
    }

}
