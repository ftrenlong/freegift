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
class Magegiant_GiantPoints_Model_Transaction extends Mage_Core_Model_Abstract
{
    const STATUS_PENDING = 1;
    const STATUS_COMPLETED = 2;
    const STATUS_CANCELED = 3;
    const STATUS_EXPIRED = 4;
    protected $_rewardCustomer;

    public function _construct()
    {
        parent::_construct();
        $this->_init('giantpoints/transaction');
    }


    /**
     * get reward customer
     *
     * @return reward customer object
     */
    public function getRewardCustomer()
    {
        if (!$this->_rewardCustomer) {
            $this->setRewardCustomer(Mage::getModel('giantpoints/customer')->load($this->getRewardId()));
        }

        return $this->_rewardCustomer;
    }

    /**
     * set reward customer
     *
     * @param $customer
     */
    public function setRewardCustomer($customer)
    {
        $this->_rewardCustomer = $customer;
    }

    protected function _beforeSave()
    {
        $this->setChangeDate(Mage::helper('giantpoints')->getMageDate('Y-m-d H:i:s'));

        return parent::_beforeSave();
    }

    /**
     * add transaction to change giant points
     *
     * @param array $data
     * @return $this
     * @throws Exception
     */
    public function addTransaction($data = array())
    {
        $transaction = $this;
        $transaction->addData($data);

        if (!$transaction->getPointAmount()) {
            throw new Exception(
                Mage::helper('giantpoints')->__('Zero transaction amount')
            );
        }
        $rewardCustomer = Mage::getModel('giantpoints/customer')->getAccountByCustomer($this->getCustomer());
        if (!$rewardCustomer || !$rewardCustomer->getId()) {
            $rewardCustomer = $this->createRewardCustomer();
        }
        $transaction->setRewardCustomer($rewardCustomer);
        $transaction->setRewardId($rewardCustomer->getId());
        if ($rewardCustomer->getPointBalance() + $this->getPointAmount() < 0) {
            throw new Exception(
                Mage::helper('giantpoints')->__('Account balance is not enough to create this transaction.')
            );
        }
        // set default status if not define

        if (!$transaction->getData('status')) {
            $transaction->setData('status', self::STATUS_PENDING);
        }
        switch ($this->getActionType()) {
            case Magegiant_GiantPoints_Model_Actions_Abstract::GIANTPOINTS_ACTION_TYPE_EARNING:
                $this->_addEarningTransaction();
                break;
            case Magegiant_GiantPoints_Model_Actions_Abstract::GIANTPOINTS_ACTION_TYPE_SPENDING:
                $this->_addSpendingTransaction();
                break;
            default:
                $this->_addDefaultTransaction();
                break;
        }

        return $this;
    }

    /**
     * create reward customer if not exist
     */
    public function createRewardCustomer()
    {
        $isSubscribedByDefault = Mage::helper('giantpoints/config')->getIsSubscribedByDefault();
        $rewardCustomer        = Mage::getModel('giantpoints/customer');
        $rewardCustomer->setCustomerId($this->getCustomerId())
            ->setPointBalance(0)
            ->setSpentBalance(0)
            ->setNotificationUpdate(0)
            ->setNotificationExpire(0);

        if ($isSubscribedByDefault) {
            $rewardCustomer
                ->setNotificationUpdate(1)
                ->setNotificationExpire(1);
        }
        try {
            $rewardCustomer->save();
        } catch (Exception $e) {
            Mage::helper('giantpoints')->log('Exception: ' . $e->getMessage() . ' in ' . __CLASS__ . ' on line ' . __LINE__);
        }

        return $rewardCustomer;
    }

    /**
     * add action type is earning
     *
     * @return $this
     * @throws Exception
     */
    protected function _addEarningTransaction()
    {
        if ($this->getPointAmount() < 0) {
            throw new Exception(Mage::helper('giantpoints')->__('points amount must be greater 0'));
        }
        $rewardCustomer = $this->getRewardCustomer();
        $this->setPointBalance($this->getPointAmount());
        if ($this->getStatus() == self::STATUS_COMPLETED) {
            $maxBalance = Mage::helper('giantpoints/config')->getMaxPointPerCustomer($this->getStoreId());
            if ($maxBalance > 0 && $rewardCustomer->getPointBalance() + $this->getPointAmount() > $maxBalance) {
                if ($maxBalance > $rewardCustomer->getPointBalance()) {
                    $this->setPointAmount($maxBalance - $rewardCustomer->getPointBalance());
                    $this->setPointBalance($maxBalance - $rewardCustomer->getPointBalance());
                    $rewardCustomer->setPointBalance($maxBalance);
                } else {
                    return $this;
                }
            } else {
                $this->setPointBalance($this->getPointAmount());
                $rewardCustomer->setPointBalance($rewardCustomer->getPointBalance() + $this->getPointAmount());
            }
            if ($this->getActionType() == Magegiant_GiantPoints_Model_Actions_Abstract::GIANTPOINTS_ACTION_TYPE_REFUNDING) {
                $rewardCustomer->setPointSpent($rewardCustomer->getPointSpent() - $this->getPointAmount());
                $this->_getResource()->applyPointSpent($this);
            }
            try {
                $this->save();
                $rewardCustomer->save();
                $this->sendBalanceEmailUpdated($rewardCustomer);
            } catch (Exception $e) {
                Mage::helper('giantpoints')->log('Exception: ' . $e->getMessage() . ' in ' . __CLASS__ . ' on line ' . __LINE__);
            }
        } else {
            $this->save();
        }
    }

    /**
     * add action type is spending
     * return $this
     */
    protected function _addSpendingTransaction()
    {
        $transaction = $this;
        if ($transaction->getPointAmount() > 0)
            throw new Exception(Mage::helper('giantpoints')->__('points amount must be lower 0'));
        $rewardCustomer = $this->getRewardCustomer();
        $rewardCustomer->setPointSpent($rewardCustomer->getPointSpent() - $this->getPointAmount());
        $rewardCustomer->setPointBalance($rewardCustomer->getPointBalance() + $this->getPointAmount());
        if (!$transaction->getStatus()) {
            $transaction->setStatus(self::STATUS_COMPLETED);
        }
        try {
            $rewardCustomer->save();
            $transaction->save();
            if ($transaction->getActionType() == Magegiant_GiantPoints_Model_Actions_Abstract::GIANTPOINTS_ACTION_TYPE_REFUNDING) {
                // Update balance points for transaction
                $this->_getResource()->applyPointBalance($this);
            }
            $this->_getResource()->applyPointSpent($this);
            $this->sendBalanceEmailUpdated($rewardCustomer);
        } catch (Exception $e) {
            Mage::helper('giantpoints')->log('Exception: ' . $e->getMessage() . ' in ' . __CLASS__ . ' on line ' . __LINE__);
        }

    }

    /**
     * process action type is both
     * return
     */
    protected function _addDefaultTransaction()
    {
        if ($this->getPointAmount() > 0) {
            $this->_addEarningTransaction();
        } else {
            $this->_addSpendingTransaction();
        }
    }


    public function throwExeption($message)
    {
        throw new Exception(
            $message
        );
    }

    /**
     * Cancel transaction
     *
     * @return Magegiant_GiantPoints_Model_Transaction
     */
    public function cancelTransaction()
    {
        $transaction   = $this;
        $transactionId = $transaction->getId();
        $customerId    = $transaction->getCustomerId();
        $rewardId      = $transaction->getRewardId();
        $pointAmount   = $transaction->getPointAmount();
        $status        = $transaction->getStatus();
        $pointBalance  = $transaction->getPointBalance();
        if (!$transactionId || !$customerId
            || !$rewardId || $pointAmount <= 0
            || $status > self::STATUS_COMPLETED || !$status
        ) {
            $this->throwExeption(Mage::helper('giantpoints')->__('Invalid transaction data to cancel.'));

            return $this;
        }


        if ($status != self::STATUS_COMPLETED) {
            $transaction->setStatus(self::STATUS_CANCELED);
            $transaction->save();

            return $this;
        }
        $transaction->setStatus(self::STATUS_CANCELED);
        $rewardCustomer = $this->getRewardCustomer();
        if ($rewardCustomer->getPointBalance() < $pointBalance) {
            $this->throwExeption(Mage::helper('giantpoints')->__('Account balance is not enough to cancel.'));

            return $this;
        }
        $rewardCustomer->setData('point_balance', $rewardCustomer->getPointBalance() - $this->getPointBalance());
        $this->sendBalanceEmailUpdated($rewardCustomer);

        if ($transaction->getPointSpent() > 0) {
            $transaction->setPointAmount(-$this->getPointSpent());
            $this->_getResource()->applyPointSpent($this);
            $transaction->setData('point_amount', $pointAmount);
        }
        try {
            //Save to reward customer, transaction
            $rewardCustomer->save();
            $transaction->save();
        } catch (Exception $e) {
        }

        return $this;
    }

    /**
     * expire transaction
     *
     * @return $this
     */
    public function expireTransaction()
    {
        $transaction    = $this;
        $transactionId  = $transaction->getId();
        $customerId     = $transaction->getCustomerId();
        $rewardId       = $transaction->getRewardId();
        $pointAmount    = $transaction->getPointAmount();
        $pointSpent     = $transaction->getPointSpent();
        $status         = $transaction->getStatus();
        $rewardCustomer = $this->getRewardCustomer();
        if (!$transactionId || !$customerId
            || !$rewardId || $pointAmount <= $pointSpent
            || $status > self::STATUS_COMPLETED || !$status
            || strtotime($transaction->getExpirationDate()) > time() || !$transaction->getExpirationDate()
        ) {
            return $this;
        }
        if ($status != self::STATUS_COMPLETED) {
            $transaction->setStatus(self::STATUS_EXPIRED);
            try {
                $transaction->save();
            } catch (Exception $e) {
            }

            return $this;
        }
        $transaction->setData('status', self::STATUS_EXPIRED);
        $rewardCustomer->setData('point_balance', $rewardCustomer->getPointBalance() - $this->getPointAmount() + $this->getPointSpent());
        $this->sendBalanceEmailUpdated($rewardCustomer);
        try {
            $rewardCustomer->save();
            $transaction->save();
        } catch (Exception $e) {
        }

        return $this;
    }

    /**
     * complete transaction
     *
     * @return $this
     * @throws Exception
     */
    public function completeTransaction()
    {
        $transaction    = $this;
        $transactionId  = $transaction->getId();
        $customerId     = $transaction->getCustomerId();
        $status         = $transaction->getStatus();
        $pointAmount    = $transaction->getPointAmount();
        $rewardCustomer = $this->getRewardCustomer();
        if (!$transactionId || !$customerId
            || !$rewardCustomer || $pointAmount <= 0
            || $status != self::STATUS_PENDING
        ) {
            $this->throwExeption(Mage::helper('giantpoints')->__('Invalid transaction data to complete.'));
        }
        $maxBalance      = (int)Mage::helper('giantpoints/config')->getMaxPointPerCustomer($this->getStoreId());
        $pointBalance    = $transaction->getPointBalance();
        $customerBalance = $rewardCustomer->getPointBalance();
        if ($maxBalance > 0 && $pointBalance > 0 && ($customerBalance + $pointBalance) > $maxBalance
        ) {
            if ($maxBalance > $customerBalance) {
                $transaction->setData('point_amount', $maxBalance - $customerBalance + $this->getPointAmount() - $pointBalance);
                $transaction->setData('point_balance', $maxBalance - $rewardCustomer->getPointBalance());
                $rewardCustomer->setData('point_balance', $maxBalance);
                $this->sendBalanceEmailUpdated($rewardCustomer);
            } else {
                $this->throwExeption(Mage::helper('giantpoints')->__('Maximum points allowed in account balance is %s.', $maxBalance));

                return $this;
            }
        } else {
            $rewardCustomer->setData('point_balance', $rewardCustomer->getPointBalance() + $this->getPointBalance());
            $this->sendBalanceEmailUpdated($rewardCustomer);
        }

        $rewardCustomer->save();

        try {
            $transaction->setData('status', self::STATUS_COMPLETED);
            $transaction->save();
        } catch (Exception $e) {
        }

        return $this;
    }

    /**
     * send email update point balance
     *
     * @param null $rewardCustomer
     * @return $this
     */
    public function sendBalanceEmailUpdated($rewardCustomer = null)
    {
        $transaction    = $this;
        $actionCode     = $transaction->getActionCode();
        $isEnabledEmail = Mage::helper('giantpoints/config')->isEmailEnabled();
        if (!$rewardCustomer) {
            $rewardCustomer = $this->getRewardCustomer();
        }
        if (!$isEnabledEmail || $actionCode == 'customer_birthday' || !$rewardCustomer->getNotificationUpdate()) {
            return $this;
        }
        Mage::helper('giantpoints/email')->sendUpdateBalanceEmail($transaction, $rewardCustomer);

        return $this;
    }

    /**
     * get transaction status label
     *
     * @return string
     */
    public function getTransactionStatusLabel()
    {
        $transaction = $this;
        $array       = Mage::getSingleton('giantpoints/transaction_status')->getStatusHash();
        if (isset($array[$transaction->getStatus()])) {
            return $array[$transaction->getStatus()];
        }

        return '';
    }

    /**
     * send email before point exprire
     *
     * @return $this
     */
    public function sendEmailBeforeExpire($rewardCustomer = null)
    {
        $transaction    = $this;
        $isEnabledEmail = Mage::helper('giantpoints/config')->isEmailEnabled();
        if (!$rewardCustomer) {
            $rewardCustomer = $this->getRewardCustomer();
        }
        if (!$isEnabledEmail || !$rewardCustomer->getNotificationUpdate()) {
            return $this;
        }

        Mage::helper('giantpoints/email')->sendBeforeExpireBalanceEmail($transaction, $rewardCustomer);
    }


}