<?php
if (!defined ('TYPO3_MODE')) 	die ('Access denied.');

$tempColumns = Array (
	"tx_shscoutnetkalender_ids" => Array (		
		"exclude" => 1,		
		"label" => "LLL:EXT:sh_scoutnet_kalender/locallang_db.xml:tt_content.tx_shscoutnetkalender_ids",		
		"config" => Array (
			"type" => "input",	
			"size" => "30",	
			"checkbox" => "",	
			"eval" => "required,trim,nospace",
		)
	),
	"tx_shscoutnetkalender_optids" => Array (		
		"exclude" => 1,		
		"label" => "LLL:EXT:sh_scoutnet_kalender/locallang_db.xml:tt_content.tx_shscoutnetkalender_optids",		
		"config" => Array (
			"type" => "input",	
			"size" => "30",	
			"checkbox" => "",	
			"eval" => "trim,nospace",
		)
	),
	"tx_shscoutnetkalender_kat_ids" => Array (		
		"exclude" => 1,		
		"label" => "LLL:EXT:sh_scoutnet_kalender/locallang_db.xml:tt_content.tx_shscoutnetkalender_kat_ids",		
		"config" => Array (
			"type" => "input",	
			"size" => "30",	
			"checkbox" => "",	
			"eval" => "trim,nospace",
		)
	),
	"tx_shscoutnetkalender_stufen_ids" => Array (		
		"exclude" => 1,		
		"label" => "LLL:EXT:sh_scoutnet_kalender/locallang_db.xml:tt_content.tx_shscoutnetkalender_stufen_ids",		
		"config" => Array (
			"type" => "input",	
			"size" => "30",	
			"checkbox" => "",	
			"eval" => "trim,nospace",
		)
	),
);


t3lib_div::loadTCA("tt_content");
t3lib_extMgm::addTCAcolumns("tt_content",$tempColumns,1);

t3lib_div::loadTCA('tt_content');
$TCA['tt_content']['types']['list']['subtypes_excludelist'][$_EXTKEY.'_pi1']='layout,select_key';
$TCA['tt_content']['types']['list']['subtypes_addlist'][$_EXTKEY.'_pi1']='tx_shscoutnetkalender_ids,tx_shscoutnetkalender_optids,tx_shscoutnetkalender_kat_ids,tx_shscoutnetkalender_stufen_ids';


t3lib_extMgm::addPlugin(Array('LLL:EXT:sh_scoutnet_kalender/locallang_db.xml:tt_content.list_type_pi1', $_EXTKEY.'_pi1'),'list_type');


t3lib_extMgm::addStaticFile($_EXTKEY,"pi1/static/","Scoutnet calendar");



if (TYPO3_MODE == 'BE') {
	$TBE_MODULES_EXT["xMOD_db_new_content_el"]["addElClasses"]["tx_shscoutnetkalender_pi1_wizicon"] = t3lib_extMgm::extPath($_EXTKEY).'pi1/class.tx_shscoutnetkalender_pi1_wizicon.php';

	//t3lib_extMgm::addModulePath('tools_txscoutnetkalenderM1', t3lib_extMgm::extPath($_EXTKEY) . 'editor/');
	t3lib_extMgm::addModule('user','scoutnet', '', t3lib_extMgm::extPath($_EXTKEY) . 'editor/');

//	$GLOBALS['TYPO3_CONF_VARS']['BE']['AJAX']['Taskcenter::saveCollapseState']      = 'EXT:taskcenter/classes/class.tx_taskcenter_status.php:tx_taskcenter_status->saveCollapseState';
//	$GLOBALS['TYPO3_CONF_VARS']['BE']['AJAX']['Taskcenter::saveSortingState']       = 'EXT:taskcenter/classes/class.tx_taskcenter_status.php:tx_taskcenter_status->saveSortingState';
}



?>
