<?php
declare(strict_types=1);

namespace Magenest\Popup\Setup\Patch\Data;

use Magenest\Popup\Model\PopupFactory;
use Magenest\Popup\Model\ResourceModel\Log;
use Magenest\Popup\Model\ResourceModel\Log\CollectionFactory;
use Magenest\Popup\Model\ResourceModel\Popup;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Framework\Setup\Patch\PatchVersionInterface;

class UpdateLogContent implements DataPatchInterface, PatchVersionInterface
{
    /** @var ModuleDataSetupInterface */
    private $moduleDataSetup;

    /** @var Log */
    private $logResources;

    /** @var CollectionFactory */
    private $logCollection;

    /** @var PopupFactory */
    private $popupModel;

    /** @var Popup */
    private $popupResource;

    /** @var SerializerInterface */
    private $serializer;

    /**
     * @param Log $logResources
     * @param Popup $popupResource
     * @param PopupFactory $popupModel
     * @param CollectionFactory $logCollection
     * @param SerializerInterface $serializer
     * @param ModuleDataSetupInterface $moduleDataSetup
     */
    public function __construct(
        Log                      $logResources,
        Popup                    $popupResource,
        PopupFactory             $popupModel,
        CollectionFactory        $logCollection,
        SerializerInterface      $serializer,
        ModuleDataSetupInterface $moduleDataSetup
    ) {
        $this->serializer      = $serializer;
        $this->logResources    = $logResources;
        $this->popupResource   = $popupResource;
        $this->popupModel      = $popupModel;
        $this->logCollection   = $logCollection;
        $this->moduleDataSetup = $moduleDataSetup;
    }

    /**
     * Apply Patch
     *
     * @return void
     * @throws \Magento\Framework\Exception\AlreadyExistsException
     */
    public function apply()
    {
        $this->moduleDataSetup->startSetup();
        $logCollection = $this->logCollection->create();
        $popupModel    = $this->popupModel->create();
        /** @var \Magenest\Popup\Model\Log $log */
        foreach ($logCollection as $log) {
            $string = $log->getContent();
            if ($this->isJSON($string)) {
                $content = $this->serializer->unserialize($string ?? 'null');
                if (is_array($content)) {
                    $count  = 0;
                    $result = '';
                    foreach ($content as $raw) {
                        if ($count == 0) {
                            $count++;
                            continue;
                        }
                        if (isset($raw['name'])) {
                            $result .= $raw['name'] . ": " . $raw['value'] . "| ";
                        }
                    }
                    $string = $result ?? $content;
                }
            }

            $this->popupResource->load($popupModel, $log->getPopupId());
            $log->setPopupName($popupModel->getPopupName());
            $log->setContent($string);

            $this->logResources->save($log);
            $popupModel->unsetData();
        }
        $this->moduleDataSetup->endSetup();
    }

    /**
     * Check JSON
     *
     * @param string $string
     * @return bool
     */
    public function isJSON($string)
    {
        return is_string($string) && is_array($this->serializer->unserialize($string ?? 'null'));
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
        return "1.1.0";
    }
}
