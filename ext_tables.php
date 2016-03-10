<?php
if (!defined ('TYPO3_MODE')) die ('Access denied.');

/** @var string $_EXTKEY */
// add static file with configs
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addStaticFile($_EXTKEY, 'Configuration/TypoScript', 'Scoutnet Kalender');

if (TYPO3_MODE === 'BE') {
	// add Wizicon
	\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addPageTSConfig('<INCLUDE_TYPOSCRIPT: source="FILE:EXT:sh_scoutnet_kalender/Configuration/TypoScript/pageTsConfig.ts">');


    // add backend user modul
	\TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerModule(
		'ScoutNet.' . $_EXTKEY,
		'user',          // Main area
		'scoutnet',         // Name of the module
		'',             // Position of the module
		array(          // Allowed controller action combinations
			'Administration' => 'list, register, error, requestRights, edit, update, delete, remove, new, template, create',
		),
		array(          // Additional configuration
			'access'    => 'user,group',
			'icon'      => 'EXT:'.$_EXTKEY.'/ext_icon.gif',
			'labels'    => 'LLL:EXT:'.$_EXTKEY.'/Resources/Private/Language/locallang_modcalendar.xlf',
		)
	);
}

