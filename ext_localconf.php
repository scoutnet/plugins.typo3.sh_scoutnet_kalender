<?php

defined('TYPO3') || die('Access denied.');

/** @var string $_EXTKEY */
\TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin(
    'sh_scoutnet_kalender',
    'Calendar',
    [
        \ScoutNet\ShScoutnetKalender\Controller\CalendarController::class => 'list, details',
    ],
    [
        \ScoutNet\ShScoutnetKalender\Controller\CalendarController::class => 'list, details',
    ]
);
