<?php

/***************************************************************
 * Extension Manager/Repository config file for ext "sh_scoutnet_kalender".
 *
 * Auto generated 26-12-2017 14:03
 *
 * Manual updates:
 * Only the data in the array - everything else is removed by next
 * writing. "version" and "dependencies" must not be touched!
 ***************************************************************/

$EM_CONF[$_EXTKEY] = array (
	'title' => 'Official Scoutnet Calendar Plugin',
	'description' => 'Official Typo3 plugin to display scoutnet.de calendar',
	'category' => 'plugin',
	'shy' => true,
	'version' => '3.0.7',
	'priority' => NULL,
	'loadOrder' => NULL,
	'module' => NULL,
	'state' => 'beta',
	'uploadfolder' => true,
	'createDirs' => '',
	'modify_tables' => NULL,
	'clearcacheonload' => false,
	'lockType' => NULL,
	'author' => 'Stefan "Mütze" Horst',
	'author_email' => 'muetze@scoutnet.de',
	'author_company' => NULL,
	'CGLcompliance' => NULL,
	'CGLcompliance_note' => NULL,
	'constraints' => 
	array (
		'depends' => 
		array (
			'typo3' => '6.2.0-7.6.99',
			'sh_scoutnet_webservice' => '2.0.0-2.0.99',
		),
		'conflicts' => 
		array (
		),
		'suggests' => 
		array (
		),
	),
);

?>