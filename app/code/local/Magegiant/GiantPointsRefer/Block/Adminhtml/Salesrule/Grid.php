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
class Magegiant_GiantPointsRefer_Block_Adminhtml_Salesrule_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
    public function __construct()
    {
        parent::__construct();
        $this->setId('rule_id');
        $this->setDefaultSort('name');
        $this->setDefaultDir('ASC');
        $this->setSaveParametersInSession(true);
    }

    protected function _prepareCollection()
    {
        $collection = Mage::getModel('giantpointsrefer/salesrule')->getCollection();
        $this->setCollection($collection);

        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {
        $helper = Mage::helper('giantpointsrefer');
        $this->addColumn(
            'rule_id',
            array(
                'header' => $helper->__('ID'),
                'align'  => 'right',
                'width'  => '50px',
                'index'  => 'rule_id',
                'type'   => 'number',
            )
        );

        $this->addColumn(
            'name',
            array(
                'header' => $helper->__('Name'),
                'align'  => 'left',
                'index'  => 'name',
            )
        );
        if (!Mage::app()->isSingleStoreMode()) {
            $this->addColumn('website_ids', array(
                'header'                    => Mage::helper('giantpoints')->__('Website'),
                'align'                     => 'left',
                'width'                     => '200px',
                'type'                      => 'options',
                'options'                   => Mage::getSingleton('adminhtml/system_store')->getWebsiteOptionHash(),
                'index'                     => 'website_ids',
                'filter_condition_callback' => array($this, 'filterCallback'),
                'sortable'                  => false,
            ));
        }

        $this->addColumn('customer_group_ids', array(
            'header'                    => Mage::helper('giantpoints')->__('Customer Groups'),
            'align'                     => 'left',
            'index'                     => 'customer_group_ids',
            'type'                      => 'options',
            'width'                     => '200px',
            'sortable'                  => false,
            'options'                   => Mage::getResourceModel('customer/group_collection')
                ->load()
                ->toOptionHash(),
            'filter_condition_callback' => array($this, 'filterCallback'),
        ));
        $this->addColumn(
            'from_date',
            array(
                'header' => $helper->__('Date Start'),
                'align'  => 'left',
                'index'  => 'from_date',
                'type'   => 'date',
            )
        );

        $this->addColumn(
            'to_date',
            array(
                'header' => $helper->__('Date Expire'),
                'align'  => 'left',
                'index'  => 'to_date',
                'type'   => 'date',
            )
        );

        $this->addColumn(
            'priority',
            array(
                'header' => $helper->__('Priority'),
                'align'  => 'left',
                'index'  => 'priority',
                'type'   => 'number',
            )
        );

        $this->addColumn(
            'is_active',
            array(
                'header'  => $helper->__('Status'),
                'align'   => 'left',
                'width'   => '100px',
                'index'   => 'is_active',
                'type'    => 'options',
                'options' => array(
                    1 => 'Active',
                    0 => 'Inactive',
                ),
            )
        );

        return parent::_prepareColumns();
    }

    protected function _prepareMassaction()
    {
        $this->setMassactionIdField('rule_id');
        $this->getMassactionBlock()->setFormFieldName('rule_ids');

        $this->getMassactionBlock()->addItem(
            'delete',
            array(
                'label' => Mage::helper('giantpointsrefer')->__('Delete'),
                'url'   => $this->getUrl('*/*/massDelete'),
            )
        );

        $this->getMassactionBlock()->addItem(
            'activate',
            array(
                'label' => Mage::helper('giantpointsrefer')->__('Activate'),
                'url'   => $this->getUrl('*/*/massActivate'),
            )
        );

        $this->getMassactionBlock()->addItem(
            'deactivate',
            array(
                'label' => Mage::helper('giantpointsrefer')->__('Inactivate'),
                'url'   => $this->getUrl('*/*/massDeactivate'),
            )
        );

        return $this;
    }

    public function filterCallback($collection, $column)
    {
        $value = $column->getFilter()->getValue();
        if (is_null(@$value))
            return;
        else
            $collection->addFieldToFilter($column->getIndex(), array('finset' => $value));
    }

    public function getRowUrl($row)
    {
        return $this->getUrl('*/*/edit', array(
                'id' => $row->getId()
            )
        );
    }
}