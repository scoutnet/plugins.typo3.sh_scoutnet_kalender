<?php

/***************************************************************
 * Extension Manager/Repository config file for ext "sh_scoutnet_kalender".
 *
 * Auto generated 03-05-2013 19:13
 *
 * Manual updates:
 * Only the data in the array - anything else is removed by next write.
 * "version" and "dependencies" must not be touched!
 ***************************************************************/

/** @var string $_EXTKEY */
$EM_CONF[$_EXTKEY] = [
	'title' => 'Official Scoutnet Calendar Plugin',
	'description' => 'Official Typo3 plugin to display scoutnet.de calendar',
	'category' => 'plugin',
	'author' => 'Stefan "Mütze" Horst',
	'author_email' => 'muetze@scoutnet.de',
	'state' => 'beta',
	'internal' => '',
	'uploadfolder' => '0',
	'createDirs' => '',
	'clearCacheOnLoad' => 1,
	'version' => '4.0.0',
	'constraints' => [
		'depends' => [
			'typo3' => '9.0.0-9.5.99',
			'sh_scoutnet_webservice' => '3.0.0-3.99.99',
		],
		'conflicts' => [],
		'suggests' => [],
	],
];
