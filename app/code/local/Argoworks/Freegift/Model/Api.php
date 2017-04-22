<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Api
 *
 * @author hoile
 */
class Argoworks_Freegift_Model_Api extends Mage_Api_Model_Resource_Abstract {

    public function updatePointForCustomer($data = array()) {
        try {           
            $id = $data['customer_id'];
            $points = $data['points'];
            $lifetimePoints = $data['lifetimePoints'];
            $pointSpent = $lifetimePoints - $points;
            $objectCustomerPoints = Mage::getModel('giantpoints/customer')->load($id, 'customer_id');
            $reward_id = $objectCustomerPoints->getRewardId();
            if($reward_id){
                 Mage::getModel('giantpoints/customer')->setPointBalance($points)->setPointSpent($pointSpent)->save();
            }else{
                Mage::getModel('giantpoints/customer')->setCustomerId($id)->setPointBalance($points)->setPointSpent($pointSpent)->save();
            }
            return 1;
        } catch (Exception $exc) {
            echo $exc->getTraceAsString();
            return 0;
        }
    }

}
