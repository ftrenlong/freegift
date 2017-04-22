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
 * Giantpoints Model
 *
 * @category    MageGiant
 * @package     MageGiant_GiantPoints
 * @author      MageGiant Developer
 */
class Magegiant_GiantPointsRefer_Model_Invitation extends Mage_Core_Model_Abstract
{
    const INVITATION_NEW      = 0;
    const INVITATION_SENT     = 1;
    const INVITATION_ACCEPTED = 2;

    const INVITATION_ACCEPTED_FROM_OTHER = 4;
    const INVITEE_IS_CUSTOMER_FROM_OTHER = 5;

    const INVITEE_EXIST       = 10;
    const REGISTERED_CUSTOMER = 20;

    const XML_PATH_EMAIL_TEMPLATE = 'giantpoints/referral_system_configuration/email_template';
    const XML_PATH_EMAIL_IDENTITY = 'giantpoints/email/sender';
    protected $_eventPrefix = 'giantpoints_referral';
    protected $_eventObject = 'referral';

    public function _construct()
    {
        parent::_construct();
        $this->_init('giantpointsrefer/invitation');
    }

    /**
     * Send email to invitee
     *
     * @param string                       $emailAddress
     * @param Mage_Core_Model_Store        $store
     * @param Mage_Customer_Model_Customer $customer
     *
     * @return boolean
     */
    public function sendEmail($emailAddress, $store, $customer)
    {
        $translate = Mage::getSingleton('core/translate');
        $translate->setTranslateInline(false);
        $mail = Mage::getModel('core/email_template');
        try {
            $mail
                ->setDesignConfig(array('area' => 'frontend', 'store' => $store->getStoreId()))
                ->sendTransactional(
                    $store->getConfig(self::XML_PATH_EMAIL_TEMPLATE),
                    $store->getConfig(self::XML_PATH_EMAIL_IDENTITY),
                    $this->getEmail(),
                    null,
                    array(
                        'url'      => $this->prepareUrl($customer, $store),
                        'message'  => $this->getMessage(),
                        'store'    => $store,
                        'customer' => $customer,
                    )
                );
            if ($mail->getSentSuccess()) {
                $this->setStatus(self::INVITATION_SENT)->setUpdateDate(true)->save();
                $translate->setTranslateInline(true);

                return true;
            }
        } catch (Exception $e) {
            Mage::helper('giantpoints')->log('Exception: ' . $e->getMessage() . ' in ' . __CLASS__ . ' on line ' . __LINE__);
        }

        return false;
    }

    /**
     * Saves new invitation data in DB and returns instance
     *
     * @param Mage_Customer_Model_Session $sessionData
     *
     */
    public function saveNewInvitation($sessionData)
    {
        $this->setData($sessionData->getData());
        $this->addData(
            array(
                'status' => self::INVITATION_NEW,
                'date'   => $this->getResource()->formatDate(time()),
            )
        );
        $this->save();

        return $this;
    }

    public function saveInvitationAccepted($sessionData)
    {
        $this->setData($sessionData->getData());
        $this->addData(
            array(
                'status' => self::INVITATION_ACCEPTED,
                'date'   => $this->getResource()->formatDate(time()),
            )
        );
        $this->save();

        return $this;
    }

    /**
     * Get invitation accepted by customer
     *
     * @param string $customerEmail
     *
     */
    public function loadAcceptedByEmail($customerEmail)
    {
        $this->getResource()->loadByEmailAndStatus($this, $customerEmail, self::INVITATION_ACCEPTED);

        return $this;
    }

    /**
     * Prepare url to insert into invitation email
     *
     * @param Mage_Customer_Model_Customer $customer
     * @param string                       $emailAddress
     * @param Mage_Core_Model_Store        $store
     *
     * @return string
     */
    public function prepareUrl($customer, $store)
    {
        $code        = Mage::helper('giantpoints/crypt')->encrypt($customer->getId());
        $preparedUrl = Mage::getModel('core/url')
            ->setStore($store->getStoreId())
            ->getUrl('giantpointsrefer/invitation/promotion', array('cod' => $code));

        return $preparedUrl;
    }

    /**
     * Load invitation data from resource model by email
     *
     * @param string $subscriberEmail
     *
     */
    public function loadByEmail($subscriberEmail)
    {
        $this->getResource()->loadByEmail($this, $subscriberEmail);

        return $this;
    }

    /**
     * Load invitation data from resource model by email
     *
     * @param string $subscriberEmail
     * @param int    $storeId
     *
     */
    public function loadByEmailAndStore($subscriberEmail, $storeId)
    {
        $this->getResource()->loadByEmailAndStore($this, $subscriberEmail, $storeId);

        return $this;
    }

    /**
     * Load invitation data from resource model by referral id
     *
     * @param int $referralId
     *
     */
    public function loadByReferralId($referralId)
    {
        $this->getResource()->loadByReferralId($this, $referralId);

        return $this;
    }

    /**
     * get transaction status as hash array
     *
     * @return array
     */
    public function getStatusHash()
    {
        return array(
            self::INVITATION_NEW      => Mage::helper('giantpoints')->__('Email wasn\'t sent. Please try later. '),
            self::INVITATION_SENT     => Mage::helper('giantpoints')->__('Invitation sent'),
            self::INVITATION_ACCEPTED => Mage::helper('giantpoints')->__('Invitation accepted'),
        );
    }

    public function getStatusOption()
    {
        $options = array();
        foreach ($this->getStatusHash() as $value => $label) {
            $options[] = array(
                'value' => $value,
                'label' => $label,
            );
        }

        return $options;
    }
}