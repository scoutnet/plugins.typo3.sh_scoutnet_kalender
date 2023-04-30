<?php

namespace ScoutNet\ShScoutnetKalender\ViewHelpers;

use TYPO3\CMS\Extbase\Persistence\Generic\QueryResult;
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;
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

class ImplodeViewHelper extends AbstractViewHelper
{
    public function initializeArguments()
    {
        parent::initializeArguments();
        $this->registerArgument('values', 'array', 'The Values to be merged together', true);
        $this->registerArgument('delimiter', 'string', 'The delimiter', false);
        $this->registerArgument('lastDelimiter', 'string', 'The last delimiter, if different from the others', false);
    }

    /**
     * @return string
     */
    public function render(): string
    {
        $values = $this->arguments['values'];
        $delimiter = $this->arguments['delimiter'];
        $lastDelimiter = $this->arguments['lastDelimiter'];

        if ($values instanceof QueryResult) {
            $values = $values->toArray();
        }

        if ($delimiter === null) {
            $delimiter = ', ';
        }

        if ($lastDelimiter === null) {
            $lastDelimiter = ' ' . LocalizationUtility::translate('community.mergeViewHelper.and', 'sh_scoutnet_community') . ' ';
        }

        if (count($values) == 0) {
            return '';
        }
        if (count($values) == 1) {
            $value = array_pop($values);

            // TODO: check alternatives
            // remove if it exists
            if ($this->templateVariableContainer->exists('object')) {
                $this->templateVariableContainer->remove('object');
            }
            $this->templateVariableContainer->add('object', $value);

            return $this->renderChildren();
        }
        $last_element = array_pop($values);
        $first_element = array_shift($values);

        // render object
        if ($this->templateVariableContainer->exists('object')) {
            $this->templateVariableContainer->remove('object');
        }
        $this->templateVariableContainer->add('object', $first_element);

        $output = $this->renderChildren();

        while (($value = array_shift($values)) !== null) {
            // render object
            if ($this->templateVariableContainer->exists('object')) {
                $this->templateVariableContainer->remove('object');
            }
            $this->templateVariableContainer->add('object', $value);

            $output .= $delimiter . $this->renderChildren();
        }

        if ($this->templateVariableContainer->exists('object')) {
            $this->templateVariableContainer->remove('object');
        }
        $this->templateVariableContainer->add('object', $last_element);
        $output .= $lastDelimiter . $this->renderChildren();

        return $output;
    }
}
