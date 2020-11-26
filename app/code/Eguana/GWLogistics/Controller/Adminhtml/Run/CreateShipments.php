<?php
/**
 * Created by Eguana Team.
 * User: sonia
 * Date: 11/19/20
 * Time: 12:53 PM
 */
declare(strict_types=1);

namespace Eguana\GWLogistics\Controller\Adminhtml\Run;

use Magento\Backend\App\Action;

class CreateShipments extends \Magento\Backend\App\Action
{
    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    const ADMIN_RESOURCE = 'Magento_Config::config';
    /**
     * @var \Eguana\GWLogistics\Cron\CreateShipmentsFactory
     */
    private $createShipmentsCron;

    public function __construct(
        \Eguana\GWLogistics\Cron\CreateShipmentsFactory $createShipmentsCronFactory,
        Action\Context $context
    ) {
        parent::__construct($context);
        $this->createShipmentsCronFactory = $createShipmentsCronFactory;
    }

    public function execute()
    {
        $result = $this->createShipmentsCronFactory->create()
            ->execute();
        $this->messageManager->addSuccessMessage('Done');

        $redirectBack = $this->_redirect->getRefererUrl();
        $this->_redirect($redirectBack);
    }
}