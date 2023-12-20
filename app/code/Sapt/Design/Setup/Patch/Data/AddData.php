<?php
declare(strict_types=1);

namespace Sapt\Design\Setup\Patch\Data;

use Magento\Framework\Setup\Patch\DataPatchInterface;

class AddData implements DataPatchInterface
{
    /**
     * @var \Magento\PageBuilder\Model\TemplateFactory
     */
     private $templateFactory;

    /**
     * @param \Magento\PageBuilder\Model\TemplateFactory $templateFactory
     */
     public function __construct(
          \Magento\PageBuilder\Model\TemplateFactory $templateFactory
     ) {
          $this->templateFactory = $templateFactory;
     }

     public function apply()
     {
          $sampleData = [
               [
                    'name' => '',
                    'preview_image' => '',
                    'template' => '',
                    'created_for' => 'any'
               ],
               [
                    'name' => '',
                    'preview_image' => '',
                    'template' => '',
                    'created_for' => 'any'
               ],
               [
                    'name' => '',
                    'preview_image' => '',
                    'template' => '',
                    'created_for' => 'any'
               ],
               [
                    'name' => '',
                    'preview_image' => '',
                    'template' => '',
                    'created_for' => 'any'
               ],
               [
                    'name' => '',
                    'preview_image' => '',
                    'template' => '',
                    'created_for' => 'any'
               ],
               [
                    'name' => '',
                    'preview_image' => '',
                    'template' => '',
                    'created_for' => 'any'
               ],
               [
                    'name' => '',
                    'preview_image' => '',
                    'template' => '',
                    'created_for' => 'any'
               ],
               [
                    'name' => '',
                    'preview_image' => '',
                    'template' => '',
                    'created_for' => 'any'
               ]
          ];
          foreach ($sampleData as $data) {
               $this->templateFactory->create()->setData($data)->save();
          }
     }

     public static function getDependencies()
     {
          return [];
     }

     public function getAliases()
     {
          return [];
     }

}
