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
 * GiantPoints Helper
 *
 * @category    MageGiant
 * @package     MageGiant_GiantPoints
 * @author      MageGiant Developer
 */
class Magegiant_GiantPoints_Helper_Email extends Mage_Core_Helper_Abstract
{
    /* XML email path config*/
    const XML_PATH_EMAIL_ENABLE = 'giantpoints/email/enable';
    const XML_PATH_EMAIL_SENDER = 'giantpoints/email/sender';
    const XML_PATH_EMAIL_UPDATE_POINT_BALANCE = 'giantpoints/email/update_balance';
    const XML_PATH_EMAIL_BEFORE_POINT_EXPIRE = 'giantpoints/email/before_expire_transaction';
    const XML_PATH_EMAIL_POINT_EXPIRE_BEFORE_DAYS = 'giantpoints/email/before_expire_days';

    public function sendUpdateBalanceEmail($transaction, $rewardCustomer)
    {
        $customer = $transaction->getCustomer();
        if (!$customer) {
            $customer = Mage::getModel('customer/customer')->load($rewardCustomer->getCustomerId());
        }
        if (!$customer || !$customer->getId()) {
            return $this;
        }
        $store     = Mage::app()->getStore($transaction->getStoreId());
        $translate = Mage::getSingleton('core/translate');
        $translate->setTranslateInline(false);

        Mage::getModel('core/email_template')
            ->setDesignConfig(array(
                'area'  => 'frontend',
                'store' => $store->getId()
            ))->sendTransactional(
                Mage::getStoreConfig(self::XML_PATH_EMAIL_UPDATE_POINT_BALANCE, $store),
                Mage::getStoreConfig(self::XML_PATH_EMAIL_SENDER, $store),
                $customer->getEmail(),
                $customer->getName(),
                array(
                    'store'         => $store,
                    'customer'      => $customer,
                    'comment'       => $transaction->getComment(),
                    'amount'        => $transaction->getPointAmount(),
                    'total'         => $rewardCustomer->getPointBalance(),
                    'point_amount'  => Mage::helper('giantpoints')->addLabelForPoint($transaction->getPointAmount(), $store->getId()),
                    'point_balance' => Mage::helper('giantpoints')->addLabelForPoint($rewardCustomer->getPointBalance(), $store->getId()),
                    'status'        => $transaction->getTransactionStatusLabel(),
                )
            );

        $translate->setTranslateInline(true);
    }

    /**
     * @param $transaction
     * @param $rewardCustomer
     * @return bool
     */
    public function sendBeforeExpireBalanceEmail($transaction, $rewardCustomer)
    {
        $customer = $transaction->getCustomer();
        if (!$customer) {
            $customer = Mage::getModel('customer/customer')->load($rewardCustomer->getCustomerId());
        }
        if (!$customer || !$customer->getId()) {
            return $this;
        }
        $store     = Mage::app()->getStore($this->getStoreId());
        $translate = Mage::getSingleton('core/translate');
        $translate->setTranslateInline(false);
        $mail = Mage::getModel('core/email_template');
        $mail->setDesignConfig(array(
            'area'  => 'frontend',
            'store' => $store->getId()
        ))->sendTransactional(
            Mage::getStoreConfig(self::XML_PATH_EMAIL_BEFORE_POINT_EXPIRE, $store),
            Mage::getStoreConfig(self::XML_PATH_EMAIL_SENDER, $store),
            $customer->getEmail(),
            $customer->getName(),
            array(
                'store'          => $store,
                'customer'       => $customer,
                'comment'        => $transaction->getComment(),
                'amount'         => $transaction->getPointAmount(),
                'spent'          => $transaction->getPointSpent(),
                'total'          => $rewardCustomer->getPointBalance(),
                'point_amount'   => Mage::helper('giantpoints')->addLabelForPoint($this->getPointAmount(), $store->getId()),
                'point_spent'    => Mage::helper('giantpoints')->addLabelForPoint($this->getPointSpent(), $store->getId()),
                'point_balance'  => Mage::helper('giantpoints')->addLabelForPoint($rewardCustomer->getPointBalance(), $store->getId()),
                'status'         => $this->getTransactionStatusLabel(),
                'expirationdays' => round((strtotime($this->getExpirationDate()) - Mage::helper('giantpoints')->getMageTime()) / 86400),
                'expirationdate' => Mage::getModel('core/date')->date('M d, Y H:i:s', $this->getExpirationDate()),
            )
        );
        $translate->setTranslateInline(true);
        if ($mail->getSentSuccess()) {
            return true;
        }

        return false;
    }

    /**
     * send email to customer when transaction is expired
     */
    public function sendEmailBeforeExpire()
    {
        $stores    = array();
        $allStores = true;
        foreach (Mage::app()->getStores(true) as $_store) {
            if (Mage::helper('giantpoints/config')->isEnabled($_store)) {
                $stores[$_store->getId()] = $_store->getId();
            } else {
                $allStores = false;
            }
        }
        $beforeDays = array();
        foreach ($stores as $_store) {
            if (!Mage::helper('giantpoints/config')->isEmailEnabled($_store)) {
                $allStores = false;
                continue;
            }
            $_beforeDays = Mage::helper('giantpoints/config')->getSendEmailExpireBeforeDays($_store);
            if ($_beforeDays <= 0) {
                $allStores = false;
            } else {
                $beforeDays[$_beforeDays][$_store] = $_store;
            }
        }
        if ($allStores && count($beforeDays) == 1) { // all stores
            reset($beforeDays);
            $_beforeDays = key($beforeDays);
            Mage::getResourceModel('giantpoints/transaction_collection')->sendEmailBeforeExpire($_beforeDays);
        } elseif (count($beforeDays)) { // each group stores
            foreach ($beforeDays as $_beforeDays => $_storeIds) {
                Mage::getResourceModel('giantpoints/transaction_collection')->sendEmailBeforeExpire($_beforeDays, $_storeIds);
            }
        }

    }

}