<?php

/**
 * MageBridge.
 *
 * @author Yireo
 * @copyright Copyright 2016
 * @license Open Source License
 *
 * @link https://www.yireo.com
 */
/** @var Mage_Core_Model_Resource_Setup $this */
$installer = $this;
$installer->startSetup();
$installer->run("
CREATE TABLE IF NOT EXISTS `{$installer->getTable('magebridge_customer_joomla')}` (
    `customer_id` int(10) unsigned default NULL,
    `joomla_id` int(10) unsigned default NULL,
    `website_id` int(10) unsigned default NULL,
    PRIMARY KEY  (`customer_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='Relation between Joomla user and Magento customer';
");
$installer->endSetup();
