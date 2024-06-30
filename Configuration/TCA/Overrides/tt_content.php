<?php

use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Extbase\Utility\ExtensionUtility;

$GLOBALS['TCA']['tt_content']['types']['list']['subtypes_excludelist']['shscoutnetkalender_calendar'] = 'pages, recursive';
$GLOBALS['TCA']['tt_content']['types']['list']['subtypes_addlist']['shscoutnetkalender_calendar'] = 'pi_flexform';
ExtensionManagementUtility::addPiFlexFormValue('shscoutnetkalender_calendar', 'FILE:EXT:sh_scoutnet_kalender/Configuration/FlexForms/Calendar.xml');

ExtensionUtility::registerPlugin(
    'sh_scoutnet_kalender',
    'Calendar',
    'LLL:EXT:sh_scoutnet_kalender/Resources/Private/Language/locallang_csh_tt_content.xlf:tt_content.pi_flexform.sheet_scoutnet'
);
