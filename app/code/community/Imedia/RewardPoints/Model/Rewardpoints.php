<?php
class Imedia_RewardPoints_Model_Rewardpoints extends Mage_Core_Model_Abstract
{
    public function _construct()
    {
        parent::_construct();
        $this->_init('imedia_rewardpoints/rewardpoints');
    }
}