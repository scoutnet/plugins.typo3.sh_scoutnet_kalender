<?php

namespace ScoutNet\ShScoutnetKalender\Controller;

use DateTime;
use Exception;
use ScoutNet\ShScoutnetWebservice\Domain\Model\Category;
use ScoutNet\ShScoutnetWebservice\Domain\Model\Event;
use ScoutNet\ShScoutnetWebservice\Domain\Model\Structure;
use ScoutNet\ShScoutnetWebservice\Domain\Repository\BackendUserRepository;
use ScoutNet\ShScoutnetWebservice\Domain\Repository\CategoryRepository;
use ScoutNet\ShScoutnetWebservice\Domain\Repository\EventRepository;
use ScoutNet\ShScoutnetWebservice\Domain\Repository\StructureRepository;
use ScoutNet\ShScoutnetWebservice\Helpers\AuthHelper;
use TYPO3\CMS\Core\Configuration\ExtensionConfiguration;
use TYPO3\CMS\Core\Messaging\AbstractMessage;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Annotation\Inject;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;
use TYPO3\CMS\Extbase\Mvc\View\ViewInterface;
use TYPO3\CMS\Extbase\Property\TypeConverter\DateTimeConverter;

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
class AdministrationController extends ActionController
{
    const ERROR_UNKNOWN_ERROR = 'errorUnknown';
    const ERROR_NO_RIGHTS = 'noRights';
    const ERROR_NO_CONNECTION = 'noConnection';
    const ERROR_RIGHTS_PENDING = 'rightsPending';

    /**
     * @var \ScoutNet\ShScoutnetWebservice\Domain\Repository\EventRepository
     * @Inject
     */
    protected $eventRepository;

    /**
     * @var \ScoutNet\ShScoutnetWebservice\Domain\Repository\StructureRepository
     * @Inject
     */
    protected $structureRepository;

    /**
     * @var \ScoutNet\ShScoutnetWebservice\Domain\Repository\CategoryRepository
     * @Inject
     */
    protected $categoryRepository;

    /**
     * @var \ScoutNet\ShScoutnetWebservice\Helpers\AuthHelper
     * @Inject
     */
    protected $authHelper;

    /**
     * @var \ScoutNet\ShScoutnetWebservice\Domain\Repository\BackendUserRepository
     * @Inject
     */
    protected $backendUserRepository;

    /**
     * AdministrationController constructor.
     *
     * @param \ScoutNet\ShScoutnetWebservice\Domain\Repository\EventRepository       $eventRepository
     * @param \ScoutNet\ShScoutnetWebservice\Domain\Repository\StructureRepository   $structureRepository
     * @param \ScoutNet\ShScoutnetWebservice\Domain\Repository\CategoryRepository    $categoryRepository
     * @param \ScoutNet\ShScoutnetWebservice\Helpers\AuthHelper                      $authHelper
     * @param \ScoutNet\ShScoutnetWebservice\Domain\Repository\BackendUserRepository $backendUserRepository
     */
    public function __construct(
        EventRepository $eventRepository,
        StructureRepository $structureRepository,
        CategoryRepository $categoryRepository,
        AuthHelper $authHelper,
        BackendUserRepository $backendUserRepository
    ) {
        $this->eventRepository = $eventRepository;
        $this->structureRepository = $structureRepository;
        $this->categoryRepository = $categoryRepository;
        $this->authHelper = $authHelper;
        $this->backendUserRepository = $backendUserRepository;
    }

    /**
     * action initializeAction
     */
    public function initializeAction()
    {
        parent::initializeAction();

        //set Default mapping for dates
        if (isset($this->arguments['event'])) {
            // set date format
            $mappingConfig = $this->arguments['event']->getPropertyMappingConfiguration();
            $mappingConfig->forProperty('startDate')->setTypeConverterOption(
                DateTimeConverter::class,
                DateTimeConverter::CONFIGURATION_DATE_FORMAT,
                'd.m.Y'
            );
            $mappingConfig->forProperty('endDate')->setTypeConverterOption(
                DateTimeConverter::class,
                DateTimeConverter::CONFIGURATION_DATE_FORMAT,
                'd.m.Y'
            );
        }
    }

    /**
     * @param \TYPO3\CMS\Extbase\Mvc\View\ViewInterface $view
     *
     * @throws \TYPO3\CMS\Core\Configuration\Exception\ExtensionConfigurationExtensionNotConfiguredException
     * @throws \TYPO3\CMS\Core\Configuration\Exception\ExtensionConfigurationPathDoesNotExistException
     */
    public function initializeView(ViewInterface $view)
    {
        parent::initializeView($view);

        // set the background for every Action
        $this->setBackground();
    }

    /**
     * @return bool
     * @throws \TYPO3\CMS\Core\Configuration\Exception\ExtensionConfigurationExtensionNotConfiguredException
     * @throws \TYPO3\CMS\Core\Configuration\Exception\ExtensionConfigurationPathDoesNotExistException
     * @throws \TYPO3\CMS\Extbase\Mvc\Exception\StopActionException
     */
    private function checkRights(): bool
    {
        /** @var ExtensionConfiguration $extensionConfiguration */
        $extensionConfiguration = GeneralUtility::makeInstance(ExtensionConfiguration::class);

        $ssid = $extensionConfiguration->get('sh_scoutnet_kalender', 'ScoutnetSSID');

        if (!is_numeric($ssid) or $ssid <=0) {
            return false;
        }

        $structure = $this->structureRepository->findByUid($ssid);

        /** @var \ScoutNet\ShScoutnetWebservice\Domain\Model\BackendUser $be_user */
        $be_user = $this->backendUserRepository->findByUid($GLOBALS['BE_USER']->user['uid']);

        // check if we get the login
        if (isset($_GET['logintype']) && $_GET['logintype'] === 'login' && isset($_GET['auth'])) {
            try {
                $data = $this->authHelper->getApiKeyFromData($_GET['auth']);

                $be_user->setScoutnetUsername($data['user']);
                $be_user->setScoutnetApikey($data['api_key']);

                $this->backendUserRepository->update($be_user);
            } catch (Exception $e) {
                $this->addFlashMessage('Cannot connect to Server' . $e->getMessage(), 'Error', AbstractMessage::ERROR);
                $this->view->assign('error', self::ERROR_NO_CONNECTION);
            }
        }

        if (trim($be_user->getScoutnetApikey()) == '' || trim($be_user->getScoutnetUsername()) == '') {
            // if we do not have a username or api key redirect to register
            $this->redirect('register');
        } else {
            try {
                $rights = $this->structureRepository->hasWritePermissionsToStructure($structure);

                switch ($rights['code']) {
                    case StructureRepository::AUTH_WRITE_ALLOWED:
                        // return no error
                        return true;
                    case StructureRepository::AUTH_NO_RIGHT:
                        $link = $this->controllerContext->getUriBuilder()->uriFor('requestRights');
                        $this->view->assign('error', 'You have no rights');
                        $this->view->assign('errorID', self::ERROR_NO_RIGHTS);
                        $this->view->assign('errorArguments', [$link]);
                        break;
                    case StructureRepository::AUTH_PENDING:
                        $this->view->assign('error', 'Your Rights are Pending');
                        $this->view->assign('errorID', self::ERROR_RIGHTS_PENDING);
                        break;
                    default:
                        $this->addFlashMessage('Cannot connect to Server', AbstractMessage::ERROR);
                        $this->view->assign('error', self::ERROR_UNKNOWN_ERROR);
                        break;
                }
            } catch (Exception $e) {
                $this->addFlashMessage('Cannot connect to Server' . $e->getMessage(), 'Error', AbstractMessage::ERROR);
                $this->view->assign('error', self::ERROR_NO_CONNECTION);
            }
        }

        return false;
    }

    /**
     * @throws \TYPO3\CMS\Core\Configuration\Exception\ExtensionConfigurationExtensionNotConfiguredException
     * @throws \TYPO3\CMS\Core\Configuration\Exception\ExtensionConfigurationPathDoesNotExistException
     * @throws \TYPO3\CMS\Extbase\Mvc\Exception\StopActionException
     */
    public function listAction()
    {
        if ($this->checkRights()) {
            /** @var ExtensionConfiguration $extensionConfiguration */
            $extensionConfiguration = GeneralUtility::makeInstance(ExtensionConfiguration::class);

            $ssid = $extensionConfiguration->get('sh_scoutnet_kalender', 'ScoutnetSSID');

            try {
                $filter = [
                    'order' => 'start_time desc',
                ];

                // load Events from ScoutNet
                $structure = $this->structureRepository->findByUid($ssid);

                $this->view->assign('structure', $structure);
                $this->view->assign('events', $this->eventRepository->findByStructureAndFilter($structure, $filter));
            } catch (Exception $e) {
                $this->addFlashMessage('Cannot connect to Server' . $e->getMessage(), 'Error', AbstractMessage::ERROR);
                $this->view->assign('error', self::ERROR_NO_CONNECTION);
            }
        }
    }

    /**
     * @param \ScoutNet\ShScoutnetWebservice\Domain\Model\Structure  $structure
     * @param \ScoutNet\ShScoutnetWebservice\Domain\Model\Event|null $event
     *
     * @throws \TYPO3\CMS\Core\Configuration\Exception\ExtensionConfigurationExtensionNotConfiguredException
     * @throws \TYPO3\CMS\Core\Configuration\Exception\ExtensionConfigurationPathDoesNotExistException
     * @throws \TYPO3\CMS\Extbase\Mvc\Exception\StopActionException
     */
    public function newAction(Structure $structure, Event $event = null)
    {
        if ($this->checkRights()) {
            if ($event === null) {
                $event = new Event();
                $event->setStructure($structure);
                $event->setStartDate(new DateTime());
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
    public function templateAction(Event $event)
    {
        $this->_loadAllCategories($event->getStructure(), $event);

        $newEvent = new Event();
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
     * @param \array                                            $categories
     * @param \array                                            $customCategories
     *
     * @throws \TYPO3\CMS\Core\Configuration\Exception\ExtensionConfigurationExtensionNotConfiguredException
     * @throws \TYPO3\CMS\Core\Configuration\Exception\ExtensionConfigurationPathDoesNotExistException
     * @throws \TYPO3\CMS\Extbase\Mvc\Exception\StopActionException
     */
    public function createAction(Event $event, array $categories, array $customCategories)
    {
        if ($this->checkRights()) {
            $categoryObjects = [];
            foreach ($categories as $uid => $selected) {
                // skip not selected
                if ($selected == 0) {
                    continue;
                }

                $categoryObjects[] = $this->categoryRepository->findByUid($uid);
            }

            foreach ($customCategories as $category) {
                if (strlen(trim($category)) > 0) {
                    $cat = new Category();
                    $cat->setText(trim($category));
                    $categoryObjects[] = $cat;
                }
            }

            $event->setCategories($categoryObjects);

            try {
                $this->eventRepository->add($event);

                $this->addFlashMessage('event created');
            } catch (Exception $e) {
                $this->addFlashMessage('Cannot connect to Server' . $e->getMessage(), 'Error', AbstractMessage::ERROR);
                $this->view->assign('error', self::ERROR_NO_CONNECTION);
            }
        }

        $this->redirect('list');
    }

    /**
     * action edit
     *
     * @param \ScoutNet\ShScoutnetWebservice\Domain\Model\Event $event
     *
     * @throws \TYPO3\CMS\Core\Configuration\Exception\ExtensionConfigurationExtensionNotConfiguredException
     * @throws \TYPO3\CMS\Core\Configuration\Exception\ExtensionConfigurationPathDoesNotExistException
     * @throws \TYPO3\CMS\Extbase\Mvc\Exception\StopActionException
     * @TYPO3\CMS\Extbase\Annotation\IgnoreValidation("event")
     */
    public function editAction(Event $event)
    {
        if ($this->checkRights()) {
            $this->_loadAllCategories($event->getStructure(), $event);

            // set event
            $this->view->assign('event', $event);
            $this->view->assign('verband', $event->getStructure()->getVerband());
        }
    }

    /**
     * action edit
     *
     * @param \ScoutNet\ShScoutnetWebservice\Domain\Model\Event $event
     * @param \array                                            $categories
     * @param \array                                            $customCategories
     *
     * @throws \TYPO3\CMS\Core\Configuration\Exception\ExtensionConfigurationExtensionNotConfiguredException
     * @throws \TYPO3\CMS\Core\Configuration\Exception\ExtensionConfigurationPathDoesNotExistException
     * @throws \TYPO3\CMS\Extbase\Mvc\Exception\StopActionException
     */
    public function updateAction(Event $event, array $categories, array $customCategories)
    {
        if ($this->checkRights()) {
            $categoryObjects = [];
            foreach ($categories as $uid => $selected) {
                // skip not selected
                if ($selected == 0) {
                    continue;
                }

                $categoryObjects[] = $this->categoryRepository->findByUid($uid);
            }

            foreach ($customCategories as $category) {
                if (strlen(trim($category)) > 0) {
                    $cat = new Category();
                    $cat->setText(trim($category));
                    $categoryObjects[] = $cat;
                }
            }

            $event->setCategories($categoryObjects);

            try {
                $this->eventRepository->update($event);

                $this->addFlashMessage('event saved');
            } catch (Exception $e) {
                $this->addFlashMessage('Cannot connect to Server' . $e->getMessage(), 'Error', AbstractMessage::ERROR);
                $this->view->assign('error', self::ERROR_NO_CONNECTION);
            }
        }
        $this->redirect('list');
    }

    /**
     * @param \ScoutNet\ShScoutnetWebservice\Domain\Model\Event $event
     * @TYPO3\CMS\Extbase\Annotation\IgnoreValidation("event")
     */
    public function deleteAction(Event $event)
    {
        // set event
        $this->view->assign('event', $event);
    }

    /**
     * @param \ScoutNet\ShScoutnetWebservice\Domain\Model\Event $event
     *
     * @throws \TYPO3\CMS\Core\Configuration\Exception\ExtensionConfigurationExtensionNotConfiguredException
     * @throws \TYPO3\CMS\Core\Configuration\Exception\ExtensionConfigurationPathDoesNotExistException
     * @throws \TYPO3\CMS\Extbase\Mvc\Exception\StopActionException
     * @TYPO3\CMS\Extbase\Annotation\IgnoreValidation("event")
     */
    public function removeAction(Event $event)
    {
        if ($this->checkRights()) {
            try {
                $this->eventRepository->delete($event);

                $this->addFlashMessage('event Deleted');
            } catch (Exception $e) {
                $this->addFlashMessage('Cannot connect to Server' . $e->getMessage(), 'Error', AbstractMessage::ERROR);
                $this->view->assign('error', self::ERROR_NO_CONNECTION);
            }
        }
        $this->redirect('list');
    }

    /**
     * @param \ScoutNet\ShScoutnetWebservice\Domain\Model\Structure  $structure
     * @param \ScoutNet\ShScoutnetWebservice\Domain\Model\Event|null $event
     */
    private function _loadAllCategories(Structure $structure, Event $event = null)
    {
        $categories = $this->categoryRepository->getAllCategoriesForStructureAndEvent($structure, $event);

        $this->view->assign('forcedCategories', $categories['forcedCategories']);
        $this->view->assign('allSectionCategories', $categories['allSectCategories']);
        $this->view->assign('generatedCategories', $categories['generatedCategories']);

        $this->view->assign('forcedCategoriesLabel', array_keys($structure->getForcedCategories())[1]);
    }

    /**
     * does nothing
     */
    public function registerAction()
    {
    }

    /**
     * @throws \TYPO3\CMS\Core\Configuration\Exception\ExtensionConfigurationExtensionNotConfiguredException
     * @throws \TYPO3\CMS\Core\Configuration\Exception\ExtensionConfigurationPathDoesNotExistException
     * @throws \TYPO3\CMS\Extbase\Mvc\Exception\StopActionException
     */
    public function requestRightsAction()
    {
        /** @var ExtensionConfiguration $extensionConfiguration */
        $extensionConfiguration = GeneralUtility::makeInstance(ExtensionConfiguration::class);

        $ssid = $extensionConfiguration->get('sh_scoutnet_kalender', 'ScoutnetSSID');

        try {
            $structure = $this->structureRepository->findByUid($ssid);
            $this->structureRepository->requestWritePermissionsForStructure($structure);
        } catch (Exception $e) {
            $this->addFlashMessage('Cannot connect to Server' . $e->getMessage(), 'Error', AbstractMessage::ERROR);
            $this->view->assign('error', self::ERROR_NO_CONNECTION);
        }

        $this->redirect('list');
    }

    /**
     * Set the Background color for the calendar we use
     *
     * @throws \TYPO3\CMS\Core\Configuration\Exception\ExtensionConfigurationExtensionNotConfiguredException
     * @throws \TYPO3\CMS\Core\Configuration\Exception\ExtensionConfigurationPathDoesNotExistException
     */
    private function setBackground()
    {
        /** @var ExtensionConfiguration $extensionConfiguration */
        $extensionConfiguration = GeneralUtility::makeInstance(ExtensionConfiguration::class);

        $ssid = $extensionConfiguration->get('sh_scoutnet_kalender', 'ScoutnetSSID');

        $nationalAssociations = [
            'BDP' => ['name' => 'bdp', 'color' => '#3333cc'],
            'DPSG' => ['name' => 'dpsg', 'color' => '#C1B38F'],
            'PSG' => ['name' => 'psg', 'color' => '#99ccff'],
            'VCP' => ['name' => 'vcp', 'color' => '#ccccff'],
            '' => ['name'=>'wosm', 'color' => '#622599'],
        ];

        $nationalAssociation = array_rand($nationalAssociations);

        if ((int)$ssid !== 0) {
            try {
                $kalender = $this->structureRepository->findByUid($ssid);

                if (isset($nationalAssociations[$kalender->getVerband()])) {
                    $nationalAssociation = $kalender->getVerband();
                }
            } catch (Exception $e) {
                // do nothing
            }
        }

        $this->view->assign('background_color', $nationalAssociations[$nationalAssociation]['color']);
        $this->view->assign('background_image', 'img/stoff_tile_' . $nationalAssociations[$nationalAssociation]['name'] . '.png');
    }
}
