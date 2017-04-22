<?php
/**
 * Magegiant
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Magegiant.com license that is
 * available through the world-wide-web at this URL:
 * https://magegiant.com/license-agreement/
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category    Magegiant
 * @package     Magegiant_GiantPoints
 * @copyright   Copyright (c) 2014 Magegiant (https://magegiant.com/)
 * @license     https://magegiant.com/license-agreement/
 */

/**
 * GiantPoints Spend for Order by Point Model
 *
 * @category    Magegiant
 * @package     Magegiant_GiantPoints
 * @author      Magegiant Developer
 */
class Magegiant_GiantPoints_Model_Total_Quote_Earning
    extends Mage_Sales_Model_Quote_Address_Total_Abstract
{
    protected $_calculator;

    public function __construct()
    {
        $this->_calculator = Mage::getSingleton('giantpoints/calculation_earning');
    }

    /**
     * @param type $observer
     */

    public function salesQuoteCollectTotalsAfter($observer)
    {
        $quote      = $observer->getQuote();
        $allAddress = $quote->getAllAddresses();
        foreach ($allAddress as $address) {
            $addressType = $address->getAddressType();
            if (!$quote->isVirtual() && $addressType == 'billing') {
                continue;
            }
            $this->_calculator->collect($address, $quote);
        }
    }


    /**
     * fetch
     *
     * @param Mage_Sales_Model_Quote_Address $address
     * @return $this|array
     */
    public function fetch(Mage_Sales_Model_Quote_Address $address)
    {
        $address->addTotal(array(
            'code'  => $this->getCode(),
            'title' => '1',
            'value' => 1,
        ));

        return $this;
    }
}
