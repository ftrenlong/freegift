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
class Magegiant_GiantPointsRefer_Block_Customer_Referral_Invitation extends Magegiant_GiantPointsRefer_Block_Customer_Referral_General
{
    /**
     * Returns collection of invited that are assigned to current customer
     *
     */
    public function getCollection()
    {
        if (!$this->getData('collection')) {
            $this->setCollection(
                Mage::getModel('giantpointsrefer/invitation')->getCollection()->addCustomerFilter($this->getCustomer())
            );
            $this->updateNumeralStatuses();
        }

        return $this->getData('collection');
    }

    public function _prepareLayout()
    {
        parent::_prepareLayout();
        $pager = $this->getLayout()->createBlock('page/html_pager', 'invitations_pager')
            ->setCollection($this->getCollection());
        $this->setChild('invitations_pager', $pager);

        return $this;
    }

    /**
     * get pager
     *
     * @return string
     */
    public function getPagerHtml()
    {
        return $this->getChildHtml('invitations_pager');
    }

    /**
     * Returns toolbar with pages and so on
     *
     * @return Mage_Page_Block_Html_Pager
     */
    public function getToolbarHtml()
    {
        return $this->getChildHtml('toolbar');
    }

    /**
     * Check if customer has invitation list
     *
     * @return bool
     */
    public function hasInvitees()
    {
        return $this->getCollection()->count() > 0;
    }

    /**
     * Change status codes in human readable format
     *
     * @return void
     */
    private function updateNumeralStatuses()
    {

        $data = $this->getData('collection');
        foreach ($data as $item) {
            switch ($item->getStatus()) {
                case Magegiant_GiantPointsRefer_Model_Invitation::INVITATION_NEW:
                    $item->setStatus($this->__('Email wasn\'t sent. Please try later. '));
                    break;
                case Magegiant_GiantPointsRefer_Model_Invitation::INVITATION_SENT:
                    $item->setStatus($this->__('Invitation sent'));
                    break;
                case Magegiant_GiantPointsRefer_Model_Invitation::INVITATION_ACCEPTED:
                    $item->setStatus($this->__('Invitation accepted'));
                    break;
                default:
                    break;
            }
        }
    }
}