<?php 
class Argoworks_Tax_Model_Api extends Mage_Api_Model_Resource_Abstract{
	
	public function listProductTaxClasses(){
		try {
			return Mage::getResourceModel('tax/class_collection')->addFieldToFilter('class_type', Mage_Tax_Model_Class::TAX_CLASS_TYPE_PRODUCT)->load()->toOptionArray();
		} catch (Mage_Core_Exception $e) {
			$this->_fault('data_invalid', $e->getMessage());
			// We cannot know all the possible exceptions,
			// so let's try to catch the ones that extend Mage_Core_Exception
		} catch (Exception $e) {
			$this->_fault('data_invalid', $e->getMessage());
		}
	}
	
	public function listCustomerTaxClasses(){
		try {
			return Mage::getResourceModel('tax/class_collection')->addFieldToFilter('class_type', Mage_Tax_Model_Class::TAX_CLASS_TYPE_CUSTOMER)->load()->toOptionArray();
		} catch (Mage_Core_Exception $e) {
			$this->_fault('data_invalid', $e->getMessage());
			// We cannot know all the possible exceptions,
			// so let's try to catch the ones that extend Mage_Core_Exception
		} catch (Exception $e) {
			$this->_fault('data_invalid', $e->getMessage());
		}
	}
	
	public function addProductTaxClass($name){
		
		if (!$name) {
			$this->_fault('data_invalid');
		}
		try {
			$taxclass = Mage::getModel('tax/class')->getCollection()->addFieldToFilter('class_name', $name)->addFieldToFilter('class_type', Mage_Tax_Model_Class::TAX_CLASS_TYPE_PRODUCT)->getFirstItem();
			if($taxclass->getId()) return $taxclass->getId();
			$taxclass = Mage::getModel('tax/class')->setData(array('class_name' => $name,'class_type' => Mage_Tax_Model_Class::TAX_CLASS_TYPE_PRODUCT))->save();
			return $taxclass->getId();
		} catch (Mage_Core_Exception $e) {
			$this->_fault('data_invalid', $e->getMessage());
			// We cannot know all the possible exceptions,
			// so let's try to catch the ones that extend Mage_Core_Exception
		} catch (Exception $e) {
			$this->_fault('data_invalid', $e->getMessage());
		}
	}
	
	public function addCustomerTaxClass($name){
		
		if (!$name) {
			$this->_fault('data_invalid');
		}
		try {
			$taxclass = Mage::getModel('tax/class')->getCollection()->addFieldToFilter('class_name', $name)->addFieldToFilter('class_type', Mage_Tax_Model_Class::TAX_CLASS_TYPE_CUSTOMER)->getFirstItem();
			if($taxclass->getId()) return $taxclass->getId();
			$taxclass = Mage::getModel('tax/class')->setData(array('class_name' => $name,'class_type' => Mage_Tax_Model_Class::TAX_CLASS_TYPE_CUSTOMER))->save();
			return $taxclass->getId();
		} catch (Mage_Core_Exception $e) {
			$this->_fault('data_invalid', $e->getMessage());
			// We cannot know all the possible exceptions,
			// so let's try to catch the ones that extend Mage_Core_Exception
		} catch (Exception $e) {
			$this->_fault('data_invalid', $e->getMessage());
		}
	}
	
	public function getProductTaxClassByName($name){
		
		if (!$name) {
			$this->_fault('data_invalid');
		}
		try {
			$taxclass = Mage::getModel('tax/class')->getCollection()->addFieldToFilter('class_name', $name)->addFieldToFilter('class_type', Mage_Tax_Model_Class::TAX_CLASS_TYPE_PRODUCT)->getFirstItem();
			return $taxclass->getId();
		} catch (Mage_Core_Exception $e) {
			$this->_fault('data_invalid', $e->getMessage());
			// We cannot know all the possible exceptions,
			// so let's try to catch the ones that extend Mage_Core_Exception
		} catch (Exception $e) {
			$this->_fault('data_invalid', $e->getMessage());
		}
	}
	
	public function getCustomerTaxClassByName($name){
		
		if (!$name) {
			$this->_fault('data_invalid');
		}
		try {
			$taxclass = Mage::getModel('tax/class')->getCollection()->addFieldToFilter('class_name', $name)->addFieldToFilter('class_type', Mage_Tax_Model_Class::TAX_CLASS_TYPE_CUSTOMER)->getFirstItem();
			return $taxclass->getId();
		} catch (Mage_Core_Exception $e) {
			$this->_fault('data_invalid', $e->getMessage());
			// We cannot know all the possible exceptions,
			// so let's try to catch the ones that extend Mage_Core_Exception
		} catch (Exception $e) {
			$this->_fault('data_invalid', $e->getMessage());
		}
	}
	
	public function getAttributeGroup($name, $set_id){
		
		if (!$name || !$set_id) {
			$this->_fault('data_invalid');
		}
		try {
			$groups = Mage::getModel('eav/entity_attribute_group')
							->getResourceCollection()
							->setAttributeSetFilter($set_id)
							->setSortOrder()
							->load();
		
			$attributeCodes = array();
			foreach ($groups as $group) {
				if($group->getAttributeGroupName() == $name){
					return $group->getAttributeGroupId();
				}
			}
			return 0;
		} catch (Mage_Core_Exception $e) {
			$this->_fault('data_invalid', $e->getMessage());
			// We cannot know all the possible exceptions,
			// so let's try to catch the ones that extend Mage_Core_Exception
		} catch (Exception $e) {
			$this->_fault('data_invalid', $e->getMessage());
		}
	}
}
?>