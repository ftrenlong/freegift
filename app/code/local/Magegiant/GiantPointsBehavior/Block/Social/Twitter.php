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
class Magegiant_GiantPointsBehavior_Block_Social_Twitter extends Magegiant_GiantPointsBehavior_Block_Social_Earning
{
    /**
     * check is enabled Twitter Count Button
     *
     * @return bool
     */
    public function showButtonCount()
    {
        return $this->_helperBehavior->isShowTwitterCount();
    }

    public function getPointsForTwitter()
    {
        return $this->getEarningPointByCondition(Magegiant_GiantPointsBehavior_Model_Rule_Action_Social_Twitter_Tweets::ACTION_TWITTER_TWEETS);
    }


    protected function _toHtml()
    {
        if ($this->_isShowTwitter()) {
            return parent::_toHtml();
        }

        return '';
    }

}
