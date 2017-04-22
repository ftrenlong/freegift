<?php
/**
 * MageGiant
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the MageGiant.com license that is
 * available through the world-wide-web at this URL:
 * http://magegiant.com/license-agreement/
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category    MageGiant
 * @package     MageGiant_GiantPoints
 * @copyright   Copyright (c) 2014 MageGiant (http://magegiant.com/)
 * @license     http://magegiant.com/license-agreement/
 */

/**
 * GiantPoints Observer Model
 *
 * @category    MageGiant
 * @package     MageGiant_GiantPoints
 * @author      MageGiant Developer
 */
class Magegiant_GiantPointsRefer_Model_Observer extends Magegiant_GiantPoints_Model_Observer
{

    public function couponPostAction($observer)
    {
        if (!$this->_isEnabledModule()) {
            return $this;
        }
        $action      = $observer->getEvent()->getControllerAction();
        $couponCode  = Mage::app()->getRequest()->getParam('coupon_code');
        $customer_id = Mage::helper('giantpoints/crypt')->decrypt($couponCode);
        $customer    = Mage::getModel('customer/customer')->load($customer_id);
        if (!$couponCode || !$customer || !$customer->getId()) {
            return $this;
        }
        /*Check code used by themselves*/
        $current_customer = Mage::helper('giantpoints/customer')->getCustomer();
        if ($current_customer && $current_customer->getId() == $customer_id) {
            return $this;
        }
        /*Set referral cookie*/
        $cookie     = Mage::getModel('giantpoints/cookie');
        $cookie_key = Magegiant_GiantPoints_Model_Cookie::COOKIE_GIANTPOINT_REFERRAL;
        if (!$cookie->getCookie($cookie_key)) {
            $cookie->setCookie($cookie_key, $couponCode);
        }
        Mage::getSingleton('checkout/session')->getMessages(true);
        Mage::getSingleton('checkout/session')->addSuccess(Mage::helper('giantpoints')->__('Referral code "%s" was applied.', $couponCode));
        $action->setFlag('', Mage_Core_Controller_Varien_Action::FLAG_NO_DISPATCH, true);
        $action->getResponse()->setRedirect(Mage::getUrl('checkout/cart'));
    }

    public function giantpointsReferrerEarningBefore($observer)
    {
        $event = $observer->getEvent();
        if (!$event)
            return $this;
        $referProgram = $event->getReferral();
        $quote        = Mage::getSingleton('checkout/session')->getQuote();
        $customer     = Mage::getModel('customer/customer')->load($referProgram->getReferralId());
        $rules        = Mage::helper('giantpointsrefer')->getReferSalesRule($quote, $customer);
        $totalEarn    = 0;
        if (count($rules)) {
            foreach ($rules as $rule) {
                $totalEarn += Mage::helper('giantpointsrefer')->calcPointsForReferRule($rule, $quote);
            }
            $referProgram->setReferralEarn($totalEarn);
        }
        $referralsRules     = Mage::helper('giantpointsrefer')->getReferSalesRule($quote, $this->getCustomer());
        $totalReferralsEarn = 0;
        if (count($referralsRules)) {
            foreach ($referralsRules as $rule) {
                $totalReferralsEarn += Mage::helper('giantpointsrefer')->calcPointsForReferRule($rule, $quote, 'referrals_');
            }
            $referProgram->setInviteeEarn($totalReferralsEarn);
        }

        return $this;

    }

    public function getCustomer()
    {
        return Mage::helper('giantpoints/customer')->getCustomer();
    }

    public function salesOrderSaveAfter($observer)
    {
        $order = $observer['order'];
        if ($order->getCustomerIsGuest() || !$order->getCustomerId()) {
            return $this;
        }
        //Clear Referrer Cookie
        $cookie     = Mage::getModel('giantpoints/cookie');
        $cookie_key = Magegiant_GiantPoints_Model_Cookie::COOKIE_GIANTPOINT_REFERRAL;
        $cookie->setCookie($cookie_key, '');

        return $this;
    }


    /**
     * process credit memo save after
     *
     * @param $observer
     * @return $this
     */
    public function salesOrderCreditmemoSaveAfter($observer)
    {
        $creditmemo = $observer['creditmemo'];
        $order      = $creditmemo->getOrder();

        // Deduce rate earned points
        if (Mage::helper('giantpoints/config')->allowCancelEarnedPoint($order->getStoreId())) {
            if ($creditmemo->getRefundInviteeEarnedPoints() > 0) {
                $this->_refundInviteeEarnedPoints($creditmemo);
            }

            if ($creditmemo->getRefundReferralEarnedPoints() > 0) {
                $this->_refundReferralEarnedPoints($creditmemo);
            }
        }


        return $this;
    }

    protected function _refundReferralEarnedPoints($creditmemo)
    {
        $order = $creditmemo->getOrder();
        if ($order->getReferralEarn() <= 0 || $creditmemo->getRefundReferralEarnedPoints() <= 0) {
            return $this;
        }
        $customer = Mage::getModel('customer/customer')->load($order->getReferralId());
        if (!$customer->getId()) {
            return $this;
        }
        $additionalData = array(
            'customer'      => $customer,
            'action_object' => $creditmemo,
            'notice'        => null,


        );
        Mage::helper('giantpoints/action')->createTransaction(
            'referral_refunded', $additionalData
        );
    }


    protected function _refundInviteeEarnedPoints($creditmemo)
    {
        $order = $creditmemo->getOrder();
        if ($order->getInviteeEarn() <= 0 || $creditmemo->getRefundInviteeEarnedPoints() <= 0) {
            return $this;
        }
        $customer = Mage::getModel('customer/customer')->load($order->getCustomerId());
        if (!$customer->getId()) {
            return $this;
        }
        $additionalData = array(
            'customer'      => $customer,
            'action_object' => $creditmemo,
            'notice'        => null,


        );
        try {
            Mage::helper('giantpoints/action')->createTransaction(
                'invitee_refunded', $additionalData
            );
        } catch (Exception $e) {
            Mage::helper('giantpoints')->log('Exception: ' . $e->getMessage() . ' in ' . __CLASS__ . ' on line ' . __LINE__);
        }
    }

    /**
     * Process invoice after save
     *
     * @param type $observer
     * @return Magegiant_GiantPoints_Model_Observer
     */
    public function salesOrderInvoiceSaveAfter($observer)
    {
        $invoice = $observer['invoice'];
        $this->_earnPointsForReferral($invoice);
        $this->_earnPointsForInvitee($invoice);

        return $this;
    }

    protected function _earnPointsForReferral($invoice)
    {
        $order = $invoice->getOrder();
        if ($invoice->getState() != Mage_Sales_Model_Order_Invoice::STATE_PAID
            || !$order->getReferralId() || !$invoice->getBaseGrandTotal()
			|| !$invoice->getReferralEarn()
        ) {
            return $this;
        }
        $customer = Mage::getModel('customer/customer')->load($order->getReferralId());
        if (!$customer->getId()) {
            return $this;
        }
        /*update invitation*/
        $customer_email = $order->getCustomerEmail();
        $helper         = Mage::helper('giantpointsrefer');
//        if (!$helper->checkFirstOrder($customer_email, $order->getStoreId())) {
//            return $this;
//        }
        $invitation = Mage::getModel('giantpointsrefer/invitation');
        $store_id   = $order->getStoreId();
        $invitee    = $invitation->loadByEmailAndStore($customer_email, $store_id);
        $data       = new Varien_Object();
        if (!$invitee || !$invitee->getId()) {
            $data->setData(
                array(
                    'email'       => $customer_email,
                    'message'     => Mage::helper('giantpoints')->__('Order invoiced'),
                    'store_id'    => $store_id,
                    'referral_id' => $order->getReferralId()
                )
            );
            $invitation->saveInvitationAccepted($data);
        } else {
            $invitee->setStatus(Magegiant_GiantPointsRefer_Model_Invitation::INVITATION_ACCEPTED)
                ->setReferralId($order->getReferralId())
                ->save();
        }
        /*end invitation*/
        $additionalData = array(
            'customer'      => $customer,
            'action_object' => $invoice,
            'notice'        => null,

        );
        Mage::helper('giantpoints/action')->createTransaction(
            'referral_invoiced', $additionalData
        );
    }

    protected function _earnPointsForInvitee($invoice)
    {
        $order = $invoice->getOrder();
        if ($invoice->getState() != Mage_Sales_Model_Order_Invoice::STATE_PAID
            || !$order->getInviteeEarn() || !$order->getReferralId()
        ) {
            return $this;
        }
        $customer = Mage::getModel('customer/customer')->load($order->getCustomerId());
        if (!$customer->getId()) {
            return $this;
        }
        $additionalData = array(
            'customer'      => $customer,
            'action_object' => $invoice,
            'notice'        => null,


        );
        Mage::helper('giantpoints/action')->createTransaction(
            'invitee_invoiced', $additionalData
        );
    }

    public function postdispatchCustomerAccountCreatePost($observer)
    {
        if (!$this->_isEnabledModule()) {
            return $this;
        }
        $customer = Mage::registry('giantpoints_current_customer');
        if (!$customer || !$customer->getId()) {
            return $this;
        }
        self::$_customerNotSet  = false;
        $controller             = $observer->getControllerAction();
        $referral_code_or_email = $controller->getRequest()->getParam('giantpoints_referral', '');
		if(!$referral_code_or_email){
			$referral_code_or_email = Mage::getSingleton('core/cookie')->get('giantpoints_referral');
		}
        $rewardAccount          = Mage::getModel('giantpoints/customer')->getAccountByCustomer($customer);
        if (!$rewardAccount || !$rewardAccount->getId()) {
            $this->_createRewardCustomer($customer);
        }
        $this->_pointForCustomerReferral($referral_code_or_email, $customer);

    }

	public function quoteSubmitSuccess($observer)
	{
		if (!$this->_isEnabledModule()) {
			return $this;
		}
		$customer = Mage::registry('giantpoints_current_customer');
		if (!$customer || !$customer->getId()) {
			return $this;
		}
		self::$_customerNotSet  = false;
		$order = $observer->getEvent()->getOrder();

		if(!$order->getReferralId()){
			return $this;
		}
		$referral = Mage::getModel('customer/customer')->load($order->getReferralId());
		if(!$referral->getId()){
			return $this;
		}
		$rewardAccount          = Mage::getModel('giantpoints/customer')->getAccountByCustomer($customer);
		if (!$rewardAccount || !$rewardAccount->getId()) {
			$this->_createRewardCustomer($customer);
		}
		$this->_pointForCustomerReferral($referral->getEmail(), $customer);

	}

    public function customerRegisterSuccess($observer)
    {
        if (!$this->_isEnabledModule()) {
            return $this;
        }
        $customer = $observer->getCustomer();
        if (!$customer || !$customer->getId()) {
            return $this;
        }
        $controller             = $observer->getControllerAction();
        $referral_code_or_email = $controller->getRequest()->getParam('giantpoints_referral', '');
        $rewardAccount          = Mage::getModel('giantpoints/customer')->getAccountByCustomer($customer);
        if (!$rewardAccount || !$rewardAccount->getId()) {
            $this->_createRewardCustomer($customer);
        }
        $this->_pointForCustomerReferral($referral_code_or_email, $customer);
    }

    protected function _pointForCustomerReferral($code_or_email, $new_customer)
    {

        if (Mage::helper('giantpoints/validation')->isValidEmail($code_or_email)) {
            if ($code_or_email == $new_customer->getEmail()) {
                return $this;
            }
            $affiliate_customer = Mage::getModel('customer/customer')->setWebsiteId(Mage::app()->getStore()->getWebsiteId())->loadByEmail($code_or_email);
        } else {
            $affiliate_customer = Mage::helper('giantpoints/crypt')->getCustomerByCode($code_or_email);
        }
        if (!$affiliate_customer || !$affiliate_customer->getId())
            return $this;
        /*Update Invitation history*/
        $invitation = Mage::getModel('giantpointsrefer/invitation');
        $store_id   = $new_customer->getStoreId();
        $invitee    = $invitation->loadByEmailAndStore($new_customer->getEmail(), $store_id);
        $data       = new Varien_Object();
        if (!$invitee || !$invitee->getId()) {
            $data->setData(
                array(
                    'email'       => $new_customer->getEmail(),
                    'message'     => Mage::helper('giantpoints')->__('Signed Up'),
                    'store_id'    => $store_id,
                    'referral_id' => $affiliate_customer->getId()
                )
            );
            $invitation->saveInvitationAccepted($data);
        }
        /*Add points for referrals */
        $rule = Mage::getModel('giantpoints/rule')
            ->getRuleByCondition($affiliate_customer, Magegiant_GiantPointsRefer_Model_Rule_Action_Referrals_Signup::ACTION_CODE);
        if ($rule && $rule->getId()) {
            $pointForReferral = $rule->getPointAmount();
        } else {
            $pointForReferral = Mage::helper('giantpointsrefer/config')->getPointForReferralSignup($new_customer->getStoreId());
        }

        if (!$pointForReferral)
            return $this;
        $rewardAccount = Mage::getModel('giantpoints/customer')->getAccountByCustomer($affiliate_customer);
        if (!$rewardAccount || !$rewardAccount->getId()) {
            $this->_createRewardCustomer($affiliate_customer);
        }
        $obj            = new Varien_Object(array(
            'point_amount' => $pointForReferral,
        ));
        $additionalData = array(
            'customer'      => $affiliate_customer,
            'action_object' => $obj,
            'notice'        => null,
        );
        Mage::helper('giantpoints/action')->createTransaction('customer_referral_signup', $additionalData);
        /*Set Referral code to cookie*/
        $referral_code = Mage::helper('giantpoints/crypt')->encrypt($affiliate_customer->getId());
        $cookie        = Mage::getModel('giantpoints/cookie');
        $cookie_key    = Magegiant_GiantPoints_Model_Cookie::COOKIE_GIANTPOINT_REFERRAL;
        $cookie->setCookie($cookie_key, $referral_code);

        return $this;
    }

    /**
     * Check module is enabled
     */
    protected function _isEnabledModule($storeId = null)
    {
        return (bool)Mage::helper('giantpointsrefer/config')->isEnabled($storeId);
    }
}