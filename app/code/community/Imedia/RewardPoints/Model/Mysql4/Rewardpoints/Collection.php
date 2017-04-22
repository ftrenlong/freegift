<?php
class Imedia_RewardPoints_Model_Mysql4_Rewardpoints_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract
{
    public function _construct()
    {
        parent::_construct();
        $this->_init('imedia_rewardpoints/rewardpoints');
    }
}