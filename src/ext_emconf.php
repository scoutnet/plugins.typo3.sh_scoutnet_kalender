<?php

########################################################################
# Extension Manager/Repository config file for ext "sh_scoutnet_kalender".
#
# Auto generated 30-04-2013 17:41
#
# Manual updates:
# Only the data in the array - everything else is removed by next
# writing. "version" and "dependencies" must not be touched!
########################################################################

$EM_CONF[$_EXTKEY] = array(
	'title' => 'Official Scoutnet Calendar Plugin',
	'description' => 'Official Typo3 plugin to display scoutnet.de calendar',
	'category' => 'plugin',
	'shy' => 0,
	'version' => '2.0.4',
	'dependencies' => '',
	'conflicts' => '',
	'priority' => '',
	'loadOrder' => '',
	'module' => 'user_scoutnet',
	'state' => 'beta',
	'uploadfolder' => 0,
	'createDirs' => '',
	'modify_tables' => 'tt_content, be_user',
	'clearcacheonload' => 1,
	'lockType' => '',
	'author' => 'Stefan "Muetze" Horst',
	'author_email' => 'muetze@scoutnet.de',
	'author_company' => '',
	'CGLcompliance' => '',
	'CGLcompliance_note' => '',
	'constraints' => array(
		'depends' => array(
			'cms' => '',
			'typo3' => '4.5.0-6.0.99',
			'sh_scoutnet_webservice' => '1.0',
		),
		'conflicts' => array(
		),
		'suggests' => array(
		),
	),
);

?>
