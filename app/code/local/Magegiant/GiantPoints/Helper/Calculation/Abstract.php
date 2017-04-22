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
class Magegiant_GiantPoints_Helper_Calculation_Abstract extends Mage_Core_Helper_Abstract
{
	protected $_quote = null;

	public function setQuote($quote)
	{
		$this->_quote = $quote;

		return $this;
	}

	/**
	 * get current checkout quote
	 *
	 * @return Mage_Sales_Model_Quote
	 */
	public function getQuote()
	{
		if(is_null($this->_quote)){
			if (Mage::app()->getStore()->isAdmin()) {
				$this->_quote = Mage::getSingleton('adminhtml/session_quote')->getQuote();
			}

			$this->_quote = Mage::getSingleton('checkout/session')->getQuote();
		}

		return $this->_quote;
	}

    public function getAddress($quote)
    {
        if ($quote->isVirtual()) {
            $address = $quote->getBillingAddress();
        } else {
            $address = $quote->getShippingAddress();
        }

        return $address;
    }

    /**
     * get max point per order
     *
     * @param null $store
     * @return int
     */
    public function getMaxPointsPerOrder($store = null)
    {
        $maxPerOrder = Mage::helper('giantpoints/config')->getMaxPointForOrder($store);
        if ($maxPerOrder > 0) {
            return $maxPerOrder;
        }

        return 0;
    }

    /**
     * @param null $item
     * @return mixed
     */
    public function getPointItemDiscount($item = null)
    {
        $container = new Varien_Object(array(
            'point_item_discount' => 0
        ));
        Mage::dispatchEvent('giantpoints_conversion_spending_point_item_discount', array(
            'item'      => $item,
            'container' => $container,
        ));

        return $container->getPointItemDiscount();
    }

    /**
     * @param null $item
     * @return mixed
     */
    public function getPointItemSpent($item = null)
    {
        $container = new Varien_Object(array(
            'point_item_spent' => 0
        ));
        Mage::dispatchEvent('giantpoints_conversion_spending_point_item_spent', array(
            'item'      => $item,
            'container' => $container,
        ));

        return $container->getPointItemSpent();
    }

    /**
     *
     * @return Varien_Object|false
     */
    public function getSpendingRateAsRule()
    {
        $customerGroupId = $this->getCustomerGroupId();
        $websiteId       = $this->getWebsiteId();
        $cacheKey        = "rate_as_rule:$customerGroupId:$websiteId";
        if (Mage::helper('giantpoints/cache')->hasCache($cacheKey)) {
            return Mage::helper('giantpoints/cache')->getCache($cacheKey);
        }
        $rate = Mage::getSingleton('giantpoints/rate')->getConversionRate(
            Magegiant_GiantPoints_Model_Rate::POINT_TO_MONEY, $customerGroupId, $websiteId
        );
        if ($rate && $rate->getId()) {
            /**
             * end update
             */
            Mage::helper('giantpoints/cache')->saveCache($cacheKey, new Varien_Object(array(
                'point_amount'  => $rate->getPoints(),
                'base_rate'     => $rate->getMoney(),
                'simple_action' => 'by_price',
                'id'            => 'rate',
            )));
        } else {
            Mage::helper('giantpoints/cache')->saveCache($cacheKey, false);
        }

        return Mage::helper('giantpoints/cache')->getCache($cacheKey);
    }


    /**
     * @param Varien_Object          $rule
     * @param Mage_Sales_Model_Quote $quote
     * @return int
     */
    public function getRuleMaxPointsForQuote($rule, $quote)
    {
        $cacheKey = "rule_max_points_for_quote:{$rule->getId()}";
        if (Mage::helper('giantpoints/cache')->hasCache($cacheKey)) {
            return Mage::helper('giantpoints/cache')->getCache($cacheKey);
        }
        if ($rule->getId() == 'rate') {
            if ($rule->getBaseRate() && $rule->getPointAmount()) {
                $quoteTotal = $this->getQuoteBaseTotal($quote);
                $maxPoints  = ceil(($quoteTotal - $this->getCheckedRuleSpentDiscount()) / $rule->getBaseRate()
                    ) * $rule->getPointAmount();
                if ($maxPerOrder = $this->getMaxPointsPerOrder($quote->getStoreId())) {
                    $maxPerOrder -= $this->getPointItemSpent();
                    $maxPerOrder -= $this->getCheckedRuleSpentPoint();
                    if ($maxPerOrder > 0) {
                        $maxPoints = min($maxPoints, $maxPerOrder);
                        $maxPoints = floor($maxPoints / $rule->getPointAmount()) * $rule->getPointAmount();
                    } else {
                        $maxPoints = 0;
                    }
                }
                Mage::helper('giantpoints/cache')->saveCache($cacheKey, $maxPoints);
            }
        } else {
            $container = new Varien_Object(array(
                'rule_max_points' => 0
            ));
            Mage::dispatchEvent('giantpoints_conversion_spending_rule_max_points', array(
                'rule'      => $rule,
                'quote'     => $quote,
                'container' => $container,
            ));
            Mage::helper('giantpoints/cache')->saveCache($cacheKey, $container->getRuleMaxPoints());
        }
        if (!Mage::helper('giantpoints/cache')->hasCache($cacheKey)) {
            Mage::helper('giantpoints/cache')->saveCache($cacheKey, 0);
        }

        return Mage::helper('giantpoints/cache')->getCache($cacheKey);
    }

    /**
     * @param $quote
     * @param $rule
     * @param $points
     * @return mixed
     */
    public function getQuoteRuleDiscount($quote, $rule, &$points)
    {
        $cacheKey = "quote_rule_discount:{$rule->getId()}:$points";

        if (Mage::helper('giantpoints/cache')->hasCache($cacheKey)) {
            return Mage::helper('giantpoints/cache')->getCache($cacheKey);
        }
        if ($rule->getId() == 'rate') {
            if ($rule->getBaseRate() && $rule->getPointAmount()) {
                $baseTotal = $this->getQuoteBaseTotal($quote) - $this->getCheckedRuleSpentDiscount();
                $maxPoints = ceil($baseTotal / $rule->getBaseRate()) * $rule->getPointAmount();
                if ($maxPerOrder = $this->getMaxPointsPerOrder($quote->getStoreId())) {
                    $maxPerOrder -= $this->getPointItemSpent();
                    $maxPerOrder -= $this->getCheckedRuleSpentPoint();
                    if ($maxPerOrder > 0) {
                        $maxPoints = min($maxPoints, $maxPerOrder);
                    } else {
                        $maxPoints = 0;
                    }
                }
                $points   = min($points, $maxPoints);
                $points   = floor($points / $rule->getPointAmount()) * $rule->getPointAmount();
                $discount = $points * $rule->getBaseRate() / $rule->getPointAmount();
                Mage::helper('giantpoints/cache')->saveCache($cacheKey, $discount);
            } else {
                $points = 0;
                Mage::helper('giantpoints/cache')->saveCache($cacheKey, 0);
            }
        } else {
            $container = new Varien_Object(array(
                'quote_rule_discount' => 0,
                'points'              => $points
            ));
            Mage::dispatchEvent('giantpoints_conversion_spending_quote_rule_discount', array(
                'rule'      => $rule,
                'quote'     => $quote,
                'container' => $container,
            ));
            $points = $container->getPoints();
            Mage::helper('giantpoints/cache')->saveCache($cacheKey, $container->getQuoteRuleDiscount());
        }

        return Mage::helper('giantpoints/cache')->getCache($cacheKey);
    }

    /**
     * @param      $quote
     * @param null $address
     * @return float|int
     */
    public function getQuoteBaseTotal($quote, $address = null)
    {
        $helperConfig = Mage::helper('giantpoints/config');
        $cacheKey     = 'quote_base_total';
        if (Mage::helper('giantpoints/cache')->hasCache($cacheKey)) {
            return Mage::helper('giantpoints/cache')->getCache($cacheKey);
        }
        if (is_null($address)) {
            if ($quote->isVirtual()) {
                $address = $quote->getBillingAddress();
            } else {
                $address = $quote->getShippingAddress();
            }
        }
        $baseTotal = 0;
        foreach ($address->getAllItems() as $item) {
            if ($item->getParentItemId())
                continue;
            if ($item->getHasChildren() && $item->isChildrenCalculated()) {
                foreach ($item->getChildren() as $child) {
                    $baseTotal += $this->getRowTotal($child);
                }
            } else {
                $baseTotal += $this->getRowTotal($item);
            }
        }
        if ($helperConfig->allowSpendPointForShippingFee()) {
            $shippingAmount = $address->getShippingAmountForDiscount();
            if ($shippingAmount !== null) {
                $baseShippingAmount = $address->getBaseShippingAmountForDiscount();
            } else {
                $baseShippingAmount = $address->getBaseShippingAmount();
            }
            $baseTotal += $baseShippingAmount - $address->getBaseShippingDiscountAmount();
        }

        $baseTotal -= $this->getPointItemDiscount();
        Mage::helper('giantpoints/cache')->saveCache($cacheKey, $baseTotal);

        return $baseTotal;
    }

    public function getRowTotal(Mage_Sales_Model_Quote_Item_Abstract $item)
    {
        $baseItemPrice = $item->getTotalQty() * $this->_getItemBasePrice($item) - $item->getBaseDiscountAmount();

        return $baseItemPrice;
    }

    protected function _getItemBasePrice($item)
    {
        $price = $item->getDiscountCalculationPrice();

        return ($price !== null) ? $item->getBaseDiscountCalculationPrice() : $item->getBaseCalculationPrice();
    }

    /**
     * Get Slider Rule discount
     *
     * @return float
     */
    public function getSliderRuleDiscount()
    {
        $session          = Mage::getSingleton('checkout/session');
        $rewardSalesRules = $session->getRewardSalesRules();
        if (is_array($rewardSalesRules) && isset($rewardSalesRules['base_discount']) && $session->getData('is_used_point')
        ) {
            return $rewardSalesRules['base_discount'];
        }

        return 0;
    }

    /**
     * @return int
     */
    public function getSliderRulePoint()
    {
        $session          = $this->getSession();
        $rewardSalesRules = $session->getRewardSalesRules();
        if (is_array($rewardSalesRules) && isset($rewardSalesRules['point_amount']) && $session->getData('is_used_point')
        ) {
            return $rewardSalesRules['point_amount'];
        }

        return 0;
    }

    /**
     * @return int
     */
    public function getTotalRulePoint()
    {
        return $this->getCheckedRuleSpentPoint() + $this->getSliderRulePoint();
    }

    /**
     * @return Varien_Object
     */
    public function getQuoteRule($ruleId = 'rate')
    {
        $cacheKey = "quote_rule_model:$ruleId";
        if (!Mage::helper('giantpoints/cache')->hasCache($cacheKey)) {
            if ($ruleId == 'rate') {
                Mage::helper('giantpoints/cache')->saveCache($cacheKey, $this->getSpendingRateAsRule());

                return Mage::helper('giantpoints/cache')->getCache($cacheKey);
            }
            $container = new Varien_Object(array(
                'quote_rule_model' => null
            ));
            Mage::dispatchEvent('giantpoints_conversion_spending_quote_rule_model', array(
                'container' => $container,
                'rule_id'   => $ruleId,
            ));
            Mage::helper('giantpoints/cache')->saveCache($cacheKey, $container->getQuoteRuleModel());
        }

        return Mage::helper('giantpoints/cache')->getCache($cacheKey);
    }

    /**
     *
     * @return int
     */
    public function getCustomerGroupId()
    {
		if($this->_quote){
			return $this->_quote->getCustomerGroupId();
		}

        return Mage::helper('giantpoints')->getCustomerGroupId();
    }

    /**
     *
     * @return int
     */
    public function getWebsiteId()
    {
		if($this->_quote){
			return $this->_quote->getStore()->getWebsiteId();
		}
        return Mage::helper('giantpoints')->getWebsiteId();
    }

    public function getSession()
    {
        return Mage::getSingleton('checkout/session');
    }

    /**
     * get Giant Points Cache
     *
     * @return Magegiant_GiantPoints_Helper_Cache
     */
    public function getCacheHelper()
    {
        return Mage::helper('giantpoints/cache');
    }

    /**
     *
     * @param $cacheKey
     * @return bool
     */
    public function hasCache($cacheKey)
    {
        return $this->getCacheHelper()->hasCache($cacheKey);
    }

    /**
     * @param      $cacheKey
     * @param null $value
     * @return $this
     */
    public function saveCache($cacheKey, $value = null)
    {
        return $this->getCacheHelper()->saveCache($cacheKey, $value);
    }

    /**
     * @param $cacheKey
     * @return mixed
     */
    public function getCache($cacheKey)
    {
        return $this->getCacheHelper()->getCache($cacheKey);
    }

}
