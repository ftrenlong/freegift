<?php
/**
 * Grid container block
 * 
 */
class Imedia_RewardPoints_Block_Adminhtml_Rewardpoints extends Mage_Adminhtml_Block_Widget_Grid_Container
{       
  public function __construct()
  {
    /*both these variables tell magento the location of our Grid.php(grid block) file.
     * $this->_blockGroup.'/' . $this->_controller . '_grid'
     * i.e  imedia_rewardpoints/adminhtml_rewardpoints_grid
     * $_blockGroup - is your module's name.
     * $_controller - is the path to your grid block. 
     */
    $this->_controller = 'adminhtml_rewardpoints';
    $this->_blockGroup = 'imedia_rewardpoints';
    $this->_headerText = Mage::helper('imedia_rewardpoints')->__('Manage Reward Points');
    
    parent::__construct();
    
    $this->_removeButton('add');
  }
}