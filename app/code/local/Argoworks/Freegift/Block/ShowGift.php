<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of ShowGift
 *
 * @author hoile
 */

/**
 * return list products is free gift
 * 
 */
class Argoworks_Freegift_Block_ShowGift extends Mage_Core_Block_Template {

    public function getFreeGift() {
        $products = Mage::getModel('catalog/product')
                ->getCollection()
                ->addAttributeToSelect('*')
                ->addAttributeToFilter('free_gift_product', array('eq' => true));
        Mage::getSingleton('cataloginventory/stock')->addInStockFilterToCollection($products);  
        return $products;
    }

    public function checkRuleApply() {
        $saleRuleM = Mage::getModel('salesrule/rule');
        $catalogRuleM = Mage::getModel('catalogrule/rule');
        $catalogRuleResoureM = Mage::getResourceModel('catalogrule/rule');

        $quote = Mage::getSingleton('checkout/session')->getQuote();
// get rule of shopping cart price rule
        $ruleIDs = $quote->getAppliedRuleIds();
        $ruleItem = $saleRuleM->load($ruleIDs);
// check rule of shopping cart price rule
        if ($ruleItem->getFreeGiftApply()) {
            return true;
        }

        //get item on cart
        $itemss = $quote->getAllItems();
        foreach ($itemss as $item) {
// check shopping cart price rule for per items
            $ruleIDs = $item->getAppliedRuleIds();
            $ruleItem = $saleRuleM->load($ruleIDs);

            if ($ruleItem->getFreeGiftApply()) {
                return true;
            }
// check rule of catalog product for per items
            $productId = $item->getProductId();
            $customer = Mage::getSingleton('customer/session');
            $customerId = $customer->getCustomer()->getGroupId();
            $idWebsite = Mage::app()->getWebsite()->getId();
            $arrayRule = $catalogRuleResoureM->getRulesFromProduct(time(), $idWebsite, $customerId, $productId);
            foreach ($arrayRule as $temp) {
                $ruleID = $temp['rule_id'];
                $ruleItem = $catalogRuleM->load($ruleID);
                if ($ruleItem->getFreeGiftApply()) {
                    return true;
                }
            }
        }
        return FALSE;
    }
   
}
