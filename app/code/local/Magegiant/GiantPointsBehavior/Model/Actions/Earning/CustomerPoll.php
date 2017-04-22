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
 * @package     MageGiant_Giantpoints
 * @copyright   Copyright (c) 2014 MageGiant (http://magegiant.com/)
 * @license     http://magegiant.com/license-agreement/
 */

/**
 * Giantpoints Resource Model
 *
 * @category    MageGiant
 * @package     MageGiant_Giantpoints
 * @author      MageGiant Developer
 */
class Magegiant_GiantPointsBehavior_Model_Actions_Earning_CustomerPoll
    extends Magegiant_GiantPoints_Model_Actions_Abstract
    implements Magegiant_GiantPoints_Model_Actions_Interface
{

    protected $_actionType = Magegiant_GiantPoints_Model_Actions_Abstract::GIANTPOINTS_ACTION_TYPE_EARNING;

    /**
     * @return int
     */
    public function getPointAmount()
    {
        $actonObj = $this->getData('action_object');

        return (int)$actonObj->getPointAmount();
    }

    /**
     * @return mixed
     */
    public function getComment()
    {

        return Mage::helper('giantpoints')->__('Reward for participating in poll');
    }

    /**
     * @param null $transaction
     * @return mixed
     */
    public function getCommentHtml($transaction = null)
    {

    }

    /**
     * set transaction data of action to storage on transactions
     * the array that returned from function $action->getData('transaction_data')
     * will be setted to transaction model
     *
     * @return Magegiant_GiantPoints_Model_Actions_Interface
     */
    public function updateTransaction()
    {
        $actonObj        = $this->getData('action_object');
        $transactionData = array(
            'status'  => $actonObj->getStatus(),
            'comment' => $this->getComment(),
        );

        // Set expire time for current transaction
        $transactionData['expiration_date'] = $this->getExpirationDate();
        $this->setTransaction($transactionData);

        return parent::updateTransaction();
    }
}
