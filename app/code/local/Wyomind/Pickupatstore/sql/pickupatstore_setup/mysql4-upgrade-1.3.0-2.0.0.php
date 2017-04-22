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
        $installer->getTable('sales/quote'), 'pickup_datetime', array(
            'type'      => Varien_Db_Ddl_Table::TYPE_DATETIME,
            'nullable'  => true,
            'default'   => null,
            'comment'   => 'Pickup datetime'
        )
    );
$setup->addAttribute('quote', 'pickup_datetime', array('type' => 'static', 'visible' => false));

$installer->getConnection()
    ->addColumn(
        $installer->getTable('sales/order'), 'pickup_datetime', array(
            'type'      => Varien_Db_Ddl_Table::TYPE_DATETIME,
            'nullable'  => true,
            'default'   => null,
            'comment'   => 'Pickup datetime'
        )
    );
$setup->addAttribute('order', 'pickup_datetime', array('type' => 'static', 'visible' => false));

$installer->getConnection()
    ->addColumn(
        $installer->getTable('sales/order_grid'), 'pickup_datetime', array(
            'type'      => Varien_Db_Ddl_Table::TYPE_DATETIME,
            'nullable'  => true,
            'default'   => null,
            'comment'   => 'Pickup datetime'
        )
    );
$setup->addAttribute('order', 'pickup_datetime', array('type' => 'static', 'visible' => false));

$installer->endSetup();
