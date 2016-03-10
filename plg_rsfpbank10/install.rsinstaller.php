<?php
defined( '_JEXEC' ) or die( 'Restricted access' );

$plg_installer = new JInstaller();

$plg_installer->install($this->parent->getPath('source').DS.'rsfp10bank');

require_once(JPATH_ADMINISTRATOR.DS.'components'.DS.'com_rsform'.DS.'helpers'.DS.'rsform.php');

if (RSFormProHelper::isJ16())
	$this->parent->parseSQLFiles($this->manifest->install->sql);