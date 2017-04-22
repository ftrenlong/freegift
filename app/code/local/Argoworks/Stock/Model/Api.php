<?php 
class Argoworks_Stock_Model_Api extends Mage_Api_Model_Resource_Abstract{
	
	public function setStockData($product_id = 0, $instock = 1, $warehouse_data = array()) {
	
		try {
			if(!$product_id) return 'There is no product to update.';
			$item = Mage::getModel('advancedinventory/item')->loadByProductId($product_id);
			$item->setData(array("id" => $item->getId(), "product_id" => $product_id, "manage_local_stock" => 1))->save();
			$inventory = Mage::getModel('cataloginventory/stock_item')->loadByProduct($product_id);
			foreach($warehouse_data as $warehouse_id => $data){
				try {
					if(!$data){
						$data = array("manage_stock" => 1, "quantity_in_stock" => 0, "backorder_allowed" => 0, "use_config_setting_for_backorders" => 1);
					}
					$stock = Mage::getModel('advancedinventory/stock')->getStockByProductIdAndPlaceId($product_id, $warehouse_id);
					$origin_qty = $stock->getQuantityInStock();
					$data["id"] = $stock->getId();
					$data["place_id"] = $warehouse_id;
					$data["product_id"] = $product_id;
					$data["localstock_id"] = Mage::getModel('advancedinventory/item')->loadByProductId($product_id)->getId();
					if ($stock->getQuantity_in_stock() != $data['quantity_in_stock'] || $stock->getUse_config_setting_for_backorders() != $data['use_config_setting_for_backorders'] || $stock->getManageStock() != $data['manage_stock'] || $stock->getBackorder_allowed() != @$data['backorder_allowed']) {
						$stock->setData($data)->save();
					}
					$new_qty = $inventory->getQty() - $origin_qty + $data['quantity_in_stock'];
					$inventory->setQty($new_qty);
					$inventory->save();
					
				}catch(Exception $e){
					return $e->getMessage();
				}
			}
			$inventory->setData('is_in_stock', $instock)->save();
			return 1;
		} catch (Exception $e) {
			return $e->getMessage();
		}
	}
	
	public function getStockData($product_id, $warehouse_ids) {
		
		try {
			$result = array();
			foreach($warehouse_ids as $warehouse_id){
				$result[$warehouse_id] = Mage::getModel('advancedinventory/stock')->getStockByProductIdAndPlaceId($product_id, $warehouse_id)->getData();
			}
			return $result;
		}catch(Exception $e){
			return $e->getMessage();
		}
	}
}
?>