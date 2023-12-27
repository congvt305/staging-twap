<?php
namespace Magenest\Popup\Model;

use Magento\Framework\Model\AbstractModel;

class Template extends AbstractModel
{
    public const YESNO_BUTTON = 1;
    public const CONTACT_FORM = 2;
    public const SHARE_SOCIAL = 3;
    public const SUBCRIBE     = 4;
    public const STATIC_POPUP = 5;
    public const HOT_DEAL = 6;

    /**
     * @inheritDoc
     */
    public function _construct()
    {
        $this->_init(ResourceModel\Template::class);
    }

    /**
     * Retrieve template text wrapper
     *
     * @return string
     */
    public function getHtmlContent()
    {
        if (!$this->getData('html_content') && !$this->getTemplateId()) {
            $this->setData('html_content', null);
        }

        return $this->getData('html_content');
    }
}
