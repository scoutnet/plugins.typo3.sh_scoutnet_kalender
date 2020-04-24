<?php
namespace ScoutNet\ShScoutnetKalender\Tests\Acceptance\Support\Extension;

use TYPO3\TestingFramework\Core\Acceptance\Extension\BackendEnvironment;

/**
 * Load various core extensions and scoutnet dummy
 */
class BackendDummyEnvironment extends BackendEnvironment
{
    /**
     * Load a list of core extensions
     *
     * @var array
     */
    protected $localConfig = [
        'coreExtensionsToLoad' => [
            'core',
            'extbase',
            'fluid',
            'backend',
            'about',
            'install',
            'frontend',
            'recordlist',
            'extensionmanager',
        ],
        'testExtensionsToLoad' => [
			'typo3conf/ext/sh_scoutnet_kalender',
        ],
        'xmlDatabaseFixtures' => [
            'PACKAGE:typo3/testing-framework/Resources/Core/Acceptance/Fixtures/be_users.xml',
            'PACKAGE:typo3/testing-framework/Resources/Core/Acceptance/Fixtures/be_sessions.xml',
            'PACKAGE:typo3/testing-framework/Resources/Core/Acceptance/Fixtures/be_groups.xml',
        ],
    ];
}
