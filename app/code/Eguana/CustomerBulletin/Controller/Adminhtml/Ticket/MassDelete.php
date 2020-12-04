<?php
/**
 * @author Eguana Team
 * @copyriht Copyright (c) 2020 Eguana {http://eguanacommerce.com}
 * Created by PhpStorm
 * User: bilalyounas
 * Date: 30/9/20
 * Time: 06:48 PM
 */
declare(strict_types=1);

namespace Eguana\CustomerBulletin\Controller\Adminhtml\Ticket;

use Eguana\CustomerBulletin\Model\Ticket;
use Magento\Framework\App\Action\HttpPostActionInterface;

/**
 * Class MassDelete to delete multiple records
 */
class MassDelete extends AbstractMassAction implements HttpPostActionInterface
{
    /**
     * @param Ticket $data
     * @return $this
     */
    protected function massAction(Ticket $data) : MassDelete
    {
        $this->ticketRepository->delete($data);
        return $this;
    }
}
