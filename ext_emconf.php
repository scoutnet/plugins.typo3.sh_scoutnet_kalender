<?php

/***************************************************************
 * Extension Manager/Repository config file for ext "sh_scoutnet_kalender".
 *
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
    'version' => '7.0.2',
    'constraints' => [
        'depends' => [
            'typo3' => '10.4.0-11.5.99',
            'sh_scoutnet_webservice' => '5.0.0-5.99.99',
        ],
        'conflicts' => [],
        'suggests' => [],
    ],
];
