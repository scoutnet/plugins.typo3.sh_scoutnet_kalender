<?php
if (!defined ('TYPO3_MODE')) 	die ('Access denied.');


\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addStaticFile($_EXTKEY, 'Configuration/TypoScript', 'Scoutnet Kalender');

// add backend user modul
if (TYPO3_MODE === 'BE') {
	// add Wizicon
	\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addPageTSConfig('<INCLUDE_TYPOSCRIPT: source="FILE:EXT:sh_scoutnet_kalender/Configuration/TypoScript/pageTsConfig.ts">');


	\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addModulePath('scoutnet_kalender', \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath($_EXTKEY) . 'user_scoutnet/');
	\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addModule('user','scoutnet', '', \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath($_EXTKEY) . 'user_scoutnet/');

//	$GLOBALS['TYPO3_CONF_VARS']['BE']['AJAX']['Taskcenter::saveCollapseState']      = 'EXT:taskcenter/classes/class.tx_taskcenter_status.php:tx_taskcenter_status->saveCollapseState';
//	$GLOBALS['TYPO3_CONF_VARS']['BE']['AJAX']['Taskcenter::saveSortingState']       = 'EXT:taskcenter/classes/class.tx_taskcenter_status.php:tx_taskcenter_status->saveSortingState';
}

