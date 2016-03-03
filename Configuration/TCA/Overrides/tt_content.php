<?php 
if (!defined('TYPO3_MODE')) {
	die('Access denied.');
}

$GLOBALS['TCA']['tt_content']['types']['list']['subtypes_excludelist']['shscoutnetkalender_calendar']='layout,select_key';
$GLOBALS['TCA']['tt_content']['types']['list']['subtypes_addlist']['shscoutnetkalender_calendar']='pi_flexform';
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addPiFlexFormValue('shscoutnetkalender_calendar', 'FILE:EXT:sh_scoutnet_kalender/Configuration/FlexForms/Calendar.xml');

\TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerPlugin('sh_scoutnet_kalender', 'Calendar', 'LLL:EXT:sh_scoutnet_kalender/Resources/Private/Language/locallang_be.xlf:plugin.calendar');


