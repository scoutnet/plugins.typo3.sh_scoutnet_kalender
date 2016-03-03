<?php
if (!defined ('TYPO3_MODE')) die ('Access denied.');

/** @var string $_EXTKEY */
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


