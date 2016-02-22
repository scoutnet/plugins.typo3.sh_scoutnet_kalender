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


class CalendarController extends \TYPO3\CMS\Extbase\Mvc\Controller\ActionController
{
	/**
	 * @var \ScoutNet\ShScoutnetWebservice\Domain\Repository\EventRepository
	 * @inject
	 */
	protected $eventRepository = null;


	/**
	 * @param array $addids
	 * @param integer $eventId
	 */
	public function listAction($addids = array(), $eventId = null)
	{
		$this->cObj = $this->configurationManager->getContentObject();
		$old_timezone = ini_get('date.timezone');
		date_default_timezone_set('UTC');

		$cssFile = $GLOBALS['TSFE']->tmpl->getFileName($this->settings["cssFile"]);

		// Include CSS and JS
		$GLOBALS['TSFE']->additionalHeaderData['tx_sh_scoutnet_Kalender'] = '<link rel="stylesheet" type="text/css" href="' . $cssFile . '" media="screen" />' . "\n" .
			'<script type="text/javascript" src="https://kalender.scoutnet.de/2.0/templates/scoutnet/behavior.js"></script>' . "\n" .
			'<script type="text/javascript" src="https://kalender.scoutnet.de/js/base2-p.js"></script>' . "\n" .
			'<script type="text/javascript" src="https://kalender.scoutnet.de/js/base2-dom-p.js"></script>' . "\n" .
			'<style type="text/css" media="all"> .snk-termin-infos{ display:none; } .snk-footer { margin-bottom: 20px; } .snk-termine {width: auto; margin-right: 5px;}</style>' . "\n" .
			'<script type="text/javascript">' . "\n" .
			'base2.DOM.bind(document);' . "\n" .
			'snk_init();' . "\n" .
			'document.addEventListener(\'DOMContentLoaded\', function(){ return snk_finish(\'\'); }, false);' . "\n" .
			'</script>' . "\n";

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

		$events = array();
		try {
			$events = $this->eventRepository->get_events_for_global_id_with_filter($ids, $filter);
			$kalender = $this->eventRepository->get_kalender_by_global_id($ids);

			$optionalKalenders = Array();
			if (isset($this->cObj->data["tx_shscoutnetkalender_optids"]) && trim($this->cObj->data["tx_shscoutnetkalender_optids"])) {
				$optids = explode(",", $this->cObj->data["tx_shscoutnetkalender_optids"]);
				$optionalKalenders = $this->eventRepository->get_kalender_by_global_id($optids);
			}

			foreach ($optionalKalenders as $optionalKalender) {
				$optionalKalender['Selected'] = in_array($optionalKalender['ID'], $ids);
			}

			//\TYPO3\CMS\Extbase\Utility\DebuggerUtility::var_dump($events);


			// create startYear and startMonth
			foreach ($events as $event) {
				$event['StartYear'] = $event['Start']->format('Y');
				$event['StartMonth'] = $event['Start']->format('m');
				$event['OneDay'] = !isset($event['End']) || $event['Start']->format('dmy') == $event['End']->format('dmy');


				$event['ShowDetails'] = trim($event['Description']) . trim($event['ZIP']) . trim($event['Location']) . trim($event['Organizer']) . trim($event['Target_Group']) . trim($event['URL']) !== '';
			}
		} catch (\Exception $e) {
			$this->view->assign('error', $e->getMessage());
		}


		$this->view->assign('events', $events);
		$this->view->assign('kalender', $kalender);
		$this->view->assign('optionalKalenders', $optionalKalenders);
		$this->view->assign('eventId', intval($eventId));

		date_default_timezone_set($old_timezone);
	}
}
