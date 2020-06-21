<?php
/**
 * Created by PhpStorm.
 * User: jason
 * Date: 2016-09-05
 * Time: 오전 11:36
 */

namespace Eguana\StoreLocator\Block\Adminhtml\Edit\Button;

use Magento\Customer\Api\AccountManagementInterface;
use Magento\Framework\View\Element\UiComponent\Control\ButtonProviderInterface;

/**
 * Save Button
 *
 * Class Save
 *  Eguana\StoreLocator\Block\Adminhtml\Edit\Button
 */
class Save extends GenericButton implements ButtonProviderInterface
{
    /**
     * This is not in this module but it is protected so it may be possibole that it is using anywhere else
     * @var AccountManagementInterface
     */
    protected $customerAccountManagement;

    /**
     * @return array
     */
    public function getButtonData()
    {
        $data = [
            'label' => __('Save'),
            'class' => 'save primary',
            'data_attribute' => [
                'mage-init' => ['button' => ['event' => 'save']],
                'form-role' => 'save',
            ],
            'sort_order' => 90,
        ];
        return $data;
    }
}
