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
 * @package     MageGiant_GiantPointsBehavior
 * @copyright   Copyright (c) 2014 MageGiant (http://magegiant.com/)
 * @license     http://magegiant.com/license-agreement/
 */

/**
 * GiantPointsBehavior Observer Model
 *
 * @category    MageGiant
 * @package     MageGiant_GiantPointsBehavior
 * @author      MageGiant Developer
 */
class Magegiant_GiantPointsBehavior_Model_Observer extends Magegiant_GiantPoints_Model_Observer
{
    /**
     * Customer post dispatch event
     *
     * @param $observer
     * @return $this
     */
    public function postdispatchCustomerAccountCreatePost($observer)
    {
        if (!$this->_isEnabledModule()) {
            return $this;
        }
        $customer = Mage::registry('giantpoints_current_customer');
        if (!$customer || !$customer->getId()) {
            return $this;
        }
        self::$_customerNotSet = false;
        $rewardAccount         = Mage::getModel('giantpoints/customer')->getAccountByCustomer($customer);
        if (!$rewardAccount || !$rewardAccount->getId()) {
            $this->_createRewardCustomer($customer);
        }
        $this->_pointForCustomerSignup($customer);

    }

    /**
     * Customer Register Success
     *
     * @param $observer
     * @return $this
     */

    public function customerRegisterSuccess($observer)
    {
        if (!$this->_isEnabledModule()) {
            return $this;
        }
        $customer = $observer->getCustomer();
        if (!$customer || !$customer->getId()) {
            return $this;
        }
        $rewardAccount = Mage::getModel('giantpoints/customer')->getAccountByCustomer($customer);
        if (!$rewardAccount || !$rewardAccount->getId()) {
            $this->_createRewardCustomer($customer);
        }
        $this->_pointForCustomerSignup($customer);
    }

    protected function _pointForCustomerSignup($customer)
    {
        $rule            = Mage::getModel('giantpoints/rule')
            ->getRuleByCondition($customer, Magegiant_GiantPoints_Model_Rule_Action::ACTION_SIGN_UP);
        $_behaviorHelper = $this->_behaviorHelper();
        if ($rule && $rule->getId()) {
            $pointsForRegistration = $rule->getPointAmount();
        } else {
            $pointsForRegistration = $_behaviorHelper->getPointsForRegistration($customer->getStoreId());
        }
        if (!$pointsForRegistration) {
            return $this;
        }
        $obj            = new Varien_Object(array(
            'point_amount'    => $pointsForRegistration,
            'onhold_duration' => ($rule && $rule->getId()) ? $rule->getOnholdDuration() : 0
        ));
        $additionalData = array(
            'customer'      => $customer,
            'action_object' => $obj,
            'notice'        => null,
        );
        Mage::helper('giantpoints/action')->createTransaction('customer_register', $additionalData);

        return $this;
    }


    /**
     * Process points for customer referral
     *
     * @param $code_or_email
     * @param $new_customer
     * @return $this
     */


    /**
     * process earn points for subcription newsletter
     *
     * @param $observer
     * @return $this]
     */
    public function earnPointsForSubscription($observer)
    {
        if (!$this->_isEnabledModule()) {
            return $this;
        }
        $currentSubscriber = $observer->getEvent()->getSubscriber();
        if (!$currentSubscriber || !$currentSubscriber->getCustomerId() || !$currentSubscriber->getIsStatusChanged()) {
            return $this;
        }
        $customer      = Mage::getModel('customer/customer')->load($currentSubscriber->getCustomerId());
        $rewardAccount = Mage::getModel('giantpoints/customer')->getAccountByCustomer($customer);
        if (!$rewardAccount || !$rewardAccount->getId()) {
            $rewardAccount = $this->_createRewardCustomer($customer);
        }
        $rule            = Mage::getModel('giantpoints/rule')
            ->getRuleByCondition($customer, Magegiant_GiantPoints_Model_Rule_Action::ACTION_NEWSLETTER);
        $_behaviorHelper = $this->_behaviorHelper();
        if ($rule && $rule->getId()) {
            $pointsForNewsletter = $rule->getPointAmount();
        } else {
            $pointsForNewsletter = $_behaviorHelper->getPointsForNewsletter($customer->getStoreId());
        }
        if (!$pointsForNewsletter) {
            return $this;
        }
        if ($rewardAccount->getSubscriptionEarned() == 0 && $currentSubscriber->isSubscribed()) {
            $rewardAccount->setSubscriptionEarned(1)->setId($rewardAccount->getId());
            try {
                $rewardAccount->save();
            } catch (Exception $e) {
                Mage::helper('giantpoints')->log('Exception: ' . $e->getMessage() . ' in ' . __CLASS__ . ' on line ' . __LINE__);
            }
            $obj            = new Varien_Object(array(
                'point_amount'    => $pointsForNewsletter,
                'onhold_duration' => ($rule && $rule->getId()) ? $rule->getOnholdDuration() : 0

            ));
            $additionalData = array(
                'customer'      => $customer,
                'action_object' => $obj,
                'notice'        => null,
            );
            Mage::helper('giantpoints/action')->createTransaction('customer_subscription', $additionalData);
        }

        return $this;
    }


    /**
     * @param $observer
     * @return $this
     */
    public function earnPointsForReview($observer)
    {
        $review = $observer->getDataObject();
        if (!$this->_isEnabledModule($review->getStoreId())) {
            return $this;
        }
        $givePointsForReview = true;

        $oldStatusId = $review->getOrigData('status_id');
        $newStatusId = $review->getStatusId();
        $customerId  = $review->getCustomerId();
        $productId   = $review->getEntityPkValue();
        if (Mage::helper('giantpointsbhv/config')->isForBuyersOnly($review->getStoreId())) {
            $givePointsForReview = $this->_hasCustomerBoughtThisProduct($customerId, $productId);
        }
        if ($givePointsForReview && $customerId && $newStatusId != $oldStatusId
        ) {
            $notice = array(
                'product_id'      => $productId,
                'review_approved' => $review->getId()
            );
            if ($newStatusId == Mage_Review_Model_Review::STATUS_PENDING) {
                $_behaviorHelper = $this->_behaviorHelper();
                $customer        = Mage::getModel('customer/customer')->load($customerId);
                $rewardAccount   = Mage::getModel('giantpoints/customer')->getAccountByCustomer($customer);
                if (!$rewardAccount || !$rewardAccount->getId()) {
                    $rewardAccount = $this->_createRewardCustomer($customer);
                }
                $rule = Mage::getModel('giantpoints/rule')
                    ->getRuleByCondition($customer, Magegiant_GiantPoints_Model_Rule_Action::ACTION_WRITES_REVIEW);
                if ($rule && $rule->getId()) {
                    $pointsForReview = $rule->getPointAmount();
                } else {
                    $pointsForReview = $_behaviorHelper->getPointsForReview($customer->getStoreId());
                }
                $pointsLimit = $_behaviorHelper->getReviewLimitPerDay($review->getStoreId());
                $pointAmount = $_behaviorHelper->limitAmountByDay($rewardAccount->getRewardId(), 'review_approved', $pointsForReview, $pointsLimit);
                $limitWords  = $_behaviorHelper->limitAmountByWordsCount($pointAmount, $review);
                if (!$pointAmount || !$limitWords)
                    return $this;
                $product        = Mage::getModel('catalog/product')->load($productId);
                $obj            = new Varien_Object(array(
                    'point_amount' => $pointAmount,
                    'store_id'     => $review->getStoreId(),
                    'product_name' => $product->getName(),
                ));
                $additionalData = array(
                    'customer'      => $customer,
                    'action_object' => $obj,
                    'notice'        => $notice,
                );

                try {
                    Mage::helper('giantpoints/action')->createTransaction('review_approved', $additionalData);
                    $message = Mage::helper('giantpoints')->__('You earned %s for reviewed this product', Mage::helper('giantpoints')->addLabelForPoint($pointAmount));
                    Mage::getSingleton('core/session')->addSuccess($message);
                } catch (Exception $e) {
                    Mage::getSingleton('core/session')->addError('Exception: ' . $e->getMessage() . ' in ' . __CLASS__ . ' on line ' . __LINE__);
                }
            }
            if ($newStatusId == Mage_Review_Model_Review::STATUS_APPROVED) {
                $transactions = Mage::getResourceModel('giantpoints/transaction_collection')
                    ->getTransactionByNotice($customerId, 'review_approved', $notice);
                foreach ($transactions as $tran) {
                    if ($tran->getStatus() == Magegiant_GiantPoints_Model_Transaction::STATUS_COMPLETED)
                        continue;
                    $tran->completeTransaction();
                }
            }
        }

        return $this;
    }

    public function modelSaveAfter($observer)
    {
        $this
            ->_earnPointsForParticipateInPoll($observer)
            ->_earnPointsForTagging($observer);
    }

    /**
     * @param $observer
     * @return $this
     */
    protected function _earnPointsForParticipateInPoll($observer)
    {
        $object = $observer->getObject();
        if (($pollVote = $object) instanceof Mage_Poll_Model_Poll_Vote) {
            if (!$this->_isEnabledModule(Mage::app()->getStore()->getStoreId())) {
                return $this;
            }

            if ($customerId = $pollVote->getCustomerId()) {
                $customer        = Mage::getModel('customer/customer')->load($customerId);
                $rewardAccount   = Mage::getModel('giantpoints/customer')->getAccountByCustomer($customer);
                $_behaviorHelper = $this->_behaviorHelper();;
                $rule = Mage::getModel('giantpoints/rule')
                    ->getRuleByCondition($customer, Magegiant_GiantPoints_Model_Rule_Action::ACTION_POLL);
                if ($rule && $rule->getId()) {
                    $pointsForPoll = $rule->getPointAmount();
                } else {
                    $pointsForPoll = $_behaviorHelper->getPointsForPoll($customer->getStoreId());
                }
                $pollStatus = $_behaviorHelper->getStatusAfterPoll($customer->getStoreId());
                if ($pollStatus == Magegiant_GiantPoints_Model_Actions_Earning_Status::STATUS_APPROVED) {
                    $status = Magegiant_GiantPoints_Model_Transaction::STATUS_COMPLETED;
                } else {
                    $status = Magegiant_GiantPoints_Model_Transaction::STATUS_PENDING;
                }
                $pointsLimit = $_behaviorHelper->getPollLimitPerDay($customer->getStoreId());
                $pointAmount = $_behaviorHelper->limitAmountByDay($rewardAccount->getRewardId(), 'customer_participate_in_poll', $pointsForPoll, $pointsLimit);
                if (!$pointAmount)
                    return $this;
                $obj            = new Varien_Object(array(
                    'point_amount' => $pointAmount,
                    'status'       => $status,
                ));
                $additionalData = array(
                    'customer'      => $customer,
                    'action_object' => $obj,
                    'notice'        => null,
                );
                try {
                    Mage::helper('giantpoints/action')->createTransaction('customer_participate_in_poll', $additionalData);
                    $message = Mage::helper('giantpoints')->__('You earned %s for votes in poll', Mage::helper('giantpoints')->addLabelForPoint($pointAmount));
                    Mage::getSingleton('core/session')->addSuccess($message);
                } catch (Exception $e) {
                    Mage::helper('giantpoints')->log('Exception: ' . $e->getMessage() . ' in ' . __CLASS__ . ' on line ' . __LINE__);
                }
            }
        }

        return $this;
    }

    /**
     * @param $observer
     * @return $this
     */
    protected function _earnPointsForTagging($observer)
    {
        $object = $observer->getObject();

        if (($tagToApprove = $object) instanceof Mage_Tag_Model_Tag) {
            if (!$this->_isEnabledModule($tagToApprove->getStoreId())) {
                return $this;
            }
            $tagCollection = Mage::getModel('tag/tag')
                ->getCollection()
                ->joinRel()
                ->addStatusFilter(Mage_Tag_Model_Tag::STATUS_APPROVED);

            $tagCollection
                ->getSelect()
                ->where('main_table.tag_id = ?', $tagToApprove->getTagId());
            foreach ($tagCollection->getData() as $tag) {
                $tagObject = new Varien_Object;
                unset($tag['tag_id']);
                $tagObject->setData($tag);
                $customer_id = $tagObject->getCustomerId() ? $tagObject->getCustomerId() : $tagObject->getFirstCustomerId();
                if (!$customer_id)
                    break;
                $customer      = Mage::getModel('customer/customer')->load($customer_id);
                $rewardAccount = Mage::getModel('giantpoints/customer')->getAccountByCustomer($customer);
                if ($this->_isNotSetInCustomer($customer, $tagObject->getTagRelationId())) {
                    $_behaviorHelper = $this->_behaviorHelper();
                    $rule            = Mage::getModel('giantpoints/rule')
                        ->getRuleByCondition($customer, Magegiant_GiantPoints_Model_Rule_Action::ACTION_TAG);
                    if ($rule && $rule->getId()) {
                        $pointsForTagging = $rule->getPointAmount();
                    } else {
                        $pointsForTagging = $_behaviorHelper->getPointsForTaggingProduct($customer->getStoreId());
                    }
                    $pointsLimit = $_behaviorHelper->getTagsLimitPerDay($customer->getStoreId());
                    $pointAmount = $_behaviorHelper->limitAmountByDay($rewardAccount->getRewardId(), 'customer_tag_product', $pointsForTagging, $pointsLimit);
                    $product     = Mage::getModel('catalog/product')->load($tagObject->getProductId());
                    if (!$pointAmount)
                        return $this;
                    $obj            = new Varien_Object(array(
                        'point_amount'    => $pointAmount,
                        'store_id'        => $customer->getStoreId(),
                        'product_name'    => $product->getName(),
                        'onhold_duration' => ($rule && $rule->getId()) ? $rule->getOnholdDuration() : 0

                    ));
                    $additionalData = array(
                        'customer'      => $customer,
                        'action_object' => $obj,
                        'notice'        => array('product_name' => $product->getName()),
                    );
                    Mage::helper('giantpoints/action')->createTransaction('customer_tag_product', $additionalData);
                }
            }
        }

        return $this;
    }

    /**
     * Check module is enabled
     */
    protected function _isEnabledModule($store = null)
    {
        return (bool)(Mage::helper('giantpoints/config')->isEnabled($store)
            && Mage::helper('giantpointsbhv/config')->isEnabled($store));
    }


    protected function _behaviorHelper()
    {
        return Mage::helper('giantpointsbhv/config');
    }
}