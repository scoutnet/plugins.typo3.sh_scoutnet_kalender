<?php
if (!defined('TYPO3_MODE')) {
	die('Access denied.');
}

// be_user fileds
$tempColumns = Array (
	'tx_shscoutnetkalender_scoutnet_username' => Array (
		'exclude' => 1,
		'label' => 'LLL:EXT:sh_scoutnet_kalender/locallang_db.xml:be_users.tx_shscoutnetkalender_scoutnet_username',
		'config' => Array (
			'type' => 'input',
			'size' => '255',
		)
	),
	'tx_shscoutnetkalender_scoutnet_apikey' => Array (
		'exclude' => 1,
		'label' => 'LLL:EXT:sh_scoutnet_kalender/locallang_db.xml:be_users.tx_shscoutnetkalender_scoutnet_apikey',
		'config' => Array (
			'type' => 'input',
			'size' => '255',
		)
	),
);


\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addTCAcolumns('be_users',$tempColumns,1);
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addToAllTCAtypes('be_users','--div--;LLL:EXT:sh_scoutnet_kalender/locallang_db.xml:be_users.tx_shscoutnetkalender_scounet_tab, tx_shscoutnetkalender_scoutnet_username, tx_shscoutnetkalender_scoutnet_apikey');
