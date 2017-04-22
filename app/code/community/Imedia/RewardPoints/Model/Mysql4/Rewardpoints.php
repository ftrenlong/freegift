<?php
class Imedia_RewardPoints_Model_Mysql4_Rewardpoints extends Mage_Core_Model_Mysql4_Abstract
{
    public function _construct()
    {    
        $this->_init('imedia_rewardpoints/rewardpoints', 'id');
    }
}