<?php
if (!defined ('TYPO3_MODE')) 	die ('Access denied.');

$_EXTCONF = unserialize($_EXTCONF);
$GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['tx_shscoutnetkalender']['ScoutnetSSID']=$_EXTCONF['ScoutnetSSID'];


  ## Extending TypoScript from static template uid=43 to set up userdefined tag:
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addTypoScript($_EXTKEY,'editorcfg','
	tt_content.CSS_editor.ch.tx_shscoutnetkalender_pi1 = < plugin.tx_shscoutnetkalender_pi1.CSS_editor
',43);


\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addPItoST43($_EXTKEY,'pi1/class.tx_shscoutnetkalender_pi1.php','_pi1','list_type',1);
?>
