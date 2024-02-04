<?php

namespace ScoutNet\ShScoutnetKalender\Tests\Acceptance\Backend;

use ScoutNet\ShScoutnetKalender\Tests\Acceptance\Support\BackendTester;

/**
 * Tests the sh_scoutnet_kalender backend module can be loaded
 */
class ModuleCest
{
    /**
     * @param BackendTester $I
     */
    public function _before(BackendTester $I)
    {
        //        $I->useExistingSession('admin');
    }

    /**
     * @param BackendTester $I
     */
    public function dummyTest(BackendTester $I) {}
}
