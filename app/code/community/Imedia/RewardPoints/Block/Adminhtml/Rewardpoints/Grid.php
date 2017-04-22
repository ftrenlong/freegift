<?php
/**
 * Manage featured product grid block
 * 
 */
class Imedia_RewardPoints_Block_Adminhtml_Rewardpoints_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
    public function __construct()
    {
        parent::__construct();
        $this->setId('rewardpointsGrid');
        $this->setDefaultSort('entity_id');
        $this->setDefaultDir('DESC');
        $this->setSaveParametersInSession(true);
        $this->setUseAjax(true);
        $this->setVarNameFilter('product_filter');

    }
    
    protected function _getStore()
    {
        $storeId = (int) $this->getRequest()->getParam('store', 0);
        return Mage::app()->getStore($storeId);
    }
    
    /**
     * Prepare rewardpoints grid collection object
     *
     * @return Imedia_RewardPoints_Block_Adminhtml_Rewardpoints_Grid
     */
    protected function _prepareCollection()
    {
       $store = $this->_getStore();
        $collection = Mage::getModel('customer/customer')->getCollection()
            ->addAttributeToSelect('email')
            ->addAttributeToSelect('firstname')
			->addAttributeToSelect('lastname')
            ->addAttributeToSelect('entity_id');
            //->addAttributeToSelect('type_id');

		$collection->joinField('points_current',
			'rewardpoints_account',
			'points_current',
			'customer_id=entity_id'
			);


        $this->setCollection($collection);

        parent::_prepareCollection();
        //$this->getCollection()->addWebsiteNamesToResult();
        return $this;
    }
    
    /**
     * Prepare default grid column
     *
     * @return Imedia_RewardPoints_Block_Adminhtml_Rewardpoints_Grid
     */
    protected function _prepareColumns()
    {
	
	    $this->addColumn('entity_id',
            array(
                'header'=> Mage::helper('imedia_rewardpoints')->__('ID'),
                'width' => '50px',
                'type'  => 'number',
                'index' => 'entity_id',
        ));
		      
        $this->addColumn('firstname',
            array(
                'header'=> Mage::helper('imedia_rewardpoints')->__('Firstname'),
                'index' => 'firstname',
        ));

		$this->addColumn('lastname',
            array(
                'header'=> Mage::helper('imedia_rewardpoints')->__('Lastname'),
                'index' => 'lastname',
        ));

        $this->addColumn('email',
                array(
                    'header'=> Mage::helper('imedia_rewardpoints')->__('Email'),
                    'index' => 'email',
            )); 
		
		$this->addColumn('points_current',
                array(
                    'header'=> Mage::helper('imedia_rewardpoints')->__('Rewards Points'),
                    'index' => 'points_current',
		));
		$this->addExportType('*/*/exportCsv',Mage::helper('imedia_rewardpoints')->__('CSV'));
		
		return parent::_prepareColumns();
    }

    public function getGridUrl()
    {
        return $this->getUrl('*/*/grid', array('_current'=>true));
    }

}

