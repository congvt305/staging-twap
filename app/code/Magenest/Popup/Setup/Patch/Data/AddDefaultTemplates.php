<?php
declare(strict_types=1);

namespace Magenest\Popup\Setup\Patch\Data;

use Magenest\Popup\Helper\Data;
use Magenest\Popup\Model\ResourceModel\Template;
use Magenest\Popup\Model\ResourceModel\Template\CollectionFactory;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Framework\Setup\Patch\PatchVersionInterface;

class AddDefaultTemplates implements DataPatchInterface, PatchVersionInterface
{
    /** @var ModuleDataSetupInterface */
    private $moduleDataSetup;

    /** @var Data */
    private $helperData;

    /** @var Template */
    private $popupTemplateResources;

    /** @var CollectionFactory */
    private $popupTemplateCollection;

    /**
     * @param Data $helperData
     * @param Template $popupTemplateResources
     * @param ModuleDataSetupInterface $moduleDataSetup
     * @param CollectionFactory $popupTemplateCollection
     */
    public function __construct(
        Data                     $helperData,
        Template                 $popupTemplateResources,
        ModuleDataSetupInterface $moduleDataSetup,
        CollectionFactory        $popupTemplateCollection
    ) {
        $this->helperData              = $helperData;
        $this->moduleDataSetup         = $moduleDataSetup;
        $this->popupTemplateResources  = $popupTemplateResources;
        $this->popupTemplateCollection = $popupTemplateCollection;
    }

    /**
     * Apply Patch
     *
     * @return AddDefaultTemplates|void
     * @throws \Magento\Framework\Exception\FileSystemException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function apply()
    {
        $this->moduleDataSetup->startSetup();
        $popup_type = [
            [
                'path'  => 'hot_deal/popup_1',
                'type'  => '6',
                'class' => 'popup-default-40',
            ],
            [
                'path'  => 'hot_deal/popup_2',
                'type'  => '6',
                'class' => 'popup-default-41',
            ]
        ];
        $data       = [];
        $count      = $this->popupTemplateCollection->create()->getSize();

        if (!empty($popup_type)) {
            foreach ($popup_type as $type) {
                $data[] = [
                    'template_name' => "Default Template " . $count,
                    'template_type' => $type['type'],
                    'html_content'  => $this->helperData->getTemplateDefault($type['path']),
                    'css_style'     => '',
                    'class'         => $type['class'],
                    'status'        => 1
                ];
                $count++;
            }

            $this->popupTemplateResources->insertMultiple($data);
        }
        $this->moduleDataSetup->endSetup();
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
        return [InstallDefaultTemplates::class];
    }

    /**
     * Get Version
     *
     * @return string
     */
    public static function getVersion()
    {
        return "1.1.0";
    }
}
