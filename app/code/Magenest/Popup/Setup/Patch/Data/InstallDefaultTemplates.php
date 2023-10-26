<?php
declare(strict_types=1);

namespace Magenest\Popup\Setup\Patch\Data;

use Magenest\Popup\Helper\Data;
use Magenest\Popup\Model\ResourceModel\Template;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Framework\Setup\Patch\PatchVersionInterface;

class InstallDefaultTemplates implements DataPatchInterface, PatchVersionInterface
{
    /** @var ModuleDataSetupInterface */
    private $moduleDataSetup;

    /** @var Data */
    private $helperData;

    /** @var Template */
    private $popupTemplateResources;

    /**
     * @param Data $helperData
     * @param Template $popupTemplateResources
     * @param ModuleDataSetupInterface $moduleDataSetup
     */
    public function __construct(
        Data                     $helperData,
        Template                 $popupTemplateResources,
        ModuleDataSetupInterface $moduleDataSetup
    ) {
        $this->helperData             = $helperData;
        $this->moduleDataSetup        = $moduleDataSetup;
        $this->popupTemplateResources = $popupTemplateResources;
    }

    /**
     * Apply Patch
     *
     * @return void
     * @throws \Magento\Framework\Exception\FileSystemException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function apply()
    {
        $this->moduleDataSetup->startSetup();
        $popupType = [
            [
                'path'  => 'yesno_button/popup_1',
                'type'  => '1',
                'class' => 'popup-default-3'
            ],
            [
                'path'  => 'contact_form/popup_1',
                'type'  => '2',
                'class' => 'popup-default-1'
            ],
            [
                'path'  => 'contact_form/popup_2',
                'type'  => '2',
                'class' => 'popup-default-4'
            ],
            [
                'path'  => 'share_social/popup_1',
                'type'  => '3',
                'class' => 'popup-default-5'
            ],
            [
                'path'  => 'subcribe_form/popup_2',
                'type'  => '4',
                'class' => 'popup-default-6'
            ],
            [
                'path'  => 'subcribe_form/popup_1',
                'type'  => '4',
                'class' => 'popup-default-2'
            ],
            [
                'path'  => 'static_form/popup_1',
                'type'  => '5',
                'class' => 'popup-default-7'
            ],
            [
                'path'  => 'static_form/popup_2',
                'type'  => '5',
                'class' => 'popup-default-8'
            ],
            [
                'path'  => 'subcribe_form/popup_3',
                'type'  => '4',
                'class' => 'popup-default-9'
            ],
            [
                'path'  => 'subcribe_form/popup_4',
                'type'  => '4',
                'class' => 'popup-default-10'
            ],
            [
                'path'  => 'yesno_button/popup_3',
                'type'  => '1',
                'class' => 'popup-default-11'
            ],
            [
                'path'  => 'static_form/popup_3',
                'type'  => '5',
                'class' => 'popup-default-12'
            ],
            [
                'path'  => 'static_form/popup_4',
                'type'  => '5',
                'class' => 'popup-default-13'
            ],
            [
                'path'  => 'subcribe_form/popup_5',
                'type'  => '4',
                'class' => 'popup-default-14'
            ],
            [
                'path'  => 'contact_form/popup_3',
                'type'  => '2',
                'class' => 'popup-default-15'
            ],
            [
                'path'  => 'subcribe_form/popup_6',
                'type'  => '4',
                'class' => 'popup-default-16'
            ],
            [
                'path'  => 'share_social/popup_2',
                'type'  => '3',
                'class' => 'popup-default-17'
            ],
            [
                'path'  => 'subcribe_form/popup_7',
                'type'  => '4',
                'class' => 'popup-default-18'
            ],
            [
                'path'  => 'subcribe_form/popup_8',
                'type'  => '4',
                'class' => 'popup-default-19'
            ],
            [
                'path'  => 'subcribe_form/popup_9',
                'type'  => '4',
                'class' => 'popup-default-20'
            ],
            [
                'path'  => 'static_form/popup_8',
                'type'  => '5',
                'class' => 'popup-default-21'
            ],
            [
                'path'  => 'static_form/popup_7',
                'type'  => '5',
                'class' => 'popup-default-22'
            ],
            [
                'path'  => 'subcribe_form/popup_10',
                'type'  => '4',
                'class' => 'popup-default-23'
            ],
            [
                'path'  => 'static_form/popup_6',
                'type'  => '5',
                'class' => 'popup-default-24'
            ],
            [
                'path'  => 'static_form/popup_9',
                'type'  => '5',
                'class' => 'popup-default-25'
            ],
            [
                'path'  => 'subcribe_form/popup_13',
                'type'  => '4',
                'class' => 'popup-default-27'
            ],
            [
                'path'  => 'static_form/popup_12',
                'type'  => '5',
                'class' => 'popup-default-28'
            ],
            [
                'path'  => 'share_social/popup_3',
                'type'  => '3',
                'class' => 'popup-default-29'
            ],
            [
                'path'  => 'share_social/popup_4',
                'type'  => '3',
                'class' => 'popup-default-30'
            ],
            [
                'path'  => 'contact_form/popup_6',
                'type'  => '2',
                'class' => 'popup-default-31'
            ],
            [
                'path'  => 'subcribe_form/popup_14',
                'type'  => '4',
                'class' => 'popup-default-32'
            ],
            [
                'path'  => 'static_form/popup_13',
                'type'  => '5',
                'class' => 'popup-default-33'
            ],
            [
                'path'  => 'yesno_button/popup_2',
                'type'  => '1',
                'class' => 'popup-default-34'
            ],
            [
                'path'  => 'yesno_button/popup_4',
                'type'  => '1',
                'class' => 'popup-default-35'
            ],
            [
                'path'  => 'subcribe_form/popup_12',
                'type'  => '4',
                'class' => 'popup-default-36'
            ],
            [
                'path'  => 'contact_form/popup_4',
                'type'  => '2',
                'class' => 'popup-default-26'
            ],
            [
                'path'  => 'contact_form/popup_5',
                'type'  => '2',
                'class' => 'popup-default-37'
            ],
            [
                'path'  => 'static_form/popup_10',
                'type'  => '5',
                'class' => 'popup-default-38'
            ],
            [
                'path'  => 'subcribe_form/popup_11',
                'type'  => '4',
                'class' => 'popup-default-39'
            ]
        ];

        $data  = [];
        $count = 0;
        foreach ($popupType as $type) {
            $data[] = [
                'template_name' => "Default Template " . $count,
                'template_type' => $type['type'],
                'html_content'  => $this->helperData->getTemplateDefault($type['path']),
                'css_style'     => '',
                'class'         => $type['class']
            ];
            $count++;
        }
        $this->popupTemplateResources->insertMultiple($data);
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
        return [];
    }

    /**
     * Get Version
     *
     * @return string
     */
    public static function getVersion()
    {
        return "1.0.0";
    }
}
