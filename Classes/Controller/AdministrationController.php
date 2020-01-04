<?php
namespace ScoutNet\ShScoutnetKalender\Controller;

use TYPO3\CMS\Core\Configuration\ExtensionConfiguration;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;
use TYPO3\CMS\Extbase\Annotation\Inject;

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
 * Plugin 'ScoutNet Calendar' for the 'sh_scoutnet_kalender' extension.
 *
 * @author	Stefan Horst <stefan.horst@dpsg-koeln.de>
 */



class AdministrationController extends ActionController {
	const ERROR_UNKNOWN_ERROR = "errorUnknown";
	const ERROR_NO_RIGHTS = "noRights";
	const ERROR_NO_CONNECTION = "noConnection";
	const ERROR_RIGHTS_PENDING = 'rightsPending';

	/**
	 * @var \ScoutNet\ShScoutnetWebservice\Domain\Repository\EventRepository
	 * @Inject
	 */
	protected $eventRepository = null;

	/**
	 * @var \ScoutNet\ShScoutnetWebservice\Domain\Repository\StructureRepository
	 * @Inject
	 */
	protected $structureRepository = null;

	/**
	 * @var \ScoutNet\ShScoutnetWebservice\Domain\Repository\CategorieRepository
	 * @Inject
	 */
	protected $categorieRepository = null;

	/**
	 * @var \ScoutNet\ShScoutnetWebservice\Helpers\AuthHelper
	 * @Inject
	 */
	protected $authHelper = null;

	/**
	 * @var \ScoutNet\ShScoutnetWebservice\Domain\Repository\BackendUserRepository
	 * @Inject
	 */
	protected $backendUserRepository;

	/**
	 * action initializeAction
	 *
	 * @return void
	 */
	public function initializeAction() {
		parent::initializeAction();

		//set Default mapping for dates
		if (isset($this->arguments['event'])) {
			// set date format
			$mappingConfig = $this->arguments['event']->getPropertyMappingConfiguration();
			$mappingConfig->forProperty('startDate')->setTypeConverterOption(
				'TYPO3\\CMS\\Extbase\\Property\\TypeConverter\\DateTimeConverter',
				\TYPO3\CMS\Extbase\Property\TypeConverter\DateTimeConverter::CONFIGURATION_DATE_FORMAT,
				'd.m.Y'
			);
			$mappingConfig->forProperty('endDate')->setTypeConverterOption(
				'TYPO3\\CMS\\Extbase\\Property\\TypeConverter\\DateTimeConverter',
				\TYPO3\CMS\Extbase\Property\TypeConverter\DateTimeConverter::CONFIGURATION_DATE_FORMAT,
				'd.m.Y'
			);
		}
	}

	public function initializeView($view) {
		parent::initializeView($view);

		// set the background for every Action
		$this->setBackground();
	}

	private function checkRights() {
        $ssid = GeneralUtility::makeInstance(ExtensionConfiguration::class)->get('sh_scoutnet_kalender', 'ScoutnetSSID');

		$structure = $this->structureRepository->findByUid($ssid);

		/** @var \ScoutNet\ShScoutnetWebservice\Domain\Model\BackendUser $be_user */
		$be_user = $this->backendUserRepository->findByUid($GLOBALS['BE_USER']->user["uid"]);
		//\TYPO3\CMS\Extbase\Utility\DebuggerUtility::var_dump($be_user);

		// check if we get the login
		if (isset($_GET['logintype']) && $_GET['logintype'] === 'login' && isset($_GET['auth'])) {
			try {
				$data = $this->authHelper->getApiKeyFromData($_GET['auth']);
				//\TYPO3\CMS\Extbase\Utility\DebuggerUtility::var_dump($data);

				$be_user->setTxShscoutnetUsername($data['user']);
				$be_user->setTxShscoutnetApikey($data['api_key']);

				$this->backendUserRepository->update($be_user);
			} catch (\Exception $e) {
				$this->addFlashMessage('Cannot connect to Server'.$e->getMessage(),'Error', \TYPO3\CMS\Core\Messaging\AbstractMessage::ERROR);
				$this->view->assign('error', self::ERROR_NO_CONNECTION);
			}

		}

		if (trim($be_user->getTxShscoutnetApikey()) == '' || trim($be_user->getTxShscoutnetUsername()) == '') {
			// if we do not have a username or api key redirect to register
			$this->redirect('register');
		} else {
			try {
				$rights = $this->structureRepository->hasWritePermissionsToStructure($structure);

				switch ($rights['code']) {
					case \ScoutNet\ShScoutnetWebservice\Domain\Repository\StructureRepository::AUTH_WRITE_ALLOWED:
						// return no error
						return true;
						break;
					case \ScoutNet\ShScoutnetWebservice\Domain\Repository\StructureRepository::AUTH_NO_RIGHT:
						$link = $this->controllerContext->getUriBuilder()->uriFor('requestRights');
						$this->view->assign('error', "You have no rights");
						$this->view->assign('errorID', self::ERROR_NO_RIGHTS);
						$this->view->assign('errorArguments', array($link));
						break;
					case \ScoutNet\ShScoutnetWebservice\Domain\Repository\StructureRepository::AUTH_PENDING:
						$this->view->assign('error', "Your Rights are Pending");
						$this->view->assign('errorID', self::ERROR_RIGHTS_PENDING);
						break;
					default:
						$this->addFlashMessage('Cannot connect to Server', \TYPO3\CMS\Core\Messaging\AbstractMessage::ERROR);
						$this->view->assign('error', self::ERROR_UNKNOWN_ERROR);
						break;
				}
			} catch (\Exception $e) {
				$this->addFlashMessage('Cannot connect to Server'.$e->getMessage(),'Error', \TYPO3\CMS\Core\Messaging\AbstractMessage::ERROR);
				$this->view->assign('error', self::ERROR_NO_CONNECTION);
			}
		}

		return false;
	}

	public function listAction() {
		if ($this->checkRights()) {
            $ssid = GeneralUtility::makeInstance(ExtensionConfiguration::class)->get('sh_scoutnet_kalender', 'ScoutnetSSID');
			try {
				$filter = array(
					'order' => 'start_time desc',
				);

				// load Events from ScoutNet
				$structure = $this->structureRepository->findByUid($ssid);

				$this->view->assign('structure', $structure);
				$this->view->assign('events', $this->eventRepository->findByStructureAndFilter($structure, $filter));
			} catch (\Exception $e) {
				$this->addFlashMessage('Cannot connect to Server'.$e->getMessage(),'Error', \TYPO3\CMS\Core\Messaging\AbstractMessage::ERROR);
				$this->view->assign('error', self::ERROR_NO_CONNECTION);
			}
		}
	}


	/**
	 * @param \ScoutNet\ShScoutnetWebservice\Domain\Model\Structure  $structure
	 * @param \ScoutNet\ShScoutnetWebservice\Domain\Model\Event|null $event
     */
	public function newAction(\ScoutNet\ShScoutnetWebservice\Domain\Model\Structure $structure, \ScoutNet\ShScoutnetWebservice\Domain\Model\Event $event = null) {
		if ($this->checkRights()) {
			if ($event === null) {
				$event = new \ScoutNet\ShScoutnetWebservice\Domain\Model\Event();
				$event->setStructure($structure);
				$event->setStartDate(new \DateTime());
			}

			$this->_loadAllCategories($event->getStructure(), $event);

			// set event
			$this->view->assign('event', $event);
			$this->view->assign('verband', $event->getStructure()->getVerband());
		}
	}

	/**
	 * @param \ScoutNet\ShScoutnetWebservice\Domain\Model\Event $event
	 */
	public function templateAction(\ScoutNet\ShScoutnetWebservice\Domain\Model\Event $event) {
		$this->_loadAllCategories($event->getStructure(), $event);

		$newEvent = new \ScoutNet\ShScoutnetWebservice\Domain\Model\Event();
		$newEvent->setStructure($event->getStructure());

		// copy all properties
		$newEvent->copyProperties($event);

		// set event
		$this->view->assign('event', $newEvent);
		$this->view->assign('verband', $newEvent->getStructure()->getVerband());
	}


	/**
	 * action create
	 *
	 * @param \ScoutNet\ShScoutnetWebservice\Domain\Model\Event $event
	 * @param \array $categories
	 * @param \array $customCategories
	 * @return void
	 */
	public function createAction(\ScoutNet\ShScoutnetWebservice\Domain\Model\Event $event, $categories, $customCategories) {
		if ($this->checkRights()) {
			$categorieObjects = array();
			foreach ($categories as $uid => $selected) {
				// skip not selected
				if ($selected == 0) continue;

				$categorieObjects[] = $this->categorieRepository->findByUid($uid);
			}

			foreach ($customCategories as $categorie){
				if (strlen(trim($categorie)) > 0) {
					$cat = new \ScoutNet\ShScoutnetWebservice\Domain\Model\Categorie();
					$cat->setText(trim($categorie));
					$categorieObjects[] = $cat;
				}
			}

			$event->setCategories($categorieObjects);

			try {
				$this->eventRepository->add($event);

				$this->addFlashMessage('event created');
			} catch (\Exception $e) {
				$this->addFlashMessage('Cannot connect to Server'.$e->getMessage(),'Error', \TYPO3\CMS\Core\Messaging\AbstractMessage::ERROR);
				$this->view->assign('error', self::ERROR_NO_CONNECTION);
			}
		}

		$this->redirect('list');
	}


	/**
	 * action edit
	 *
	 * @param \ScoutNet\ShScoutnetWebservice\Domain\Model\Event $event
	 * @TYPO3\CMS\Extbase\Annotation\IgnoreValidation("event")
	 * @return void
	 */
	public function editAction(\ScoutNet\ShScoutnetWebservice\Domain\Model\Event $event) {
		if ($this->checkRights()) {
			$this->_loadAllCategories($event->getStructure(), $event);

			// set event
			$this->view->assign('event', $event);
			$this->view->assign('verband', $event->getStructure()->getVerband());
			//\TYPO3\CMS\Extbase\Utility\DebuggerUtility::var_dump($event);
		}
	}

	/**
	 * action edit
	 *
	 * @param \ScoutNet\ShScoutnetWebservice\Domain\Model\Event $event
	 * @param \array $categories
	 * @param \array $customCategories
	 * @return void
	 */
	public function updateAction(\ScoutNet\ShScoutnetWebservice\Domain\Model\Event $event, $categories, $customCategories) {
		if ($this->checkRights()) {
            $categorieObjects = array();
            foreach ($categories as $uid => $selected) {
                // skip not selected
                if ($selected == 0) continue;

                $categorieObjects[] = $this->categorieRepository->findByUid($uid);
            }

            foreach ($customCategories as $categorie){
                if (strlen(trim($categorie)) > 0) {
                    $cat = new \ScoutNet\ShScoutnetWebservice\Domain\Model\Categorie();
                    $cat->setText(trim($categorie));
                    $categorieObjects[] = $cat;
                }
            }

            $event->setCategories($categorieObjects);

			try {
				$this->eventRepository->update($event);

				$this->addFlashMessage('event saved');
			} catch (\Exception $e) {
				$this->addFlashMessage('Cannot connect to Server'.$e->getMessage(),'Error', \TYPO3\CMS\Core\Messaging\AbstractMessage::ERROR);
				$this->view->assign('error', self::ERROR_NO_CONNECTION);
			}
		}
		$this->redirect('list');
	}

	/**
	 * @param \ScoutNet\ShScoutnetWebservice\Domain\Model\Event $event
     * @TYPO3\CMS\Extbase\Annotation\IgnoreValidation("event")
     */
	public function deleteAction(\ScoutNet\ShScoutnetWebservice\Domain\Model\Event $event = null) {
		// set event
		$this->view->assign('event', $event);
	}

	/**
	 * @param \ScoutNet\ShScoutnetWebservice\Domain\Model\Event $event
     * @TYPO3\CMS\Extbase\Annotation\IgnoreValidation("event")
	 */
	public function removeAction(\ScoutNet\ShScoutnetWebservice\Domain\Model\Event $event = null) {
		if ($this->checkRights()) {
			try {
				$this->eventRepository->delete($event);

				$this->addFlashMessage('event Deleted');
			} catch (\Exception $e) {
				$this->addFlashMessage('Cannot connect to Server'.$e->getMessage(),'Error', \TYPO3\CMS\Core\Messaging\AbstractMessage::ERROR);
				$this->view->assign('error', self::ERROR_NO_CONNECTION);
			}
		}
		$this->redirect('list');
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
        $ssid = GeneralUtility::makeInstance(ExtensionConfiguration::class)->get('sh_scoutnet_kalender', 'ScoutnetSSID');
		try {
			$structure = $this->structureRepository->findByUid($ssid);
			$this->structureRepository->requestWritePermissionsForStructure($structure);

		} catch (\Exception $e) {
			$this->addFlashMessage('Cannot connect to Server'.$e->getMessage(),'Error', \TYPO3\CMS\Core\Messaging\AbstractMessage::ERROR);
			$this->view->assign('error', self::ERROR_NO_CONNECTION);
		}

		$this->redirect('list');
	}


	/**
	 * Set the Background color for the calendar we use
     */
	private function setBackground() {
        $ssid = GeneralUtility::makeInstance(ExtensionConfiguration::class)->get('sh_scoutnet_kalender', 'ScoutnetSSID');

		$verbaende = array(
			'BDP' => array('name' => 'bdp', 'color' => '#3333cc'),
			'DPSG' => array('name' => 'dpsg', 'color' => '#C1B38F'),
			'PSG' => array('name' => 'psg', 'color' => '#99ccff'),
			'VCP' => array('name' => 'vcp', 'color' => '#ccccff'),
			'' => array('name'=>'wosm', 'color' => '#622599'),
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
		#$uri = $this->objectManager->get('TYPO3\CMS\Fluid\ViewHelpers\Uri\ResourceViewHelper')->render('img/stoff_tile_'.$verbaende[$verband]['name'].'.png','sh_scoutnet_kalender');
		#$uri = "";

		//\TYPO3\CMS\Extbase\Utility\DebuggerUtility::var_dump($uri);
		//$this->view->assign('background_style', 'background-color: '.$verbaende[$verband]['color'].';background-image: url(\''.$uri.'\')');
		$this->view->assign('background_color', $verbaende[$verband]['color']);
		$this->view->assign('background_image', 'img/stoff_tile_'.$verbaende[$verband]['name'].'.png');
	}

}
