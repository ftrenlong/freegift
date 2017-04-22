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
 * Giantpoints Grid Block
 *
 * @category    Magegiant
 * @package     Magegiant_GiantPoints
 * @author      Magegiant Developer
 */
class Magegiant_GiantPointsRefer_Block_Adminhtml_Invitation_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
    public function __construct()
    {
        parent::__construct();
        $this->setId('invitationGrid');
        $this->setDefaultSort('invitation_id');
        $this->setDefaultDir('DESC');
        $this->setUseAjax(true);
        $this->setSaveParametersInSession(true);
    }

    /**
     * prepare collection for block to display
     *
     */
    protected function _prepareCollection()
    {
        $collection = Mage::getResourceModel('giantpointsrefer/invitation_collection');
        $this->setCollection($collection);
        parent::_prepareCollection();

        return $this;
    }

    /**
     * prepare columns for this grid
     *
     * @return Magegiant_GiantPointsRefer_Block_Adminhtml_Giantpoints_Grid
     */
    protected function _prepareColumns()
    {
        $this->addColumn('invitation_id', array(
            'header' => Mage::helper('giantpoints')->__('ID'),
            'align'  => 'right',
            'width'  => '50px',
            'index'  => 'invitation_id',
            'type'   => 'number',
        ));


        $this->addColumn('referral_id', array(
            'header'   => Mage::helper('giantpoints')->__('Referral Email'),
            'index'    => 'referral_id',
            'type'     => 'text',
            'renderer' => 'giantpointsrefer/adminhtml_invitation_renderer_customer',
        ));

        $this->addColumn('email', array(
            'header' => Mage::helper('giantpoints')->__('Invited Email'),
            'index'  => 'email',
            'type'   => 'text',
        ));
        $this->addColumn('store_id',array(
                'header'  => Mage::helper('giantpoints')->__('Store View'),
                'index'   => 'store_id',
                'type'    => 'options',
                'options' => Mage::getModel('adminhtml/system_store')->getStoreOptionHash(true),
            )
        );
        $this->addColumn('status', array(
            'header'                    => Mage::helper('giantpoints')->__('Status'),
            'align'                     => 'left',
            'index'                     => 'status',
            'type'                      => 'options',
            'width'                     => '200px',
            'sortable'                  => false,
            'options'                   => Mage::getModel('giantpointsrefer/invitation')->getStatusHash(),
            'filter_condition_callback' => array($this, 'filterCallback'),
        ));

        return parent::_prepareColumns();
    }

    /**
     * prepare mass action for this grid
     *
     * @return Magegiant_GiantPointsRefer_Block_Adminhtml_Giantpoints_Grid
     */
    protected function _prepareMassaction()
    {
        $this->setMassactionIdField('invitation_id');
        $this->getMassactionBlock()->setFormFieldName('invitation_id');

        $this->getMassactionBlock()->addItem('delete', array(
            'label'   => Mage::helper('giantpoints')->__('Delete'),
            'url'     => $this->getUrl('*/*/massDelete'),
            'confirm' => Mage::helper('giantpoints')->__('Are you sure?')
        ));

        return $this;
    }

    /**
     * @return string
     */
    public function getGridUrl()
    {
        return $this->getUrl('*/*/grid', array('_current' => true));
    }

    /**
     * process filter call back
     *
     * @param $collection
     * @param $column
     */
    public function filterCallback($collection, $column)
    {
        $value = $column->getFilter()->getValue();
        if (!is_null(@$value)) {
            $collection->addFieldToFilter($column->getIndex(), array('finset' => $value));
        }
    }
}