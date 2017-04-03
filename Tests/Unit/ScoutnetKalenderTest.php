<?php

namespace ScoutNet\ShScoutnetKalender\Tests\Unit;

/***************************************************************
 *  Copyright notice
 *
 *  (c) 2017 Stefan Horst <stefan@ultrachaos.de>
 *  All rights reserved
 *
 *  This script is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  This copyright notice MUST APPEAR in all copies of the script!
 ***************************************************************/

/**
 * Class TmplMock
 */
class TmplMock {
    public function getFileName() {
        return "";
    }
}

/**
 * Class TmplMock
 */
class ViewMock {
    protected $view = [];

    public function assign($key, $value) {
        $this->view[$key] = $value;
    }

    /**
     * @return array
     */
    public function getView() {
        return $this->view;
    }

    /**
     * @param array $view
     */
    public function setView(array $view) {
        $this->view = $view;
    }
}

/**
 * Testcase for class ScoutNet\ShScoutnetKalender\Test
 */
class ScoutnetKalenderTest extends \TYPO3\CMS\Core\Tests\UnitTestCase {
    /**
     * @test
     * @return void
     */
    public function myFirstTest() {
        /** @var \ScoutNet\ShScoutnetKalender\Helpers\BackendHelpers $authenticationService */
        $cc = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\ScoutNet\ShScoutnetKalender\Controller\CalendarController::class);

        $event = new \ScoutNet\ShScoutnetWebservice\Domain\Model\Event();
        $event->setUid(15);
        $event->setTitle("Test Event");

        $structure = new \ScoutNet\ShScoutnetWebservice\Domain\Model\Structure();
        $structure->setUid(15);
        $structure->setName("Test Stamm");

        $sr = $this->getMock(\ScoutNet\ShScoutnetWebservice\Domain\Repository\StructureRepository::class);//, array('findByUids'), array(), '', FALSE);
        $sr->expects($this->once())->method('findByUids')->will($this->returnValue([$structure]));
        $er = $this->getMock(\ScoutNet\ShScoutnetWebservice\Domain\Repository\EventRepository::class);//, array('findByStructuresAndFilter'), array(), '', FALSE);
        $er->expects($this->once())->method('findByStructuresAndFilter')->will($this->returnValue([$event]));

        $view = new ViewMock();

        $settings['ssids'] = "3";

        $this->inject($cc, 'structureRepository', $sr);
        $this->inject($cc, 'eventRepository', $er);
        $this->inject($cc, 'view', $view);
        $this->inject($cc, 'settings', $settings);



        $GLOBALS['TSFE'] = new \stdClass();
        $GLOBALS['TSFE']->tmpl = new TmplMock();

        $cc->listAction();

        $this->assertEquals( $event->getUid(), $view->getView()['events'][0]->getUid());
        $this->assertEquals( $structure->getUid(), $view->getView()['structures'][0]->getUid());
    }
}
