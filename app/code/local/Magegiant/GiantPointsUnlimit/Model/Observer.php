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
 * @package     MageGiant_GiantPointsUnlimit
 * @copyright   Copyright (c) 2014 MageGiant (http://magegiant.com/)
 * @license     http://magegiant.com/license-agreement/
 */

/**
 * GiantPointsUnlimit Observer Model
 *
 * @category    MageGiant
 * @package     MageGiant_GiantPointsUnlimit
 * @author      MageGiant Developer
 */
class Magegiant_GiantPointsUnlimit_Model_Observer
{
    /**
     * process controller_action_predispatch event
     *
     * @return Magegiant_GiantPointsUnlimit_Model_Observer
     */
    public function giantpointsTransactionIncrement($observer)
    {
        $event = $observer->getEvent();
        if (!$event)
            return $this;
        $container = $event->getContainer();
        $container->setIncrement('ml');
        return $this;
    }
}