<?php
namespace ScoutNet\ShScoutnetKalender\Controller;

use Exception;
use ScoutNet\ShScoutnetWebservice\Domain\Repository\EventRepository;
use ScoutNet\ShScoutnetWebservice\Domain\Repository\StructureRepository;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;
use TYPO3\CMS\Frontend\Resource\FilePathSanitizer;

/***************************************************************
*  Copyright notice
*
*  (c) 2009 Stefan Horst <stefan.horst@dpsg-koeln.de>
*  All rights reserved
*
*  This script is part of the TYPO3 project. The TYPO3 project is
*  free software; you can redistribute it and/or modify
*  it under the terms of the GNU General Public License as published by
*  the Free Software Foundation; either version 2 of the License, or
*  (at your option) any later version.
*
*  The GNU General Public License can be found at
*  http://www.gnu.org/copyleft/gpl.html.
*
*  This script is distributed in the hope that it will be useful,
*  but WITHOUT ANY WARRANTY; without even the implied warranty of
*  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
*  GNU General Public License for more details.
*
*  This copyright notice MUST APPEAR in all copies of the script!
***************************************************************/
/**
 * Plugin 'Scoutnet calendar' for the 'sh_scoutnet_kalender' extension.
 *
 * @author	Stefan Horst <stefan.horst@dpsg-koeln.de>
 */


class CalendarController extends ActionController {
	/**
	 * @var \ScoutNet\ShScoutnetWebservice\Domain\Repository\EventRepository
	 */
    private $eventRepository;

    /**
     * @var \ScoutNet\ShScoutnetWebservice\Domain\Repository\StructureRepository
     */
    private $structureRepository;

    /**
     * CalendarController constructor.
     *
     * @param \ScoutNet\ShScoutnetWebservice\Domain\Repository\EventRepository     $eventRepository
     * @param \ScoutNet\ShScoutnetWebservice\Domain\Repository\StructureRepository $structureRepository
     */
    public function __construct(
        EventRepository $eventRepository,
        StructureRepository $structureRepository
    ) {
        $this->eventRepository = $eventRepository;
        $this->structureRepository = $structureRepository;
    }

    /**
     * @param array|null $addids
     * @param int|null   $eventId
     */
	public function listAction(?array $addids = [], ?int $eventId = null) {
	    $filePathSanitizer = GeneralUtility::makeInstance(FilePathSanitizer::class);

        $cssFile = $filePathSanitizer->sanitize($this->settings['cssFile']);
        $jsFolder = dirname($filePathSanitizer->sanitize('EXT:sh_scoutnet_kalender/Resources/Public/JS/base2-p.js')).'/';

		// add CSS and Javascript
		$GLOBALS['TSFE']->additionalHeaderData['tx_sh_scoutnet_Kalender'] =
			'<link rel="stylesheet" type="text/css" href="' . $cssFile . '" media="screen" />' . "\n" .
			'<script type="text/javascript" src="'.$jsFolder.'base2-p.js"></script>' . "\n" .
			'<script type="text/javascript" src="'.$jsFolder.'base2-dom-p.js"></script>' . "\n" .
			'<script type="text/javascript" src="'.$jsFolder.'kalender.js"></script>' . "\n";

		$ids = explode(",", $this->settings["ssids"]);

		$filter = [
			'limit' => isset($this->settings["limit"]) ? $this->settings["limit"] : 999,
			'after' => 'now()',
        ];

		if (isset($this->settings["categories"]) && trim($this->settings["categories"])) {
			$filter['kategories'] = explode(",", $this->settings["categories"]);
		}

		/*
		if (isset($this->cObj->data["tx_shscoutnetkalender_stufen_ids"]) && trim($this->cObj->data["tx_shscoutnetkalender_stufen_ids"])) {
			$filter['stufen'] = explode(",", $this->cObj->data["tx_shscoutnetkalender_stufen_ids"]);
		}
		*/

		if (isset($addids) && count($addids) > 0 && is_array($addids)) {
			$ids = array_merge($ids, $addids);
		}

		$structures = [];
		$optionalStructures = [];
		$events = [];
		try {
			$structures = $this->structureRepository->findByUids($ids);
			$events = $this->eventRepository->findByStructuresAndFilter($structures, $filter);

			$optStructures = [];
			if (isset($this->settings["optSsids"]) && trim($this->settings["optSsids"])) {
				$optids = explode(",", $this->settings["optSsids"]);
				$optStructures = $this->structureRepository->findByUids($optids);
			}

			foreach ($optStructures as $optionalStructure) {
				$optionalStructures[] = [
					'selected' => in_array($optionalStructure->getUid(), $ids),
					'structure' => $optionalStructure,
                ];
			}
		} catch (Exception $e) {
			$this->view->assign('error', $e->getMessage());
		}

		$this->view->assign('events', $events);
		$this->view->assign('structures', $structures);
		$this->view->assign('optionalStructures', $optionalStructures);
		$this->view->assign('eventId', intval($eventId));
	}

    /**
     * @param int   $eventId
     */
    public function detailsAction(int $eventId) {
        $filePathSanitizer = GeneralUtility::makeInstance(FilePathSanitizer::class);

        $cssFile = $filePathSanitizer->sanitize($this->settings['cssFile']);
        $jsFolder = dirname($filePathSanitizer->sanitize('EXT:sh_scoutnet_kalender/Resources/Public/JS/base2-p.js')).'/';

        // add CSS and Javascript
        $GLOBALS['TSFE']->additionalHeaderData['tx_sh_scoutnet_Kalender'] =
            '<link rel="stylesheet" type="text/css" href="' . $cssFile . '" media="screen" />' . "\n" .
            '<script type="text/javascript" src="'.$jsFolder.'base2-p.js"></script>' . "\n" .
            '<script type="text/javascript" src="'.$jsFolder.'base2-dom-p.js"></script>' . "\n" .
            '<script type="text/javascript" src="'.$jsFolder.'kalender.js"></script>' . "\n";

        try {
            $event = $this->eventRepository->findByUid($eventId);
            $this->view->assign('event', $event);
        } catch (Exception $e) {
            $this->view->assign('error', $e->getMessage());
        }
    }
}
