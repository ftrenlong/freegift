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
class Magegiant_GiantPointsBehavior_Model_Cron
{
    const STOP_MAIL = 'giantpoints_stop_email';

    /**
     * cron job add point when customer birthday is true
     */
    public function addBirthdayTransactions()
    {
        $helper     = Mage::helper('giantpoints/config');
        $collection = Mage::getModel('customer/customer')->getCollection()
            ->addAttributeToFilter('dob', array('notnull' => 'dob'));

        $collection->getSelect()
            ->joinLeft(
                array(
                    'account' => $collection->getTable('giantpoints/customer')
                ),
                'account.customer_id = e.entity_id'
            )
            ->having('EXTRACT(DAY FROM `dob`) = EXTRACT(DAY FROM UTC_TIMESTAMP())')
            ->having('EXTRACT(MONTH FROM `dob`) = EXTRACT(MONTH FROM UTC_TIMESTAMP())');
        foreach ($collection as $customer) {
            $rewardAccount = Mage::getModel('giantpoints/customer')->load($customer->getRewardId());
            if (!$rewardAccount || !$rewardAccount->getId()) {
                if (!$rewardAccount = $this->_createRewardAccount($customer)) {
                    continue;
                }
            }
            try {
                $rewardAccount->setLastBirthday(preg_replace("#^(\d+)#is", gmdate('Y'), $customer->getDob()))->save();
            } catch (Exception $e) {
                Mage::helper('giantpoints')->log('Exception: ' . $e->getMessage() . ' in ' . __CLASS__ . ' on line ' . __LINE__);
            }
            if (!$points = $this->_getPointsToSend($customer)) {
                continue;
            }
            $customer = Mage::getModel('customer/customer')->load($customer->getEntityId());
            Mage::register(self::STOP_MAIL, true, true);
            if (!$this->_addBirthdayTransaction($customer, $points)) {
                continue;
            }
            /* notifications for store are not enabled */
            $behaviorConfig = Mage::helper('giantpointsbhv/config');
            if (!$behaviorConfig->getIsEnabledEmail($customer->getStoreId())) {
                continue;
            }
            /* customer is not subscribed to email notifications */
            if (!$rewardAccount->getNotificationUpdate()) {
                continue;
            }
            try {
                /*send birthday email*/
                $comment = $helper->__('%s for birthday', $helper->getPointLabel($customer->getStoreId()));
                Mage::getModel('core/email_template')
                    ->setDesignConfig(array('area' => 'frontend', 'store' => $customer->getStoreId()))
                    ->sendTransactional(
                        $behaviorConfig->getPointsBirthdayTemplate($customer->getStoreId()),
                        $behaviorConfig->getNotificationSender($customer->getStoreId()),
                        $customer->getEmail(),
                        null,
                        array(
                            'store'         => $customer->getStore(),
                            'customer'      => $customer,
                            'point_balance' => $rewardAccount->getPointBalance() + $points,
                            'point_amount'  => $points,
                            'comment'       => $comment,
                            'pointsname'    => $helper->getPointLabel($customer->getStoreId())
                        ),
                        $customer->getStoreId()
                    );
            } catch (Exception $e) {
                Mage::helper('giantpoints')->log('Exception: ' . $e->getMessage() . ' in ' . __CLASS__ . ' on line ' . __LINE__);
            }
        }
    }

    protected function _createRewardAccount($customer)
    {
        $isSubscribedByDefault = Mage::helper('giantpoints/config')->getIsSubscribedByDefault();
        $rewardAccount         = Mage::getModel('giantpoints/customer');
        $rewardAccount->setCustomerId($customer->getId());
        if ($isSubscribedByDefault) {
            $rewardAccount
                ->setNotificationUpdate(1)
                ->setNotificationExpire(1);
        }
        $rewardAccount->save();

        return $rewardAccount;
    }

    protected function _addBirthdayTransaction($customer, $pointAmount)
    {

        $obj            = new Varien_Object(array(
            'point_amount' => $pointAmount,
            'store_id'     => $customer->getStoreId(),
        ));
        $additionalData = array(
            'customer'      => $customer,
            'action_object' => $obj,
        );
        try {
            Mage::helper('giantpoints/action')->createTransaction('customer_birthday', $additionalData);

            return true;
        } catch (Exception $e) {
            Mage::helper('giantpoints')->log('Exception: ' . $e->getMessage() . ' in ' . __CLASS__ . ' on line ' . __LINE__);
        }

        return false;
    }

    protected function _getPointsToSend($customer)
    {
        $helper = Mage::helper('giantpointsbhv/config');
        if (!$customer->getIsActive()) {
            return 0;
        }
        $birthdayRule = Mage::getModel('giantpoints/rule')
            ->getRuleByCondition($customer, Magegiant_GiantPointsBehavior_Model_Rule_Action_Customer_Birthday::ACTION_CODE);
        if ($birthdayRule && $birthdayRule->getId()) {
            $points = $birthdayRule->getPointAmount();
        } else {
            $points = $helper->getPointsForBirthday($customer->getStoreId());
        }
        if (!$points) {
            return 0;
        }

        if ($customer->getLastBirthday()) {
            $now = new Zend_Date();
            $now->setTimezone('UTC');
            $dob = new Zend_Date($customer->getLastBirthday(), Zend_Date::ISO_8601);
            $dob->setTimezone('UTC');
            $dateDiff = $now->get(Zend_Date::TIMESTAMP) - $dob->get(Zend_Date::TIMESTAMP);
            $days     = floor((($dateDiff / 60) / 60) / 24);
            if ($days < 365) {
                return 0;
            }
        }

        return $points;
    }

}
