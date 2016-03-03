<?php
namespace ScoutNet\ShScoutnetKalender\Controller;

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


class CalendarController extends \TYPO3\CMS\Extbase\Mvc\Controller\ActionController {
	/**
	 * @var \ScoutNet\ShScoutnetWebservice\Domain\Repository\EventRepository
	 * @inject
	 */
	protected $eventRepository = null;

	/**
	 * @var \ScoutNet\ShScoutnetWebservice\Domain\Repository\StructureRepository
	 * @inject
	 */
	protected $structureRepository = null;

	/**
	 * @var \TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer
	 */
	protected $cObj = null;

	/**
	 * @param array $addids
	 * @param integer $eventId
	 */
	public function listAction($addids = array(), $eventId = null) {
		$this->cObj = $this->configurationManager->getContentObject();

		$cssFile = $GLOBALS['TSFE']->tmpl->getFileName($this->settings['cssFile']);
		$jsFolder = $GLOBALS['TSFE']->tmpl->getFileName('EXT:sh_scoutnet_kalender/Resources/Public/JS/');

		// add CSS and Javascript
		$GLOBALS['TSFE']->additionalHeaderData['tx_sh_scoutnet_Kalender'] =
			'<link rel="stylesheet" type="text/css" href="' . $cssFile . '" media="screen" />' . "\n" .
			'<script type="text/javascript" src="'.$jsFolder.'base2-p.js"></script>' . "\n" .
			'<script type="text/javascript" src="'.$jsFolder.'base2-dom-p.js"></script>' . "\n" .
			'<script type="text/javascript" src="'.$jsFolder.'kalender.js"></script>' . "\n";

		$ids = explode(",", $this->cObj->data["tx_shscoutnetkalender_ids"]);

		$filter = array(
			'limit' => isset($this->settings["limit"]) ? $this->settings["limit"] : 999,
			'after' => 'now()',
		);

		if (isset($this->cObj->data["tx_shscoutnetkalender_kat_ids"]) && trim($this->cObj->data["tx_shscoutnetkalender_kat_ids"])) {
			$filter['kategories'] = explode(",", $this->cObj->data["tx_shscoutnetkalender_kat_ids"]);
		}

		if (isset($this->cObj->data["tx_shscoutnetkalender_stufen_ids"]) && trim($this->cObj->data["tx_shscoutnetkalender_stufen_ids"])) {
			$filter['stufen'] = explode(",", $this->cObj->data["tx_shscoutnetkalender_stufen_ids"]);
		}

		if (isset($addids) && count($addids) > 0 && is_array($addids)) {
			$ids = array_merge($ids, $addids);
		}

		$structures = array();
		$optionalStructures = array();
		$events = array();
		try {
			$structures = $this->structureRepository->findByUids($ids);
			$events = $this->eventRepository->findByStructuresAndFilter($structures, $filter);

			$optStructures = Array();
			if (isset($this->cObj->data["tx_shscoutnetkalender_optids"]) && trim($this->cObj->data["tx_shscoutnetkalender_optids"])) {
				$optids = explode(",", $this->cObj->data["tx_shscoutnetkalender_optids"]);
				$optStructures = $this->structureRepository->findByUids($optids);
			}

			foreach ($optStructures as $optionalStructure) {
				$optionalStructures[] = array(
					'selected' => in_array($optionalStructure->getUid(), $ids),
					'structure' => $optionalStructure,
				);
			}

			//\TYPO3\CMS\Extbase\Utility\DebuggerUtility::var_dump($events);
		} catch (\Exception $e) {
			$this->view->assign('error', $e->getMessage());
		}


		$this->view->assign('events', $events);
		$this->view->assign('structures', $structures);
		$this->view->assign('optionalStructures', $optionalStructures);
		$this->view->assign('eventId', intval($eventId));
	}
}
