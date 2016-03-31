<?php
/**
 * Always nice to give cookies to customers.
 *
 * Changes:
 * - Add column to hold customer information
 * - Make user_id field nullable now that we support more entities.
 * - Add foreign key to customer_entity.
 * - Add unique key for cookie/customer_id combination
 * - Increase storage for cookie. Time teaches us hashes become obsolete.
 */
/** @var Mage_Core_Model_Resource_Setup $installer */
$installer = $this;
$installer->startSetup();

$tbl = $installer->getTable('twofactorauth/user_cookie');
$installer->getConnection()->addColumn(
    $tbl,
    'customer_id',
    array(
        'type'      => Varien_Db_Ddl_Table::TYPE_INTEGER,
        'length'    => null,
        'unsigned'  => true,
        'nullable'  => true,
        'comment'   => 'Link to customer for frontend authentication'
    )
);
$installer->getConnection()->addIndex(
    $tbl,
    'UNQ_CUSTOMER_ID_COOKIE_USER_ID_COOKIE', // Err...I think I'm respecting the naming convention...
    array('customer_id', 'cookie'),
    Varien_Db_Adapter_Interface::INDEX_TYPE_UNIQUE
);
$installer->getConnection()->modifyColumn(
    $tbl,
    'user_id',
    array(
        'type'      => Varien_Db_Ddl_Table::TYPE_INTEGER,
        'length'    => null,
        'unsigned'  => true,
        'nullable'  => true,
        'comment'   => 'Admin User ID'
    )
);
$installer->getConnection()->modifyColumn(
    $tbl,
    'cookie',
    array(
        'type'      => Varien_Db_Ddl_Table::TYPE_VARCHAR,
        'length'    => 128, // will work up to sha512
        'nullable'  => false, // Not sure why this was set to true, shouldn't be.
        'comment'   => 'Cookie value'
    )
);
$installer->getConnection()->addForeignKey(
    'FK_CUSTOMER_ID_CUSTOMER_ENTITY_ENTITY_ID',
    $tbl,
    'customer_id',
    $installer->getConnection()->getTableName('customer/customer'),
    'entity_id',
    Varien_Db_Adapter_Interface::FK_ACTION_CASCADE,
    Varien_Db_Adapter_Interface::FK_ACTION_CASCADE
);
$installer->endSetup();

