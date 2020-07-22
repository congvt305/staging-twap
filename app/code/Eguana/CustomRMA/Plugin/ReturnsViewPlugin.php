<?php
/**
 * Created by Eguana Team.
 * User: sonia
 * Date: 7/22/20
 * Time: 2:28 PM
 */

namespace Eguana\CustomRMA\Plugin;


use Magento\Rma\Block\Returns\View;

class ReturnsViewPlugin
{

    /**
     * @param \Magento\Rma\Block\Returns\View $subject
     * @param $result
     */
    public function after_construct(\Magento\Rma\Block\Returns\View $subject, $result)
    {
        $subject->setTemplate('Eguana_CustomRMA::return/view.phtml');
        return $result;
    }
}
