<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Observe
 *
 * @author hoile
 */
class Argoworks_Freegift_Model_Observer {

    public function savePaymentFreeGift() {
        $params = Mage::app()->getRequest()->getParams();
        foreach ($params['giftId'] as $giftId) {
            try {
                $arrayGift[] = $giftId;
            } catch (Exception $e) {
                echo $e->getMessage();
            }
        }        
        Mage::getSingleton('core/session')->setSessionGift($arrayGift);
    }

    public function hookBeforeSalesOrder($observer) {
        try {
           $addGift = Mage::getSingleton('core/session')->getSessionGift();
           Mage::getSingleton('core/session')->unsSessionGift();
            $order = $observer->getEvent()->getOrder();
            $quote = $order->getQuote();
            $pointAmount = 0;
            foreach ($addGift as $giftId) {
                $productCollection = Mage::getModel('catalog/product');
                $product = $productCollection->load($giftId);
                $quoteItem = Mage::getModel('sales/quote_item')->setProduct($product)->setQuote($quote)->setQty(1);
                $quoteItem->save();
                Mage::getModel('sales/convert_quote')->itemToOrderItem($quoteItem)->setOrderID($order->getId())->save();
                $pointAmount -= $product->getPoints();
            }
            if($pointAmount){              
                $additionalData = array(
                    'customer'      => $quote->getCustomer(),
                    'action_object' => $order,
                    'notice'        => null,
                );
                Mage::helper('giantpoints/action')->createTransaction('spending_order',
                    $additionalData, $pointAmount  );
            }            
        } catch (Exception $ex) {
            echo $ex->getMessage();
        }
    }

}
