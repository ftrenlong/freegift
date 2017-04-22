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
class Magegiant_GiantPointsBehavior_Helper_Config extends Mage_Core_Helper_Abstract
{
    const XML_PATH_ENABLED                    = 'giantpoints/behavior/is_enabled';
    const XML_PATH_GENERAL_CONFIG             = 'giantpoints/behavior/';
    const GIANTPOINTS_BEHAVIOR_REGISTER       = 'giantpoints/group_signandnews/';
    const GIANTPOINTS_BEHAVIOR_REVIEW_PRODUCT = 'giantpoints/group_review_product/';
    const GIANTPOINTS_BEHAVIOR_TAG_PRODUCT    = 'giantpoints/group_tagging_product/';
    const GIANTPOINTS_BEHAVIOR_POLL           = 'giantpoints/group_poll/';
    const GIANTPOINTS_BEHAVIOR_BIRTHDAY       = 'giantpoints/group_customer_birthday/';
    const GIANTPOINTS_BEHAVIOR_FB             = 'giantpoints/group_fb_setting/';
    const GIANTPOINTS_BEHAVIOR_TW             = 'giantpoints/group_tw_setting/';
    const GIANTPOINTS_BEHAVIOR_GG             = 'giantpoints/group_gg_setting/';
    const GIANTPOINTS_PURCHASE_SHARE          = 'giantpoints/purchase_share/';
    protected $_locale;

    /**
     * @param null $storeId
     * @return mixed
     */
    public function isEnabled($storeId = null)
    {
        return Mage::getStoreConfig(self::XML_PATH_ENABLED, $storeId);
    }

    /**
     * @param      $name
     * @param null $storeId
     * @return mixed
     */
    public function getGeneralConfig($name, $storeId = null)
    {
        return Mage::getStoreConfig(self::XML_PATH_GENERAL_CONFIG . $name, $storeId);

    }

    /*=============Begin register config==========*/
    public function getRegisterConfig($code, $storeId = null)
    {
        $storeId = !is_null($storeId) ? $storeId : Mage::app()->getStore()->getId();

        return Mage::getStoreConfig(self::GIANTPOINTS_BEHAVIOR_REGISTER . $code, $storeId);
    }

    public function getPointsForRegistration($storeId = null)
    {
        return $this->getRegisterConfig('registration', $storeId);
    }

    public function getPointsForNewsletter($storeId = null)
    {
        return $this->getRegisterConfig('newsletter', $storeId);
    }

    /*================End register config====================*/

    /*================Review config==========================*/
    public function getReviewConfig($code, $storeId = null)
    {
        $storeId = !is_null($storeId) ? $storeId : Mage::app()->getStore()->getId();

        return Mage::getStoreConfig(self::GIANTPOINTS_BEHAVIOR_REVIEW_PRODUCT . $code, $storeId);
    }

    public function getPointsForReview($storeId = null)
    {
        return $this->getReviewConfig('review', $storeId);
    }

    public function getReviewLimitPerDay($storeId = null)
    {
        return $this->getReviewConfig('review_limit', $storeId);
    }

    public function getLimitWords($storeId = null)
    {
        return $this->getReviewConfig('review_words', $storeId);
    }

    public function limitAmountByWordsCount($amount, $review)
    {
        $minimumWordsCount = $this->getLimitWords();
        if ($minimumWordsCount) {
            $reviewContent = $review->getData('detail');
            $wordsCount    = count(preg_split('/\s+/mu', trim($reviewContent)));

            return $wordsCount >= $minimumWordsCount
                ? $amount
                : 0;
        }

        return $amount;
    }

    public function isForBuyersOnly($storeId = null)
    {
        return (bool)$this->getReviewConfig('restriction', $storeId);
    }
    /*====================End Review Config==============================*/

    /*====================Tags config====================================*/

    public function getTagsConfig($code, $storeId = null)
    {
        $storeId = !is_null($storeId) ? $storeId : Mage::app()->getStore()->getId();

        return Mage::getStoreConfig(self::GIANTPOINTS_BEHAVIOR_TAG_PRODUCT . $code, $storeId);
    }

    public function getPointsForTaggingProduct($storeId = null)
    {
        return $this->getTagsConfig('tag', $storeId);
    }

    public function getTagsLimitPerDay($storeId = null)
    {
        return $this->getTagsConfig('tag_limit', $storeId);
    }
    /*========================End tags config=================================*/


    /*========================Poll config=====================================*/

    public function getPollConfig($code, $storeId = null)
    {
        $storeId = !is_null($storeId) ? $storeId : Mage::app()->getStore()->getId();

        return Mage::getStoreConfig(self::GIANTPOINTS_BEHAVIOR_POLL . $code, $storeId);
    }

    public function getPointsForPoll($storeId = null)
    {
        return $this->getPollConfig('poll', $storeId);
    }

    public function getStatusAfterPoll($store = null)
    {
        return $this->getPollConfig('poll_status', $store);
    }

    public function getPollLimitPerDay($storeId = null)
    {
        return $this->getPollConfig('poll_limit', $storeId);
    }
    /*===========================End poll config==============================*/

    /*===========================Birthday config==============================*/

    public function getBirthdayConfig($code, $storeId = null)
    {
        $storeId = !is_null($storeId) ? $storeId : Mage::app()->getStore()->getId();

        return Mage::getStoreConfig(self::GIANTPOINTS_BEHAVIOR_BIRTHDAY . $code, $storeId);
    }

    public function getPointsForBirthday($storeId = null)
    {
        return $this->getBirthdayConfig('birthday', $storeId);
    }

    public function getIsEnabledEmail($storeId = null)
    {
        return $this->getBirthdayConfig('enable_email', $storeId);
    }

    public function getPointsBirthdayTemplate($storeId = null)
    {
        return $this->getBirthdayConfig('email_template', $storeId);
    }

    public function getNotificationSender($storeId = null)
    {
        return $this->getBirthdayConfig('identity', $storeId);
    }
    /*===========================End birthday config=============================*/

    /*========================Facebook config=====================================*/

    public function getFacebookConfig($code, $storeId = null)
    {
        $storeId = !is_null($storeId) ? $storeId : Mage::app()->getStore()->getId();

        return Mage::getStoreConfig(self::GIANTPOINTS_BEHAVIOR_FB . $code, $storeId);
    }

    public function isShowFacebookLike($storeId = null)
    {
        return (bool)$this->getFacebookConfig('show_fb_like', $storeId);
    }

    public function isShowFacebookCount($storeId = null)
    {
        return (bool)$this->getFacebookConfig('show_fb_count', $storeId);
    }

    public function getFacebookPageView($storeId = null)
    {
        return explode(',', $this->getFacebookConfig('page_view', $storeId));
    }

    public function getPointsForFacebook($storeId = null)
    {
        return (int)$this->getFacebookConfig('fb_like_earn', $storeId);
    }

    public function getFacebookLimitPerDay($storeId = null)
    {
        return (int)$this->getFacebookConfig('fb_like_earn_limit', $storeId);
    }

    public function getFacebookWaitingTime($storeId = null)
    {
        return (int)$this->getFacebookConfig('minSecondsBetweenLikes', $storeId);
    }
    /*===========================End Facebook config==============================*/


    /*========================Twitter config=====================================*/

    public function getTwitterConfig($code, $storeId = null)
    {
        $storeId = !is_null($storeId) ? $storeId : Mage::app()->getStore()->getId();

        return Mage::getStoreConfig(self::GIANTPOINTS_BEHAVIOR_TW . $code, $storeId);
    }

    public function isShowTwitter($storeId = null)
    {
        return (bool)$this->getTwitterConfig('show_tw_tweet', $storeId);
    }

    public function isShowTwitterCount($storeId = null)
    {
        return (bool)$this->getTwitterConfig('show_tw_count', $storeId);
    }

    public function getTwitterPageView($storeId = null)
    {
        return explode(',', $this->getTwitterConfig('page_view', $storeId));
    }

    public function getPointsForTwitter($storeId = null)
    {
        return (int)$this->getTwitterConfig('tw_earn', $storeId);
    }

    public function getTwitterLimitPerDay($storeId = null)
    {
        return (int)$this->getTwitterConfig('tw_earn_limit', $storeId);
    }

    public function getTwitterWaitingTime($storeId = null)
    {
        return (int)$this->getTwitterConfig('minSecondsBetweenTweets', $storeId);
    }
    /*===========================End Twitter config==============================*/

    /*========================Google config=====================================*/

    public function getGoogleConfig($code, $storeId = null)
    {
        $storeId = !is_null($storeId) ? $storeId : Mage::app()->getStore()->getId();

        return Mage::getStoreConfig(self::GIANTPOINTS_BEHAVIOR_GG . $code, $storeId);
    }

    public function isShowGoogle($storeId = null)
    {
        return (bool)$this->getGoogleConfig('show_gg_button', $storeId);
    }

    public function isShowGoogleCount($storeId = null)
    {
        return (bool)$this->getGoogleConfig('show_gg_count', $storeId);
    }

    public function getGooglePageView($storeId = null)
    {
        return explode(',', $this->getGoogleConfig('page_view', $storeId));
    }

    public function getPointsForGoogle($storeId = null)
    {
        return (int)$this->getGoogleConfig('gg_earn', $storeId);
    }

    public function getGoogleLimitPerDay($storeId = null)
    {
        return (int)$this->getGoogleConfig('gg_earn_limit', $storeId);
    }

    public function getGoogleWaitingTime($storeId = null)
    {
        return (int)$this->getGoogleConfig('minSecondsBetweenPlusOnes', $storeId);
    }
    /*===========================End Google config==============================*/

    /*========================Purchase Share Config==========================*/
    public function getPurchaseShareConfig($code, $storeId = null)
    {
        return Mage::getStoreConfig(self::GIANTPOINTS_PURCHASE_SHARE . $code, $storeId);
    }

    public function isEnabledPurchaseShare($store = null)
    {
        return $this->getPurchaseShareConfig('enable', $store);
    }

    public function isEnabledPurchaseShareFacebook($store = null)
    {
        return $this->getPurchaseShareConfig('enable_facebook_share', $store);
    }

    public function isEnabledPurchaseShareTwitter($store = null)
    {
        return $this->getPurchaseShareConfig('enable_twitter_tweet', $store);
    }
    /*========================/Purchase Share Config==========================*/
    /**
     * Calculates correct amount to add to transaction according limit ($limitMax)
     *
     * @param int $currentAmount
     * @param int $amountToAdd
     * @param int $limitMax
     *
     * @return int
     */
    public function calculateNewAmount($currentAmount, $amountToAdd, $limitMax)
    {
        $newAmountToAdd = $amountToAdd;
        /* If current amount + amount to add > limitation, we need to change amount to add */
        if ($limitMax && $currentAmount + $amountToAdd > $limitMax && $amountToAdd > 0) {
            if ($limitMax > $currentAmount) {
                $newAmountToAdd = $limitMax - $currentAmount;
            } else {
                $newAmountToAdd = 0;
            }
        }

        return $newAmountToAdd;
    }

    /**
     * @param $reward_id
     * @param $action_code
     * @param $amount
     * @param $pointLimitForAction
     * @return int
     */
    public function limitAmountByDay($reward_id, $action_code, $amount, $pointLimitForAction)
    {

        $collection = Mage::getModel('giantpoints/transaction')
            ->getCollection()
            ->addFieldToFilter('reward_id', $reward_id)
            ->addFieldToFilter('action_code', $action_code)
            ->limitByDay(Mage::helper('giantpoints')->getMageTime());
        /* Current sum getting */
        $earnedPointToday = 0;
        foreach ($collection as $transaction) {
            $earnedPointToday += $transaction->getPointBalance();
        }

        return $this->calculateNewAmount($earnedPointToday, $amount, $pointLimitForAction);
    }

    protected function getFbLocales()
    {
        return array(
            'af_ZA', 'ar_AR', 'az_AZ', 'be_BY', 'bg_BG', 'bn_IN', 'bs_BA', 'ca_ES', 'cs_CZ',
            'cy_GB', 'da_DK', 'de_DE', 'el_GR', 'en_GB', 'en_PI', 'en_UD', 'en_US', 'eo_EO',
            'es_ES', 'es_LA', 'et_EE', 'eu_ES', 'fa_IR', 'fb_LT', 'fi_FI', 'fo_FO', 'fr_CA',
            'fr_FR', 'fy_NL', 'ga_IE', 'gl_ES', 'he_IL', 'hi_IN', 'hr_HR', 'hu_HU', 'hy_AM',
            'id_ID', 'is_IS', 'it_IT', 'ja_JP', 'ka_GE', 'km_KH', 'ko_KR', 'ku_TR', 'la_VA',
            'lt_LT', 'lv_LV', 'mk_MK', 'ml_IN', 'ms_MY', 'nb_NO', 'ne_NP', 'nl_NL', 'nn_NO',
            'pa_IN', 'pl_PL', 'ps_AF', 'pt_BR', 'pt_PT', 'ro_RO', 'ru_RU', 'sk_SK', 'sl_SI',
            'sq_AL', 'sr_RS', 'sv_SE', 'sw_KE', 'ta_IN', 'te_IN', 'th_TH', 'tl_PH', 'tr_TR',
            'uk_UA', 'vi_VN', 'zh_CN', 'zh_HK', 'zh_TW'
        );
    }

    public function getFacebookLocale()
    {
        if (isset($this->_locale)) {
            return $this->_locale;
        }

        $_locale = Mage::app()->getLocale()->getLocaleCode();

        if (!in_array($_locale, $this->getFbLocales())) {
            $_locale = 'en_US';
        }

        $this->_locale = $_locale;

        return $_locale;
    }

}