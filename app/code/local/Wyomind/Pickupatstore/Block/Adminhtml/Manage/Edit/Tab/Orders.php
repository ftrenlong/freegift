<?php

class Wyomind_Pickupatstore_Block_Adminhtml_Manage_Edit_Tab_Orders extends Mage_Adminhtml_Block_Sales_Order_Grid
{

 
    protected function _prepareCollection()
    {
        $pointofsaleId = Mage::app()->getRequest()->getParam('place_id');
        
        $collection = Mage::getResourceModel('sales/order_grid_collection');
        $collection->addFieldToFilter('pickup_pointofsale_id', $pointofsaleId);
        $this->setCollection($collection);
        
        return Mage_Adminhtml_Block_Widget_Grid::_prepareCollection();
    }
    
    protected function _prepareColumns()
    {
        parent::_prepareColumns();
        
        if (!Mage::app()->isSingleStoreMode()) {
            $this->removeColumn('store_id');
            $this->addColumnAfter(
                'store_id_column', array(
                'header'    => Mage::helper('sales')->__('Purchased From (Store)'),
                'index'     => 'store_id',
                'type'      => 'store',
                'store_view'=> true,
                'display_deleted' => true,
                ), 'real_order_id'
            );
        }

        $this->sortColumnsByOrder();
        
        $this->_exportTypes = array();
        
        return $this;
    }
    
    protected function _prepareMassaction()
    {
        return $this;
    }
    
    public function getGridUrl()
    {
        return $this->getUrl('*/pickupatstore_order/grid', array('_current' => true));
    }
}
