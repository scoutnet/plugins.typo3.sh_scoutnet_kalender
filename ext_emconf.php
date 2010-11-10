<?php

########################################################################
# Extension Manager/Repository config file for ext "sh_scoutnet_kalender".
#
# Auto generated 10-11-2010 17:59
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
	'module' => 'user_scoutnet',
	'state' => 'beta',
	'internal' => 0,
	'uploadfolder' => 0,
	'createDirs' => '',
	'modify_tables' => 'tt_content, be_user',
	'clearCacheOnLoad' => 1,
	'lockType' => '',
	'author_company' => '',
	'version' => '2.0.0',
	'constraints' => array(
		'depends' => array(
			'cms' => '',
			'sh_scoutnet_webservice' => '1.0',
		),
		'conflicts' => array(
		),
		'suggests' => array(
		),
	),
	'_md5_values_when_last_written' => 'a:37:{s:9:"ChangeLog";s:4:"4967";s:10:"README.txt";s:4:"d41d";s:21:"ext_conf_template.txt";s:4:"bbf4";s:12:"ext_icon.gif";s:4:"3ec8";s:17:"ext_localconf.php";s:4:"0048";s:14:"ext_tables.php";s:4:"ba85";s:14:"ext_tables.sql";s:4:"da03";s:28:"ext_typoscript_constants.txt";s:4:"d119";s:24:"ext_typoscript_setup.txt";s:4:"2227";s:13:"locallang.xml";s:4:"415d";s:16:"locallang_db.xml";s:4:"8a2a";s:19:"doc/wizard_form.dat";s:4:"22b9";s:20:"doc/wizard_form.html";s:4:"f6c9";s:14:"pi1/ce_wiz.gif";s:4:"40cf";s:39:"pi1/class.tx_shscoutnetkalender_pi1.php";s:4:"fe29";s:47:"pi1/class.tx_shscoutnetkalender_pi1_wizicon.php";s:4:"8176";s:13:"pi1/clear.gif";s:4:"cc11";s:16:"pi1/kalender.css";s:4:"977e";s:17:"pi1/locallang.xml";s:4:"42e2";s:26:"pi1/scoutnet_kalender.tmpl";s:4:"5314";s:24:"pi1/static/editorcfg.txt";s:4:"e688";s:28:"user_scoutnet/background.png";s:4:"88b6";s:28:"user_scoutnet/background.psd";s:4:"703c";s:25:"user_scoutnet/bg_ecke.gif";s:4:"3664";s:23:"user_scoutnet/bg_h1.png";s:4:"24e7";s:23:"user_scoutnet/clear.gif";s:4:"cc11";s:22:"user_scoutnet/conf.php";s:4:"1e2b";s:22:"user_scoutnet/icon.gif";s:4:"7941";s:23:"user_scoutnet/index.php";s:4:"64ee";s:32:"user_scoutnet/kalender-infos.css";s:4:"b23d";s:26:"user_scoutnet/kalender.css";s:4:"9fb3";s:27:"user_scoutnet/locallang.xml";s:4:"eafc";s:23:"user_scoutnet/style.css";s:4:"a16a";s:32:"user_scoutnet/template_edit.html";s:4:"0a25";s:33:"user_scoutnet/template_error.html";s:4:"5fdf";s:36:"user_scoutnet/template_noApiKey.html";s:4:"6fc8";s:36:"user_scoutnet/template_overview.html";s:4:"064a";}',
	'suggests' => array(
	),
);

?>