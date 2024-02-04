<?php

namespace ScoutNet\ShScoutnetKalender\ViewHelpers;

use ScoutNet\ShScoutnetWebservice\Exceptions\ScoutNetExceptionMissingConfVar;
use ScoutNet\ShScoutnetWebservice\Helpers\ScoutNetConnectHelper;
use TYPO3\CMS\Backend\Routing\Exception\RouteNotFoundException;
use TYPO3\CMS\Backend\Routing\UriBuilder;
use TYPO3\CMS\Core\Configuration\Exception\ExtensionConfigurationExtensionNotConfiguredException;
use TYPO3\CMS\Core\Configuration\Exception\ExtensionConfigurationPathDoesNotExistException;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;

/***************************************************************
 *
 *  Copyright notice
 *
 *  (c) 2015 Stefan "Mütze" Horst <muetze@scoutnet.de>, ScoutNet
 *
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

class ScoutNetConnectButtonViewHelper extends AbstractViewHelper
{
    /**
     * @return string
     * @throws ScoutNetExceptionMissingConfVar
     * @throws RouteNotFoundException
     * @throws ExtensionConfigurationExtensionNotConfiguredException
     * @throws ExtensionConfigurationPathDoesNotExistException
     */
    public function render(): string
    {
        /** @var UriBuilder $uriBuilder */
        $uriBuilder = GeneralUtility::makeInstance(UriBuilder::class);
        $uri = $uriBuilder->buildUriFromRoute('user_ShScoutnetKalenderScoutnet', []);

        /** @var ScoutNetConnectHelper $scoutNetConnectHelper */
        $scoutNetConnectHelper = GeneralUtility::makeInstance(ScoutNetConnectHelper::class);
        return $scoutNetConnectHelper->getScoutNetConnectLoginButton($uri, true);
    }
}
