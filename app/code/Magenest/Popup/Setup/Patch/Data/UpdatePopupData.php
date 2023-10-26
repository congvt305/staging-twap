<?php
declare(strict_types=1);

namespace Magenest\Popup\Setup\Patch\Data;

use Magenest\Popup\Helper\Data;
use Magenest\Popup\Model\ResourceModel\Popup;
use Magenest\Popup\Model\ResourceModel\Template;
use Magento\Framework\Exception\AlreadyExistsException;
use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Framework\Setup\Patch\PatchVersionInterface;

class UpdatePopupData implements DataPatchInterface, PatchVersionInterface
{
    /** @var ModuleDataSetupInterface */
    private $moduleDataSetup;

    /** @var Popup */
    private $popupResource;

    /** @var Popup\CollectionFactory */
    protected $popupCollection;

    /** @var Data */
    private $helperData;

    /** @var Template\Collection */
    private $templateCollection;

    /** @var Template\CollectionFactory */
    private $popupTemplateCollection;

    /** @var Template */
    private $templateResource;

    /**
     * @param Data $helperData
     * @param Popup $popupResource
     * @param Template $templateResource
     * @param Popup\CollectionFactory $popupCollection
     * @param ModuleDataSetupInterface $moduleDataSetup
     * @param Template\CollectionFactory $popupTemplateCollection
     */
    public function __construct(
        Data                       $helperData,
        Popup                      $popupResource,
        Template                   $templateResource,
        Popup\CollectionFactory    $popupCollection,
        ModuleDataSetupInterface   $moduleDataSetup,
        Template\CollectionFactory $popupTemplateCollection
    ) {
        $this->helperData              = $helperData;
        $this->popupResource           = $popupResource;
        $this->templateResource        = $templateResource;
        $this->popupCollection         = $popupCollection;
        $this->moduleDataSetup         = $moduleDataSetup;
        $this->popupTemplateCollection = $popupTemplateCollection;
    }

    /**
     * Apply Patch
     *
     * @return void
     * @throws AlreadyExistsException
     * @throws FileSystemException
     * @throws LocalizedException
     */
    public function apply()
    {
        $this->moduleDataSetup->startSetup();

        $this->updatePopupConfig();

        // Update new default template
        $popup_type_default = $this->helperData->getPopupTemplateDefault();

        // set status for template default
        $this->setDefaultTemplateStatus($popup_type_default);

        // set status for tempalate_edited
        $this->setEditedTemplateStatus();

        // set status for tempalate_default_deleted
        $this->setDeletedTemplateStatus($popup_type_default);

        // add class = 'magenest-popup-step' to html_content of template default
        $this->updateDefaultTemplateHtml($popup_type_default);

        $this->moduleDataSetup->endSetup();
    }

    /**
     * Update Popup Config
     *
     * @throws AlreadyExistsException
     */
    private function updatePopupConfig()
    {
        $popups = $this->popupCollection->create();
        /** @var  \Magenest\Popup\Model\Popup $popup */
        foreach ($popups as $popup) {
            if ($popup->getData('floating_button_text_color')
                && $popup->getData('floating_button_text_color')[0] != '#') {
                $popup->setData('floating_button_text_color', '#' . $popup->getData('floating_button_text_color'));
            }
            if ($popup->getData('floating_button_background_color')
                && $popup->getData('floating_button_background_color')[0] != '#') {
                $popup->setData(
                    'floating_button_background_color',
                    '#' . $popup->getData('floating_button_background_color')
                );
            }
            if (!$popup->getData('floating_button_hover_color')) {
                $popup->setData('floating_button_hover_color', '#eaeaea');
            }
            if (!$popup->getData('floating_button_text_hover_color')) {
                $popup->setData('floating_button_text_hover_color', '#0e3e81');
            }
            if (!$popup->setData('customer_group_ids')) {
                $popup->setData('customer_group_ids', '32000');
            }
            $this->popupResource->save($popup);
        }
    }

    /**
     * Set Default Template Status
     *
     * @param array $defaultPopup
     * @throws AlreadyExistsException
     * @throws FileSystemException
     */
    private function setDefaultTemplateStatus($defaultPopup)
    {
        foreach ($defaultPopup as $type) {
            /** @var \Magenest\Popup\Model\Template $matchedTemplate */
            $matchedTemplate = $this->getTemplateCollection()
                ->addFieldToFilter('class', $type['class'])
                ->addFieldToFilter('template_name', $type['name'])
                ->addFieldToFilter('template_type', $type['type'])
                ->addFieldToFilter('html_content', $this->helperData->getTemplateDefault($type['path']))
                ->addFieldToFilter('css_style', '')
                ->addFieldToFilter('status', 0)
                ->setPageSize(0)->setCurPage(0)
                ->getFirstItem();
            if ($matchedTemplate->getTemplateId()) {
                $matchedTemplate->setStatus(1);
                $this->templateResource->save($matchedTemplate);
            }
        }
    }

    /**
     * Get Template Collection
     *
     * @return Template\Collection
     */
    private function getTemplateCollection()
    {
        if ($this->templateCollection === null) {
            $this->templateCollection = $this->popupTemplateCollection->create();
        }

        return $this->templateCollection->reset();
    }

    /**
     * Set Edited Template Status
     *
     * @throws AlreadyExistsException
     */
    private function setEditedTemplateStatus()
    {
        $templateEdited = $this->getTemplateCollection()
            ->addFieldToFilter('status', ['nin' => [1]])
            ->getItems();
        /** @var \Magenest\Popup\Model\Template $template */
        foreach ($templateEdited as $template) {
            $template->setStatus(2);
            $this->templateResource->save($template);
        }
    }

    /**
     * Set Deleted Template Status
     *
     * @param array $defaultPopup
     * @throws FileSystemException
     * @throws LocalizedException
     */
    private function setDeletedTemplateStatus($defaultPopup)
    {
        $dataTemplateDefault = [];
        $templateDefault     = $this->getTemplateCollection()
            ->addFieldToFilter('status', ['eq' => 1])
            ->addFieldToSelect('class')
            ->getData();
        $templateClass       = array_column($templateDefault, 'class');
        foreach ($defaultPopup as $type) {
            $check = in_array($type['class'], $templateClass);
            if (!$check) {
                $dataTemplateDefault[] = [
                    'template_name' => $type['name'],
                    'template_type' => $type['type'],
                    'html_content'  => $this->helperData->getTemplateDefault($type['path']),
                    'css_style'     => '',
                    'class'         => $type['class'],
                    'status'        => 1
                ];
            }
        }
        if (!empty($dataTemplateDefault)) {
            $this->templateResource->insertMultiple($dataTemplateDefault);
        }
    }

    /**
     * Update Default Template HTML
     *
     * @param array $defaultPopup
     * @throws AlreadyExistsException
     * @throws FileSystemException
     */
    private function updateDefaultTemplateHtml($defaultPopup)
    {
        $type_array      = [];
        $templateDefault = $this->getTemplateCollection()
            ->addFieldToFilter('status', ['eq' => 1])
            ->getItems();
        foreach ($defaultPopup as $type) {
            $type_array[$type['class']] = $type['path'];
        }
        /** @var \Magenest\Popup\Model\Template $template */
        foreach ($templateDefault as $template) {
            if (isset($type_array[$template['class']])) {
                $html_content = $this->helperData->getTemplateDefault($type_array[$template['class']]);
                $template->setHtmlContent($html_content);
                $this->templateResource->save($template);
            }
        }
    }

    /**
     * @inheritdoc
     */
    public function getAliases()
    {
        return [];
    }

    /**
     * @inheritdoc
     */
    public static function getDependencies()
    {
        return [];
    }

    /**
     * @inheritDoc
     */
    public static function getVersion()
    {
        return "1.2.0";
    }
}
