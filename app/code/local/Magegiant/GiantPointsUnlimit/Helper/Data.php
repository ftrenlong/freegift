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
 * GiantPointsUnlimit Helper
 *
 * @category    MageGiant
 * @package     MageGiant_GiantPointsUnlimit
 * @author      MageGiant Developer
 */
class Magegiant_GiantPointsUnlimit_Helper_Data extends Mage_Core_Helper_Abstract
{
    const XML_PATH_ENABLED = 'giantpointsunlimit/general/is_enabled';
    const XML_PATH_GENERAL_CONFIG = 'giantpointsunlimit/general/';
    public function isEnabled($storeId=null){
        if(!$storeId){
            $storeId= Mage::app()->getStore()->getId();
        }
        return Mage::getStoreConfig(self::XML_PATH_ENABLED,$storeId);
    }
    public function getGeneralConfig($name,$storeId=null){
        if(!$storeId){
            $storeId= Mage::app()->getStore()->getId();
        }
        return Mage::getStoreConfig(self::XML_PATH_GENERAL_CONFIG.$name,$storeId);

    }

}