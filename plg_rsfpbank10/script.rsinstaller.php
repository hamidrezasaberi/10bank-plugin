<?php
defined('_JEXEC') or die('Restricted access');

class com_rsinstallerInstallerScript
{
	function postflight($type, $parent)
	{	
		if ($type == 'install' || $type == 'update')
		{
			$db =& JFactory::getDBO();
			
			$db->setQuery("UPDATE #__extensions SET `enabled`='0' WHERE `element`='com_rsinstaller' AND `type`='component'");
			$db->query();
			
			return true;
		}
	}
}