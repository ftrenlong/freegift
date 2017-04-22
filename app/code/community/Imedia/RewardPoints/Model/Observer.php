<?php
/**
 * Reward points Observer
 * 
 */

class Imedia_RewardPoints_Model_Observer  extends  Mage_Core_Model_Abstract {
  
  public function rewardpoints($observer)
    {
       if(Mage::getStoreConfig('rewardpoints/rewardpoints/enabled')&& 
	   Mage::getStoreConfig('rewardpoints/display/signup'))
	   {    
	        //Mage::log('testfornewsletter:'.Mage::getStoreConfig('rewardpoints/display/signup_points'));
			$customer = $observer->getCustomer();
			$customerId = $customer->getEntityId();
			$rewardPoints = 0;
			//$customerId = Mage::getSingleton('checkout/session')->getQuote()->getShippingAddress()->getCustomerId();
			//Mage::log('customer id for register:'.$customerId);
			$rewardPoints = Mage::getStoreConfig('rewardpoints/display/signup_points');
			$this->recordPoints($rewardPoints, $customerId);
		}

    }
	
	
   public function subscribedToNewsletter(Varien_Event_Observer $observer)
      {
	  
       if(Mage::getStoreConfig('rewardpoints/rewardpoints/enabled') && 
	   Mage::getStoreConfig('rewardpoints/display/newsletter_signup'))
	   { 
	        //Mage::log('testfornewsletter:'.Mage::getStoreConfig('rewardpoints/display/newsletter_signup_points'));
			$event = $observer->getEvent();
			$subscriber = $event->getDataObject();
			$data = $subscriber->getData();
			$email = $data['subscriber_email'];
			$rewardPoints = 0;
			//Mage::log('email get subs:'.$email);
			$customer = Mage::getModel('customer/customer');
			$customer->setWebsiteId(Mage::app()->getWebsite()->getId());
			$customer->loadByEmail($email); //load customer by email id
			$customerid = $customer->getId();
            $subscribed = Mage::getModel('newsletter/subscriber')->loadByEmail($email);
			if($subscribed->getId())
				{
				  $alreadysubscribed = $subscribed->getStatus();
				}
			else{$alreadysubscribed = 5;}	
				//Mage::log('customer subscriptin status:'.$alreadysubscribed);			

				$statusChange = $subscriber->getIsStatusChanged();
			if($customerid && $alreadysubscribed==5){
			if ($data['subscriber_status'] == "1" && $statusChange == true) {
				//Mage::log('customer id for newsletter subscription:'.$customerid);
				$rewardPoints = Mage::getStoreConfig('rewardpoints/display/newsletter_signup_points');
				$this->recordPoints($rewardPoints, $customerid);
				
				 }
			  }
           }			
        }
		
		
/*		
public function invoiceSaveAfter(Varien_Event_Observer $observer)
	{
		$invoice = $observer->getEvent()->getInvoice();
		if ($invoice->getBaseFeeAmount()) {
			$order = $invoice->getOrder();
			$order->setFeeAmountInvoiced($order->getFeeAmountInvoiced() - $invoice->getFeeAmount());
			$order->setBaseFeeAmountInvoiced($order->getBaseFeeAmountInvoiced() - $invoice->getBaseFeeAmount());
		}
		return $this;
	}
public function creditmemoSaveAfter(Varien_Event_Observer $observer)
	{
		/* @var $creditmemo Mage_Sales_Model_Order_Creditmemo */
		/*$creditmemo = $observer->getEvent()->getCreditmemo();
		if ($creditmemo->getFeeAmount()) {
			$order = $creditmemo->getOrder();
			$order->setFeeAmountRefunded($order->getFeeAmountRefunded() - $creditmemo->getFeeAmount());
			$order->setBaseFeeAmountRefunded($order->getBaseFeeAmountRefunded() - $creditmemo->getBaseFeeAmount());
		}
		return $this;
	}
	
	/**
		* Record the points for each product.
		*
		* @triggeredby: sales_order_place_after
		* @param $eventArgs array "order"=>$order
		*/
		public function recordPointsForOrderEvent($observer) {
		
	if(Mage::getStoreConfig('rewardpoints/rewardpoints/enabled')&& 
	   Mage::getStoreConfig('rewardpoints/display/product_rewards'))
	   { 
			$order = $observer->getEvent()->getOrder();
			$items =$order->getItemsCollection();
			//load all products for each sales item
			//sum up points per product per quantity
			//record points for item into db
			//grab the customerId
			$customer_id_from_event = $order->getCustomerId();
			//Mage::log('customer id from order event:'.$customer_id_from_event);
			$customerId = Mage::getSingleton('checkout/session')->getQuote()->getShippingAddress()->getCustomerId();
			//load all products for each sales item
			$rewardPoints = 0;
			$prodIds = array();
			foreach ($items as $_item) {
			$prodIds[] = $_item->getProductId();
			}
			//load products from quote IDs to get the points
			//(this won’t work if points were set dynamically
			// in the addToCart process)
			$prod = Mage::getResourceModel('catalog/product_collection')->addAttributeToSelect('reward_points')->addIdFilter($prodIds);
			//sum up points per product per quantity
			foreach ($items as $_item) {
			$rewardPoints += $prod->getItemById($_item->getProductId())->getRewardPoints() * $_item->getQtyOrdered();
			}
			//record points for item into db
			$this->recordPoints($rewardPoints, $customerId);
			
		    $discounted = Mage::getSingleton('customer/session')->getDisc();
		//subtract points for this order
		   $this->useCouponPoints($discounted, $customerId);
		   
			}
			 Mage::getSingleton('customer/session')->setdisc(0);
		}
		
		public function recordPoints($pointsInt, $customerId) {
			$points = Mage::getModel('rewardpoints/account')->load($customerId);
			$points->addPoints($pointsInt, $customerId);
			$points->save();
			
		}
		
		//this completely new method should be placed outside
		// recordPointsForOrderEvent(), but inside the class
		public function useCouponPoints($discounted, $customerId) {
			$pointsAmt = $discounted;
			$points = Mage::getModel('rewardpoints/account')->load($customerId);
			$points->subtractPoints($pointsAmt, $customerId);
			$points->save();
		}
  
  
  
}