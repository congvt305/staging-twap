<?php
namespace Magenest\Popup\Observer;

use Magenest\Popup\Model\Popup;
use Magenest\Popup\Model\ResourceModel\Popup\CollectionFactory;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

class SetHtmlContentPopup implements ObserverInterface
{
    /** @var \Magenest\Popup\Model\ResourceModel\Popup */
    private $popupResources;

    /** @var CollectionFactory */
    private $popupCollection;

    /**
     * SetHtmlContentPopup constructor.
     * @param \Magenest\Popup\Model\ResourceModel\Popup $popupResources
     * @param CollectionFactory $popupCollection
     */
    public function __construct(
        \Magenest\Popup\Model\ResourceModel\Popup $popupResources,
        CollectionFactory $popupCollection
    ) {
        $this->popupResources = $popupResources;
        $this->popupCollection = $popupCollection;
    }

    /**
     * Execute
     *
     * @param Observer $observer
     * @throws \Exception
     */
    public function execute(Observer $observer)
    {
        $template = $observer->getData('template');
        $template_id = $template->getTemplateId();
        $popupCollection = $this->popupCollection->create()
            ->addFieldToFilter('popup_template_id', $template_id)
            ->getItems();
        /** @var Popup $popup */
        foreach ($popupCollection as $popup) {
            $popup->setHtmlContent($template->getHtmlContent());
            $this->popupResources->save($popup);
        }
    }
}
