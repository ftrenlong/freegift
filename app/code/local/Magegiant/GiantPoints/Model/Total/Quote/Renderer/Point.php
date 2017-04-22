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
class Magegiant_GiantPoints_Model_Total_Quote_Renderer_Point
    extends Mage_Sales_Model_Quote_Address_Total_Abstract
{
    public function __construct()
    {
        $this->setCode('giantpoints_renderer_point');
    }

    public function collect(Mage_Sales_Model_Quote_Address $address)
    {
		$this->_resetPointData($address);

		foreach ($address->getAllItems() as $item) {
			if ($item->getParentItemId())
				continue;
			if ($item->getHasChildren() && $item->isChildrenCalculated()) {
				foreach ($item->getChildren() as $child) {
					$this->_resetPointData($child);
				}
			} elseif ($item->getProduct()) {
				$this->_resetPointData($item);
			}
		}

        return $this;
    }

	protected function _resetPointData($object)
	{
		$object->addData(array(
			'giantpoints_spent'               => 0,
			'giantpoints_base_discount'          => 0,
			'giantpoints_discount'      => 0,
			'giantpoints_earn' => 0,
		));

		return $this;
	}

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
