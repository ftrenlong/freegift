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
class Magegiant_GiantPoints_Model_Transaction_Status
{
    const STATUS_PENDING = 1;
    const STATUS_COMPLETED = 2;
    const STATUS_CANCELED = 3;
    const STATUS_EXPIRED = 4;

    /**
     * get transaction status as hash array
     *
     * @return array
     */
    public function getStatusHash()
    {
        return array(
            self::STATUS_PENDING   => Mage::helper('giantpoints')->__('Pending'),
            self::STATUS_COMPLETED => Mage::helper('giantpoints')->__('Completed'),
            self::STATUS_CANCELED  => Mage::helper('giantpoints')->__('Canceled'),
            self::STATUS_EXPIRED   => Mage::helper('giantpoints')->__('Expired'),
        );
    }

    /**
     * get transaction status as hash array
     *
     * @return array
     */
    public function getStatusArray()
    {
        $options = array();
        foreach ($this->getStatusHash() as $value => $label) {
            $options[] = array(
                'value' => $value,
                'label' => $label,
            );
        }

        return $options;
    }
}
