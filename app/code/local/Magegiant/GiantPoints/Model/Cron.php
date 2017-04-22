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
class Magegiant_GiantPoints_Model_Cron
{
    /**
     * Process complete , expire transaction
     */
    public function run()
    {
        $this->expireTransactions();
        $this->completeOnholdTransation();
    }

    public function completeOnholdTransation()
    {
        $onholdTransactions = Mage::getResourceModel('giantpoints/transaction_collection')
            ->addNotLockedFilter()
            ->addOnholdFilter();
        foreach ($onholdTransactions as $trans) {
            $trans->completeTransaction();
        }

        return $this;
    }

    public function expireTransactions()
    {

        $this->_expireTransactions();
        $this->_sendEmailBeforeExpire();

        // send before expire email to customer

        return $this;
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

    /**
     * Process expire transactions
     */
    protected function _expireTransactions()
    {
        $expiredTransactions = Mage::getResourceModel('giantpoints/transaction_collection')
            ->addAvailableBalanceFilter()
            ->addNotLockedFilter()
            ->addExpiredFilter();
        if ($expiredTransactions->getSize()) {
            $expiredTransactions->lock();
            foreach ($expiredTransactions as $tran) {
                try {
                    $rewardCustomer = Mage::getModel('giantpoints/customer')->load($tran->getRewardId());
                    $customer       = Mage::getModel('customer/customer')->load($tran->getCustomerId());
                    $tran->setData('reward_customer', $rewardCustomer);
                    $tran->setData('customer', $customer);
                    $tran->expireTransaction();

                } catch (Exception $e) {
                    Mage::helper('giantpoints')->log('Exception: ' . $e->getMessage() . ' in ' . __CLASS__ . ' on line ' . __LINE__);
                }

            }
        }

    }

    protected function _sendEmailBeforeExpire()
    {
        Mage::helper('giantpoints/email')->sendEmailBeforeExpire();
    }
}
