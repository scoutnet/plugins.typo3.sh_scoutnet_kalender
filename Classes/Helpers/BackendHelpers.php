<?php

namespace ScoutNet\ShScoutnetKalender\Helpers;

use ScoutNet\ShScoutnetWebservice\Domain\Repository\CategoryRepository;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/***************************************************************
 *  Copyright notice
 *
 *  (c) 2016 Stefan "Mütze" Horst <muetze@scoutnet.de>
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
class BackendHelpers {

    /**
     * @param array $fConfig
     *
     * @return void
     */
    public function getCategories(array &$fConfig) {
        /** @var CategoryRepository $categorieRepository */
        $categorieRepository = GeneralUtility::makeInstance(CategoryRepository::class);

   //     $configurationManager = $objectManager->get('TYPO3\\CMS\Extbase\\Configuration\\ConfigurationManager');
   //     $configuration = $configurationManager->getConfiguration(ConfigurationManager::CONFIGURATION_TYPE_SETTINGS, 'ShScoutnetKalender');

        $categories = $categorieRepository->findAll();
        // change conf
        foreach ($categories as $categorie) {
            array_push($fConfig['items'], [
                $categorie->getText(),
                $categorie->getUid()
            ]);
        }
    }

}