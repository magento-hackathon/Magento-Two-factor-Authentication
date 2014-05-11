<?php

$installer = $this;
$installer->startSetup();

$setup = new Mage_Eav_Model_Entity_Setup('core_setup');

$setup->addAttribute('customer', 'twofactorauth_enabled', array(
    'label'         => 'Enabled',
    'type'          => 'int',
    'input'         => 'text',
    'visible'       => true,
    'required'      => false,
    'position'      => 9999,
));

$installer->endSetup();