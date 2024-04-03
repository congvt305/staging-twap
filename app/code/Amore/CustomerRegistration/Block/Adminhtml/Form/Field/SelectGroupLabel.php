<?php
/**
 * @author Eguana Team
 * @copyright Copyright (c) 2020 Eguana {http://eguanacommerce.com}
 * Created by PhpStorm
 * User: umer
 * Date: 10/12/20
 * Time: 6:35 PM
 */
namespace Amore\CustomerRegistration\Block\Adminhtml\Form\Field;

use Magento\Customer\Model\ResourceModel\Group\Collection;
use Magento\Framework\View\Element\Context;
use Magento\Framework\View\Element\Html\Select;

/**
 * Class SelectGroupLabel
 *
 * Select group label customersgroups
 */
class SelectGroupLabel extends Select
{
    const GUEST = 'NOT LOGGED IN';

    /**
     * @var Collection
     */
    private $customerGroup;

    /**
     * SelectGroupLabel constructor.
     * @param Context $context
     * @param Collection $customerGroup
     * @param array $data
     */
    public function __construct(
        Context $context,
        Collection $customerGroup,
        array $data = []
    ) {
        $this->_customerGroup = $customerGroup;
        parent::__construct($context, $data);
    }

    /**
     * set Input Name
     * @param $value
     * @return mixed
     */
    public function setInputName($value)
    {
        return $this->setName($value);
    }

    /**
     * Render html
     * @return string
     */
    public function _toHtml()
    {
        if (!$this->getOptions()) {
            $customerGroups = $this->_customerGroup->toOptionArray();
            foreach ($customerGroups as $group) {
                if ($group['label'] != self::GUEST) {
                    $this->addOption($group['label'], $group['label']);
                }
            }
        }
        return parent::_toHtml();
    }
}
