<?php
$installer = $this;
$installer->startSetup();

if (version_compare(Mage::getVersion(), '1.5.0', '>=')) {
    $setup = new Mage_Sales_Model_Resource_Setup('core_setup');
} else {
    $setup = new Mage_Eav_Model_Entity_Setup('core_setup');
}

$installer->getConnection()
    ->addColumn(
        $installer->getTable('sales/quote'), 'shipping_description', array(
            'type'      => Varien_Db_Ddl_Table::TYPE_TEXT,
            'length'    => 1500,
            'nullable'  => true,
            'default'   => null,
            'comment'   => 'Shipping description'
        )
    );

$installer->getConnection()        
    ->addColumn(
        $installer->getTable('sales/quote'), 'pickup_hour', array(
            'type'      => Varien_Db_Ddl_Table::TYPE_TEXT,
            'length'    => 255,
            'nullable'  => true,
            'default'   => null,
            'comment'   => 'Pickup hour'
        )
    );
$setup->addAttribute('quote', 'pickup_hour', array('type' => 'static', 'visible' => false));

$installer->getConnection()
    ->addColumn(
        $installer->getTable('sales/quote'), 'pickup_day', array(
            'type'      => Varien_Db_Ddl_Table::TYPE_TEXT,
            'length'    => 255,
            'nullable'  => true,
            'default'   => null,
            'comment'   => 'Pickup day'
        )
    );
$setup->addAttribute('quote', 'pickup_day', array('type' => 'static', 'visible' => false));

$installer->getConnection()
    ->modifyColumn(
        $installer->getTable('sales/order'), 'shipping_description', array(
            'type'      => Varien_Db_Ddl_Table::TYPE_TEXT,
            'length'    => 1500
        )
    );

$installer->getConnection()
    ->addColumn(
        $installer->getTable('sales/order'), 'pickup_hour', array(
            'type'      => Varien_Db_Ddl_Table::TYPE_TEXT,
            'length'    => 255,
            'nullable'  => true,
            'default'   => null,
            'comment'   => 'Pickup hour'
        )
    );
$setup->addAttribute('order', 'pickup_hour', array('type' => 'static', 'visible' => false));

$installer->getConnection()
    ->addColumn(
        $installer->getTable('sales/order'), 'pickup_day', array(
            'type'      => Varien_Db_Ddl_Table::TYPE_TEXT,
            'length'    => 255,
            'nullable'  => true,
            'default'   => null,
            'comment'   => 'Pickup day'
        )
    );
$setup->addAttribute('order', 'pickup_day', array('type' => 'static', 'visible' => false));

$installer->endSetup();
