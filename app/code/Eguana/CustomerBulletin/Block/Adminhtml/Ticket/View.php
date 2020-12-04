<?php
/**
 * @author Eguana Team
 * @copyriht Copyright (c) 2020 Eguana {http://eguanacommerce.com}
 * Created by PhpStorm
 * User: bilalyounas
 * Date: 30/9/20
 * Time: 06:48 PM
 */
namespace Eguana\CustomerBulletin\Block\Adminhtml\Ticket;

/**
 * Adminhtml Ticket view block.
 *
 */
class View extends \Magento\Backend\Block\Widget\Container
{

    /**
     * @inheritdoc
     */
    protected function _construct()
    {
        parent::_construct();

        $this->buttonList->remove('delete');
        $this->buttonList->remove('reset');
        $this->buttonList->remove('save');
        $this->buttonList->add(
            'back',
            [
                'label' => __('Back'),
                'onclick' => "window.location.href = '" . $this->getBackUrl() . "'",
                'class' => 'back'
            ]
        );

        $this->buttonList->add(
            'quote_send',
            [
                    'label' => __('Send'),
                    'class' => 'send primary',
            ]
        );
    }

    /**
     * Get URL for back (reset) button.
     *
     * @return string
     */
    protected function getBackUrl()
    {
        return $this->getUrl('*/*/');
    }

    /**
     * URL getter.
     *
     * @param string $params  [optional]
     * @param array  $params2 [optional]
     * @return string
     */
    public function getUrl($params = '', $params2 = [])
    {
        $params2['ticket_id'] = $this->getTicketId();
        return parent::getUrl($params, $params2);
    }

    /**
     * Retrieve Ticket Identifier.
     *
     * @return int
     */
    protected function getTicketId()
    {
        return $this->getRequest()->getParam('ticket_id');
    }
}
