<?php
/**
 * @author Eguana Team
 * @copyriht Copyright (c) 2020 Eguana {http://eguanacommerce.com}
 * Created by PhpStorm
 * User: Mobeen
 * Date: 30/7/20
 * Time: 5:10 PM
 */

namespace Eguana\CustomRMA\Model\Config\Source;

use Magento\Eav\Api\AttributeRepositoryInterface;

/**
 * This class is for RMA resolution dropdown
 *
 * Class Resolution
 */
class Resolution
{
    /**
     * @var AttributeRepositoryInterface
     */
    private $attributeRepositoryInterface;

    /**
     * Resolution constructor.
     * @param AttributeRepositoryInterface $attributeRepositoryInterface
     */
    public function __construct(AttributeRepositoryInterface $attributeRepositoryInterface) {
        $this->attributeRepositoryInterface = $attributeRepositoryInterface;
    }
    /**
     * to option array
     *
     * @return array
     */
    public function toOptionArray()
    {
      return $this->getResolution();
    }

    /**
     * This function will return resolution dropdown
     * @return array
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    private function getResolution(){
        $dropDown[] = ['label'=> 'Select rma resolution','value'=> ''];
        $attribute = $this->attributeRepositoryInterface->get('rma_item', 'resolution');
        $attribute->setStoreId(0);
        $options = $attribute->getOptions();
        foreach ($options as $option) {
            if (!empty($option->getLabel()) && !empty($option->getValue())){
                $dropDown[] = ['label'=>$option->getLabel(),'value'=> $option->getValue()];
            }
        }
        return $dropDown;
    }


}
