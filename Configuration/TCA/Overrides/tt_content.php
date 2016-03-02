<?php 
if (!defined('TYPO3_MODE')) {
	die('Access denied.');
}

// tt_content add fileds
$tempColumns = Array (
	"tx_shscoutnetkalender_ids" => Array (		
		"exclude" => 1,		
		"label" => "LLL:EXT:sh_scoutnet_kalender/Resources/Private/Language/locallang_be.xlf:tt_content.tx_shscoutnetkalender_ids",
		"config" => Array (
			"type" => "input",	
			"size" => "30",	
			"checkbox" => "",	
			"eval" => "required,trim,nospace",
		)
	),
	"tx_shscoutnetkalender_optids" => Array (		
		"exclude" => 1,		
		"label" => "LLL:EXT:sh_scoutnet_kalender/Resources/Private/Language/locallang_be.xlf:tt_content.tx_shscoutnetkalender_optids",
		"config" => Array (
			"type" => "input",	
			"size" => "30",	
			"checkbox" => "",	
			"eval" => "trim,nospace",
		)
	),
	"tx_shscoutnetkalender_kat_ids" => Array (		
		"exclude" => 1,		
		"label" => "LLL:EXT:sh_scoutnet_kalender/Resources/Private/Language/locallang_be.xlf:tt_content.tx_shscoutnetkalender_kat_ids",
		"config" => Array (
			"type" => "input",	
			"size" => "30",	
			"checkbox" => "",	
			"eval" => "trim,nospace",
		)
	),
	"tx_shscoutnetkalender_stufen_ids" => Array (		
		"exclude" => 1,		
		"label" => "LLL:EXT:sh_scoutnet_kalender/Resources/Private/Language/locallang_be.xlf:tt_content.tx_shscoutnetkalender_stufen_ids",
		"config" => Array (
			"type" => "input",	
			"size" => "30",	
			"checkbox" => "",	
			"eval" => "trim,nospace",
		)
	),
);

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addTCAcolumns("tt_content",$tempColumns,1);

$GLOBALS['TCA']['tt_content']['types']['list']['subtypes_excludelist']['shscoutnetkalender_calendar']='layout,select_key';
$GLOBALS['TCA']['tt_content']['types']['list']['subtypes_addlist']['shscoutnetkalender_calendar']='tx_shscoutnetkalender_ids,tx_shscoutnetkalender_optids,tx_shscoutnetkalender_kat_ids,tx_shscoutnetkalender_stufen_ids';
//\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addPiFlexFormValue('shscoutnetkalender_calendar', 'FILE:EXT:sh_scoutnet_kalender/Configuration/FlexForms/Calendar.xml');

\TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerPlugin('sh_scoutnet_kalender', 'Calendar', 'LLL:EXT:sh_scoutnet_kalender/Resources/Private/Language/locallang_be.xlf:plugin.calendar');


