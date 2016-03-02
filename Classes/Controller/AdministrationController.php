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


class AdministrationController extends \TYPO3\CMS\Extbase\Mvc\Controller\ActionController {
	const ERROR_UNKNOWN_ERROR = 0;
	const ERROR_NO_RIGHTS = 1;
	const ERROR_NO_CONECTION = 2;
	const ERROR_RIGHTS_PENDING = 3;

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
	 * @var \ScoutNet\ShScoutnetWebservice\Domain\Repository\CategorieRepository
	 * @inject
	 */
	protected $categorieRepository = null;

	/**
	 * @var \ScoutNet\ShScoutnetWebservice\Helpers\AuthHelper
	 * @inject
	 */
	protected $authHelper = null;

	/**
	 * @var \ScoutNet\ShScoutnetKalender\Domain\Repository\BackendUserRepository
	 * @inject
	 */
	protected $backendUserRepository;





	protected $extConfig = null;

	public function initializeAction() {
		parent::initializeAction();

		// load the extConfig
		$this->extConfig = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['sh_scoutnet_kalender']);
	}

	public function initializeView($view) {
		parent::initializeView($view);

		// set the background for every Action
		$this->setBackground();
	}

	private function checkRights() {
		$ssid = $this->extConfig['ScoutnetSSID'];

		/** @var \ScoutNet\ShScoutnetKalender\Domain\Model\BackendUser $be_user */
		$be_user = $this->backendUserRepository->findByUid($GLOBALS['BE_USER']->user["uid"]);
		//\TYPO3\CMS\Extbase\Utility\DebuggerUtility::var_dump($be_user);

		// check if we get the login
		if (isset($_GET['logintype']) && $_GET['logintype'] === 'login' && isset($_GET['auth'])) {
			try {
				$data = $this->authHelper->getApiKeyFromData($_GET['auth']);
				//\TYPO3\CMS\Extbase\Utility\DebuggerUtility::var_dump($data);

				$be_user->setTxShscoutnetkalenderScoutnetUsername($data['user']);
				$be_user->setTxShscoutnetkalenderScoutnetApikey($data['api_key']);

				$this->backendUserRepository->update($be_user);
			} catch (\Exception $e) {
				// TODO: handle error with flash message
			}

		}

		if (trim($be_user->getTxShscoutnetkalenderScoutnetApikey()) == '' || trim($be_user->getTxShscoutnetkalenderScoutnetUsername()) == '') {
			// if we do not have a username or api key redirect to register
			$this->redirect('register');
		} else {
			try {
				$rights = $this->structureRepository->hasWritePermissionsToCalender($ssid,$be_user->getTxShscoutnetkalenderScoutnetUsername(),$be_user->getTxShscoutnetkalenderScoutnetApikey());

				switch ($rights['code']) {
					case \ScoutNet\ShScoutnetWebservice\Domain\Repository\StructureRepository::AUTH_WRITE_ALLOWED:
						// return no error
						return true;
						break;
					case \ScoutNet\ShScoutnetWebservice\Domain\Repository\StructureRepository::AUTH_NO_RIGHT:
						$this->redirect('error', Null, Null, array('error'=> self::ERROR_NO_RIGHTS));
						break;
					case \ScoutNet\ShScoutnetWebservice\Domain\Repository\StructureRepository::AUTH_PENDING:
						$this->redirect('error', Null, Null, array('error'=> self::ERROR_RIGHTS_PENDING));
						break;
					default:
						$this->redirect('error', Null, Null, array('error'=> self::ERROR_UNKNOWN_ERROR));
						break;
				}

				//\TYPO3\CMS\Extbase\Utility\DebuggerUtility::var_dump($rights);
			} catch (\Exception $e) {
				// TODO: handle error with flash message
				$this->view->assign('error', $e->getMessage());
				return false;
			}
		}

		return false;
	}

	public function listAction() {
		if ($this->checkRights()) {
			$ssid = $this->extConfig['ScoutnetSSID'];
			try {
				$filter = array(
					'order' => 'start_time desc',
					'limit' => '5',
				);

				// load Events from ScoutNet
				$this->view->assign('events', $this->eventRepository->get_events_for_global_id_with_filter(array($ssid), $filter));
				$this->view->assign('kalender', $this->structureRepository->findKalenderByGlobalid($ssid)[0]);
			} catch (\Exception $e) {
				// TODO: handle error with flash message
				$this->addFlashMessage('Cannot connect to Server'.$e->getMessage(),'Error', \TYPO3\CMS\Core\Messaging\AbstractMessage::ERROR);
				$this->view->assign('error', $e->getMessage());

			}
		}
	}

	/**
	 * action edit
	 *
	 * @param \ScoutNet\ShScoutnetWebservice\Domain\Model\Event $event
	 * @ignorevalidation $event
	 * @return void
	 */
	public function editAction(\ScoutNet\ShScoutnetWebservice\Domain\Model\Event $event) {
		$this->_loadAllCategories($event->getStructure(), $event);

		// set event
		$this->view->assign('event', $event);
		$this->view->assign('verband', $event->getStructure()->getVerband());
		//\TYPO3\CMS\Extbase\Utility\DebuggerUtility::var_dump($event);
	}

	/**
	 * @param \ScoutNet\ShScoutnetWebservice\Domain\Model\Event $event
	 * @ignorevalidation $event
     */
	public function deleteAction(\ScoutNet\ShScoutnetWebservice\Domain\Model\Event $event = null) {
		// set event
		$this->view->assign('event', $event);

	}

	/**
	 * @param \ScoutNet\ShScoutnetWebservice\Domain\Model\Event $event
	 * @ignorevalidation $event
	 */
	public function removeAction(\ScoutNet\ShScoutnetWebservice\Domain\Model\Event $event = null) {
		// set event
		$this->view->assign('event', $event);

	}

	/**
	 * @param \ScoutNet\ShScoutnetWebservice\Domain\Model\Structure  $structure
	 * @param \ScoutNet\ShScoutnetWebservice\Domain\Model\Event|null $event
     */
	private function _loadAllCategories(\ScoutNet\ShScoutnetWebservice\Domain\Model\Structure $structure, \ScoutNet\ShScoutnetWebservice\Domain\Model\Event $event = null) {
		$categories = $this->categorieRepository->getAllCategoriesForStructureAndEvent($structure, $event);

		$this->view->assign('forcedCategories', $categories['forcedCategories']);
		$this->view->assign('allSectionCategories', $categories['allSectCategories']);
		$this->view->assign('generatedCategories', $categories['generatedCategories']);

		$this->view->assign('forcedCategoriesLabel', array_keys($structure->getForcedCategories())[1]);
	}


	public function registerAction() {

	}

	public function requestRightsAction() {
		/*
					if ($rights['code'] == 2) {
						$markers['CONTENT'] = $GLOBALS['LANG']->getLL('noRightsButRequestedError');
					} else {
						$markers['CONTENT'] = sprintf($GLOBALS['LANG']->getLL('noRightsError'),$link);
					}
		*/
	}
/*
	public function errorAction($error = self::ERROR_NO_RIGHTS) {
		return "error".$error;
	}
*/


	/**
	 * Set the Background color for the calendar we use
     */
	private function setBackground() {
		$ssid = $this->extConfig['ScoutnetSSID'];

		$verbaende = array(
			'BDP' => array('name' => 'bdp', 'color' => '#3333cc'),
			'DPSG' => array('name' => 'dpsg', 'color' => '#C1B38F'),
			'PSG' => array('name' => 'psg', 'color' => '#99ccff'),
			'VCP' => array('name' => 'vcp', 'color' => '#ccccff'),
		);

		$verband = array_rand($verbaende);

		if (intval($ssid) !== 0) {
			try {
				$kalender = $this->structureRepository->findByUid($ssid);

				if (isset($verbaende[$kalender->getVerband()])) {
					$verband = $kalender->getVerband();
				}
				//\TYPO3\CMS\Extbase\Utility\DebuggerUtility::var_dump($kalender);
			} catch (\Exception $e) {
				// do nothing
			}
		}

		// use the f:uri.resource view helper to render resource url
		$uri = $this->objectManager->get('\TYPO3\CMS\Fluid\ViewHelpers\Uri\ResourceViewHelper')->render('img/stoff_tile_'.$verbaende[$verband]['name'].'.png','sh_scoutnet_kalender');

		//\TYPO3\CMS\Extbase\Utility\DebuggerUtility::var_dump($uri);
		$this->view->assign('background_style', 'background-color: '.$verbaende[$verband]['color'].';background-image: url(\''.$uri.'\')');
	}

}
