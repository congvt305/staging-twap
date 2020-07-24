<?php
/**
 * Created by Eguana Team.
 * User: sonia
 * Date: 7/24/20
 * Time: 10:46 AM
 */

namespace Eguana\NameSorter\Block\Widget;


class Name extends \Magento\Customer\Block\Widget\Name
{
    /**
     * @inheritdoc
     */
    public function _construct()
    {
        parent::_construct();

        // default template location
        $this->setTemplate('Eguana_NameSorter::widget/name.phtml');
    }

}
