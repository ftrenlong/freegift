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
class Magegiant_GiantPoints_Helper_Cache extends Mage_Core_Helper_Abstract
{
    /**
     *
     * @var array
     */
    protected $_giantCache = array();

    /**
     * check cache is existed or not
     *
     * @param string $cacheKey
     * @return boolean
     */
    public function hasCache($cacheKey)
    {
        if (array_key_exists($cacheKey, $this->_giantCache)) {
            return true;
        }

        return false;
    }

    /**
     * @param      $cacheKey
     * @param null $value
     * @return $this
     */
    public function saveCache($cacheKey, $value = null)
    {
        $this->_giantCache[$cacheKey] = $value;

        return $this;
    }

    /**
     * get cache value by cache key
     *
     * @param  $cacheKey
     * @return mixed
     */
    public function getCache($cacheKey)
    {
        if (array_key_exists($cacheKey, $this->_giantCache)) {
            return $this->_giantCache[$cacheKey];
        }

        return null;
    }
}