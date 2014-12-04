<?php

/***************************************************************
 * Extension Manager/Repository config file for ext "sh_scoutnet_kalender".
 *
 * Auto generated 03-05-2013 19:13
 *
 * Manual updates:
 * Only the data in the array - everything else is removed by next
 * writing. "version" and "dependencies" must not be touched!
 ***************************************************************/

$EM_CONF[$_EXTKEY] = array (
	'title' => 'Official Scoutnet Calendar Plugin',
	'description' => 'Official Typo3 plugin to display scoutnet.de calendar',
	'category' => 'plugin',
	'shy' => 0,
	'version' => '2.0.9',
	'priority' => '',
	'loadOrder' => '',
	'module' => 'user_scoutnet',
	'state' => 'beta',
	'uploadfolder' => 0,
	'createDirs' => '',
	'modify_tables' => 'tt_content, be_user',
	'clearcacheonload' => 0,
	'lockType' => '',
	'author' => 'Stefan "Muetze" Horst',
	'author_email' => 'muetze@scoutnet.de',
	'author_company' => '',
	'CGLcompliance' => NULL,
	'CGLcompliance_note' => NULL,
	'constraints' => array (
		'depends' => array (
			'typo3' => '4.5.0-7.0.99',
			'sh_scoutnet_webservice' => '1.0',
		),
		'conflicts' => '',
		'suggests' => array (
		),
	),
);

?>
