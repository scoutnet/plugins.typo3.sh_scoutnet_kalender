<?php

########################################################################
# Extension Manager/Repository config file for ext "sh_scoutnet_kalender".
#
# Auto generated 04-10-2010 10:14
#
# Manual updates:
# Only the data in the array - everything else is removed by next
# writing. "version" and "dependencies" must not be touched!
########################################################################

$EM_CONF[$_EXTKEY] = array(
	'title' => 'Official Scoutnet Calendar Plugin',
	'description' => 'Official Typo3 plugin to display scoutnet.de calendar',
	'category' => 'plugin',
	'author' => 'Stefan "Muetze" Horst',
	'author_email' => 'muetze@scoutnet.de',
	'shy' => '',
	'dependencies' => 'cms,sh_scoutnet_webservice',
	'conflicts' => '',
	'priority' => '',
	'module' => '',
	'state' => 'beta',
	'internal' => 0,
	'uploadfolder' => 0,
	'createDirs' => '',
	'modify_tables' => 'tt_content',
	'clearCacheOnLoad' => 1,
	'lockType' => '',
	'author_company' => '',
	'version' => '0.10.3',
	'constraints' => array(
		'depends' => array(
			'cms' => '',
			'sh_scoutnet_webservice' => '',
		),
		'conflicts' => array(
		),
		'suggests' => array(
		),
	),
	'_md5_values_when_last_written' => 'a:20:{s:9:"ChangeLog";s:4:"4967";s:10:"README.txt";s:4:"d41d";s:12:"ext_icon.gif";s:4:"3ec8";s:17:"ext_localconf.php";s:4:"969d";s:14:"ext_tables.php";s:4:"ffb0";s:14:"ext_tables.sql";s:4:"7b1f";s:28:"ext_typoscript_constants.txt";s:4:"d119";s:24:"ext_typoscript_setup.txt";s:4:"2227";s:13:"locallang.xml";s:4:"415d";s:16:"locallang_db.xml";s:4:"27b6";s:19:"doc/wizard_form.dat";s:4:"22b9";s:20:"doc/wizard_form.html";s:4:"f6c9";s:14:"pi1/ce_wiz.gif";s:4:"40cf";s:39:"pi1/class.tx_shscoutnetkalender_pi1.php";s:4:"35f3";s:47:"pi1/class.tx_shscoutnetkalender_pi1_wizicon.php";s:4:"8176";s:13:"pi1/clear.gif";s:4:"cc11";s:16:"pi1/kalender.css";s:4:"977e";s:17:"pi1/locallang.xml";s:4:"42e2";s:26:"pi1/scoutnet_kalender.tmpl";s:4:"5314";s:24:"pi1/static/editorcfg.txt";s:4:"e688";}',
	'suggests' => array(
	),
);

?>