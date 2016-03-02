<?php
if (!defined ('TYPO3_MODE')) die ('Access denied.');

\TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin(
	'ScoutNet.' . $_EXTKEY,
	'Calendar',
	array(
		'Calendar' => 'list',
	),
	// non-cacheable actions
	array(
		'Calendar' => 'list',
	)
);


