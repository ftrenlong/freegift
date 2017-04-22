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
 * Giantpoints Block
 *
 * @category    Magegiant
 * @package     Magegiant_GiantPoints
 * @author      Magegiant Developer
 */
class Magegiant_GiantPointsBehavior_Block_Earning extends Magegiant_GiantPoints_Block_Abstract
{
    protected $_helper;

    public function __construct()
    {
        parent::_construct();
        $this->_helper = Mage::helper('giantpointsbhv/config');
    }

    /**
     * get message earn points for signing up account
     *
     * @return string
     */
    public function getRewardRegistration()
    {
        $point = $this->getEarningPointByCondition(Magegiant_GiantPoints_Model_Rule_Action::ACTION_SIGN_UP);
        if (!$point)
            $point = $this->_helper->getPointsForRegistration();
        if ($point) {
            return $this->__('Earn %s for registering an account', Mage::helper('giantpoints')->addLabelForPoint($point));
        }

        return '';

    }

    /**
     * get earn message to show on frontend
     *
     * @return string
     */
    public function getRewardNewsletter()
    {
        $point = $this->getEarningPointByCondition(Magegiant_GiantPoints_Model_Rule_Action::ACTION_NEWSLETTER);
        if (!$point)
            $point = $this->_helper->getPointsForNewsletter();
        if ($point) {
            return $this->__('Earn %s for subscribing to newsletter.', Mage::helper('giantpoints')->addLabelForPoint($point));
        }

        return '';
    }


    /**
     * get earn message to show on frontend
     *
     * @return string
     */
    public function getRewardPoll()
    {
        $point = $this->getEarningPointByCondition(Magegiant_GiantPoints_Model_Rule_Action::ACTION_POLL);
        if (!$point)
            $point = $this->_helper->getPointsForPoll();
        if ($point) {
            return $this->__('Earn %s for taking poll', Mage::helper('giantpoints')->addLabelForPoint($point));
        }

        return '';
    }

    /**
     * get product
     *
     * @return current product
     */
    public function getProduct()
    {
        return Mage::registry('current_product');
    }

    /**
     * get earn message to show on frontend
     *
     * @return string
     */
    public function getRewardTagsProduct()
    {
        $point = $this->getEarningPointByCondition(Magegiant_GiantPoints_Model_Rule_Action::ACTION_TAG);
        if (!$point)
            $point = $this->_helper->getPointsForTaggingProduct();
        if ($point) {
            return $this->__('You will earn %s for writing a tag for this product', Mage::helper('giantpoints')->addLabelForPoint($point));
        }

        return '';
    }

    /**
     * check rate, review is enable
     *
     * @return int
     */
    public function getRewardReviewProduct()
    {
        if (!$this->hasData('product-review-earning')) {
            $point = $this->getEarningPointByCondition(Magegiant_GiantPoints_Model_Rule_Action::ACTION_WRITES_REVIEW);
            if (!$point)
                $point = $this->_helper->getPointsForReview();
            if ($point) {
                $review_link = '<a href="' . $this->getReviewsUrl() . '" >' . $this->__('review this product') . '</a>';
                $this->setData('product-review-earning', true);

                return $this->__('You will earn %s for writing a %s', Mage::helper('giantpoints')->addLabelForPoint($point), $review_link);
            }
        }

        return '';
    }

    public function getReviewsUrl()
    {
        return Mage::getUrl('review/product/list', array(
            'id'       => $this->getProduct()->getId(),
            'category' => $this->getProduct()->getCategoryId()
        ));
    }

    /**
     * get earn message to show on frontend
     *
     * @return string
     */
    public function getRewardBirthday()
    {
        $point = $this->getEarningPointByCondition(Magegiant_GiantPointsBehavior_Model_Rule_Action_Customer_Birthday::ACTION_CODE);
        if (!$point)
            $point = $this->_helper->getPointsForBirthday();
        if ($point) {
            $point = Mage::helper('giantpoints')->addLabelForPoint($point);

            return $this->_helper->__('You will earn %s on your birthday.', $point);
        }

        return '';
    }

    public function getEarningPointByCondition($condition)
    {
        $customer       = $this->getCustomer();
        $behavior_rules = Mage::getResourceModel('giantpoints/rule_collection')
            ->addRuleConditionFilter($condition);
        if ($customer && $customer->getId()) {
            $behavior_rules->addAvailableFilter($customer->getGroupId(), $customer->getWebsiteId());
        }
        $behavior_rule = $behavior_rules->getFirstItem();
        if ($behavior_rule && $behavior_rule->getId()) {
            return $behavior_rule->getPointAmount();
        }

        return $this->getPointByConfigByCondition($condition);
    }

    public function getPointByConfigByCondition($condition)
    {
        $earningPoints = 0;
        switch ($condition) {
            case Magegiant_GiantPointsBehavior_Model_Rule_Action_Social_Google_PlusOne::ACTION_GOOGLE_PLUS:
                $earningPoints = $this->_helper->getPointsForGoogle();
                break;
            case Magegiant_GiantPointsBehavior_Model_Rule_Action_Social_Twitter_Tweets::ACTION_TWITTER_TWEETS:
                $earningPoints = $this->_helper->getPointsForTwitter();
                break;
            case Magegiant_GiantPointsBehavior_Model_Rule_Action_Social_Facebook_Like::ACTION_LIKE_FACEBOOK:
                $earningPoints = $this->_helper->getPointsForFacebook();
                break;
        }

        return $earningPoints;
    }
}
