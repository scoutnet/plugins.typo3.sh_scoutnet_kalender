<?php
namespace ScoutNet\ShScoutnetKalender\ViewHelpers;

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

class ImplodeViewHelper extends \TYPO3\CMS\Fluid\Core\ViewHelper\AbstractViewHelper {
	/**
	 * @param object $values
	 * @param string $delimiter
	 * @param string $lastDelimiter
	 * @return string
	 */
	public function render($values, $delimiter = ', ', $lastDelimiter = null) {
		if ($values instanceof \TYPO3\CMS\Extbase\Persistence\Generic\QueryResult) $values = $values->toArray();

		if ($lastDelimiter === null) {
			$lastDelimiter = ' '.\TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate( 'community.mergeViewHelper.and', 'sh_scoutnet_community').' ';
		}

		if (count($values) == 0) {
			return '';
		} elseif (count($values) == 1) {
			$value = array_pop($values);

			// remove if it exists
			if ($this->templateVariableContainer->exists('object')) $this->templateVariableContainer->remove('object');
			$this->templateVariableContainer->add('object', $value);

			return $this->renderChildren();
		} else {
			$last_element = array_pop($values);
			$first_element = array_shift($values);

			//\TYPO3\CMS\Extbase\Utility\DebuggerUtility::var_dump($values,'values');

			// render object
			if ($this->templateVariableContainer->exists('object')) $this->templateVariableContainer->remove('object');
			$this->templateVariableContainer->add('object', $first_element);

			$output = $this->renderChildren();

			while (($value = array_shift($values)) !== NULL) {
				// render object
				if ($this->templateVariableContainer->exists('object')) $this->templateVariableContainer->remove('object');
				$this->templateVariableContainer->add('object', $value);

				$output .= $delimiter.$this->renderChildren();
			}

			if ($this->templateVariableContainer->exists('object')) $this->templateVariableContainer->remove('object');
			$this->templateVariableContainer->add('object', $last_element);
			$output .= $lastDelimiter.$this->renderChildren();

			return $output;
		}
	}
}
