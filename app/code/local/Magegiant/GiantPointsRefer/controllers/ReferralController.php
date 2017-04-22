<?php
/**
 * Magegiant
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the magegiant.com license that is
 * available through the world-wide-web at this URL:
 * http://magegiant.com/license-agreement/
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category    Magegiant
 * @package     Magegiant_GiantPoints
 * @copyright   Copyright (c) 2014 Magegiant (http://magegiant.com/)
 * @license     http://magegiant.com/license-agreement/
 */

/**
 * GiantPoints Index Controller
 *
 * @category    Magegiant
 * @package     Magegiant_GiantPoints
 * @author      Magegiant Developer
 */
class Magegiant_GiantPointsRefer_ReferralController extends Mage_Core_Controller_Front_Action
{
    /**
     * Check customer authentication
     */
    public function preDispatch()
    {
        parent::preDispatch();
        $action = $this->getRequest()->getActionName();

        if (
            Mage::helper('giantpointsrefer/config')->isEnabled()
            && Mage::helper('giantpoints/config')->isEnabled()
        ) {

            if ($action != 'createAccount' && $action !== 'new') {
                $loginUrl = Mage::helper('customer')->getLoginUrl();
                if (!Mage::getSingleton('customer/session')->authenticate($this, $loginUrl)) {
                    $this->setFlag('', self::FLAG_NO_DISPATCH, true);
                }
            }
        } else {
            $this->_redirect('customer/account/');
        }
    }

    public function indexAction()
    {
        $this->loadLayout();
        $this->getLayout()->getBlock('head')->setTitle($this->__('Reward Points Referrals'));
        $this->renderLayout();
    }

    public function sendInvitationAction()
    {
        $postData = $this->getRequest()->getPost();
        if ($postData) {
            $customer    = Mage::getSingleton('customer/session')->getCustomer();
            $store       = Mage::app()->getStore();
            $sessionData = $this->_getWrapped($postData, $customer, $store);
            $emails      = $sessionData->getEmails();
            $sessionData->unsetData('emails');
            foreach ($emails as $email) {
                $sessionData->setData('email', $email);
                try {
                    $preparedInvitation = Mage::getModel('giantpointsrefer/invitation')->loadAcceptedByEmail($email);
                    /* invitation exists */
                    if ($preparedInvitation->getId()) {
                        Mage::getSingleton('core/session')->addError(
                            Mage::helper('giantpoints')->__('Invitation for email: %s has already been accepted', $email)
                        );
                        continue;
                    }
                    /* save new invitation */
                    $preparedInvitation->saveNewInvitation($sessionData);
                    /* send invitation email */
                    $sentSuccessfully = $preparedInvitation->sendEmail($email, $store, $customer);
                    if ($sentSuccessfully) {
                        Mage::getSingleton('core/session')->addSuccess(
                            Mage::helper('giantpoints')->__('Invitation for %s has been sent.', $email)
                        );
                    } else {
                        Mage::getSingleton('customer/session')->addNotice(
                            Mage::helper('giantpoints')->__('Invitation for %s is not sent. Please try later.', $email)
                        );
                    }
                } catch (Exception $e) {
                    Mage::helper('giantpoints')->log($e->getMessage());
                }
            }

            return $this->_redirect('giantpointsrefer/referral/');
        }
        $this->loadLayout();
        $this->_initPage();
        $this->getLayout()->getBlock('head')->setTitle($this->__('Send Invitations'));
        $this->renderLayout();
    }

    private function _initPage()
    {
        $this->_initLayoutMessages('core/session');
        $navigationBlock = $this->getLayout()->getBlock('customer_account_navigation');
        if ($navigationBlock) {
            $navigationBlock->setActive('giantpoints/referral');
        }
    }

    /**
     * Wraps post, customer, store data into Varien_Object
     *
     * @param array                        $postData
     * @param Mage_Customer_Model_Customer $customer
     * @param Mage_Core_Model_Store        $store
     *
     * @return Varien_Object
     */
    private function _getWrapped($postData, $customer, $store)
    {
        $objToReturn = new Varien_Object;

        $storeId = $store->getStoreId();
        $message = isset($postData['message']) ? htmlspecialchars($postData['message']) : '';

        $emailAddresses = explode(',', $postData['contacts']);
        foreach ($emailAddresses as $key => $emailAddress) {
            $emailAddress = trim($emailAddress);
            if (empty($emailAddress) || !Zend_Validate::is($emailAddress, 'EmailAddress')) {
                unset($emailAddresses[$key]);
            }
        }

        $objToReturn->setData(
            array(
                'emails'      => $emailAddresses,
                'referral_id' => $customer->getEntityId(),
                'message'     => $message,
                'store_id'    => $storeId
            )
        );

        return $objToReturn;
    }

    public function invitationAction()
    {

        $this->loadLayout();
        $this->getLayout()->getBlock('head')->setTitle($this->__('My Invitation'));
        $this->renderLayout();
    }

    public function gmailAction() {
        $code = $this->getRequest()->getParam('code');
        if (!$code) {
			$client_id = Mage::getStoreConfig('giantpoints/referral_system_configuration/google_consumer_key');
            $redirect_url = Mage::getUrl("*/*/gmail", array('_nosid' => true,'_secure'=>false));
            $url = 'https://accounts.google.com/o/oauth2/auth?client_id='.$client_id.'&redirect_uri=' . $redirect_url . '&scope='.urlencode('https://www.googleapis.com/auth/contacts.readonly').'&response_type=code';
            $this->_redirectUrl($url);

			return;
        }
        $this->loadLayout();
        $this->renderLayout();
    }

    public function yahooAction() {
        $yahoo = Mage::getSingleton('giantpointsrefer/refer_yahoo');
        if (!$yahoo->hasSession() || !$this->getRequest()->getParam('oauth_token') || !$this->getRequest()->getParam('oauth_verifier'))
            return $this->_redirectUrl($yahoo->getAuthUrl());

        $this->loadLayout();
        $this->renderLayout();
    }

	public function liveAction() {
		$yahoo = Mage::getSingleton('giantpointsrefer/refer_yahoo');
		if (!$yahoo->hasSession() || !$this->getRequest()->getParam('oauth_token') || !$this->getRequest()->getParam('oauth_verifier'))
			return $this->_redirectUrl($yahoo->getAuthUrl());

		$this->loadLayout();
		$this->renderLayout();
	}

}