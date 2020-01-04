<?php

use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Extbase\Utility\ExtensionUtility;

if (!defined ('TYPO3_MODE')) die ('Access denied.');

if (TYPO3_MODE === 'BE') {
	// add Wizicon
	ExtensionManagementUtility::addPageTSConfig('<INCLUDE_TYPOSCRIPT: source="FILE:EXT:sh_scoutnet_kalender/Configuration/TypoScript/pageTsConfig.ts">');


    // add backend user modul
	ExtensionUtility::registerModule(
		'ScoutNet.sh_scoutnet_kalender',
		'user',          // Main area
		'scoutnet',         // Name of the module
		'',             // Position of the module
		array(          // Allowed controller action combinations
			'Administration' => 'list, register, error, requestRights, edit, update, delete, remove, new, template, create',
		),
		array(          // Additional configuration
			'access'    => 'user,group',
			'icon'      => 'EXT:sh_scoutnet_kalender/ext_icon.gif',
			'labels'    => 'LLL:EXT:sh_scoutnet_kalender/Resources/Private/Language/locallang_modcalendar.xlf',
		)
	);
}

