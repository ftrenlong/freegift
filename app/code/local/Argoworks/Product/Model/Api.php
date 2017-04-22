<?php 
class Argoworks_Product_Model_Api extends Mage_Api_Model_Resource_Abstract{
	
	public function getProductPriceChanges($productId = null){
		try {
			$product = Mage::getModel('catalog/product')->load($productId);
			$productAttributeOptions = $product->getTypeInstance(true)->getConfigurableAttributesAsArray($product);
			$attributeOptions = array();
			foreach ($productAttributeOptions as $productAttribute) {
				$attribute_code = $productAttribute['attribute_code'];
				foreach ($productAttribute['values'] as $attribute) {
					if(empty($attributeOptions[$attribute_code])){
						$attributeOptions[$attribute_code] = array();
						$attributeOptions[$attribute_code][$attribute['label']]= $attribute['pricing_value'];
					}else{
						if(!in_array($attribute['label'], $attributeOptions[$attribute_code])){
							$attributeOptions[$attribute_code][$attribute['label']]= $attribute['pricing_value'];
						}
					}
				}
			}
			return $attributeOptions;
		} catch (Exception $e) {
			$this->_fault('data_invalid', $e->getMessage());
		}
	}
	
	public function reindexAll(){
		try {
			set_time_limit(6000);
			/* @var $indexCollection Mage_Index_Model_Resource_Process_Collection */
			$indexCollection = Mage::getModel('index/process')->getCollection();
			foreach ($indexCollection as $index) {
				/* @var $index Mage_Index_Model_Process */
				$index->reindexAll();
			}
			return 1;
		} catch (Exception $e) {
			$this->_fault('data_invalid', $e->getMessage());
		}
	}
}
?>