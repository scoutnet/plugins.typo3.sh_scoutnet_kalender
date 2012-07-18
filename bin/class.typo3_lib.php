<?php
/***************************************************************
 *  Copyright notice
 *
 *  (c) 2012 Xavier Perseguers <xavier@causal.ch>
 *  All rights reserved
 *
 *  This script is an extract the TYPO3 project. The TYPO3 project is
 *  free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 2 of the License, or
 *  (at your option) any later version.
 *
 *  The GNU General Public License can be found at
 *  http://www.gnu.org/copyleft/gpl.html.
 *
 *  A copy is found in the textfile GPL.txt and important notices to the license
 *  from the author is found in LICENSE.txt distributed with these scripts.
 *
 *
 *  This script is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  This copyright notice MUST APPEAR in all copies of the script!
 ***************************************************************/

define('LF', chr(10));

class typo3_lib {

	/**************************************
	 *
	 * origin: typo3/sysext/em/classes/extensions/
	 *
	 **************************************/

	/**
	 * Make upload array out of extension
	 *
	 * @param string $extPath
	 * @param string $extKey
	 * @param array $conf
	 * @return array
	 * @see tx_em_Extensions_Details::makeUploadarray()
	 */
	public static function makeUploadArray($extPath, $extKey, array $conf) {
		// Get files for extension:
		$fileArr = array();
		$fileArr = self::getAllFilesAndFoldersInPath($fileArr, $extPath, '', 0, 99, '(CVS|\..*|.*~|.*\.bak)');

		// Calculate the total size of those files:
		$totalSize = 0;
		foreach ($fileArr as $file) {
			$totalSize += filesize($file);
		}

		// Initialize output array:
		$uploadArray = array();
		$uploadArray['extKey'] = $extKey;
		$uploadArray['EM_CONF'] = $conf['EM_CONF'];
		$uploadArray['misc']['codelines'] = 0;
		$uploadArray['misc']['codebytes'] = 0;

		//$uploadArray['techInfo'] = $this->install->makeDetailedExtensionAnalysis($extKey, $conf, 1);

		// Read all files:
		foreach ($fileArr as $file) {
			$relFileName = substr($file, strlen($extPath));
			$fI = pathinfo($relFileName);
			if ($relFileName != 'ext_emconf.php') { // This file should be dynamically written...
				$uploadArray['FILES'][$relFileName] = array(
					'name' => $relFileName,
					'size' => filesize($file),
					'mtime' => filemtime($file),
					'is_executable' => is_executable($file),
					'content' => file_get_contents($file)
				);
				if (self::inList('php,inc', strtolower(@$fI['extension']))) {
					$uploadArray['FILES'][$relFileName]['codelines'] = count(explode(LF, $uploadArray['FILES'][$relFileName]['content']));
					$uploadArray['misc']['codelines'] += $uploadArray['FILES'][$relFileName]['codelines'];
					$uploadArray['misc']['codebytes'] += $uploadArray['FILES'][$relFileName]['size'];
				}
				$uploadArray['FILES'][$relFileName]['content_md5'] = md5($uploadArray['FILES'][$relFileName]['content']);
			}
		}

		// Return upload-array:
		return $uploadArray;
	}

	/**************************************
	 *
	 * origin: typo3/sysext/em/classes/connection/
	 *
	 **************************************/

	/**
	 * Encodes extension upload array
	 *
	 * @param	array		Array containing extension
	 * @return	string		Content stream
	 * @see tx_em_Connection_Ter::makeUploadDataFromarray()
	 */
	public static function makeUploadDataFromArray($uploadArray) {
		$content = '';
		if (is_array($uploadArray)) {
			$serialized = serialize($uploadArray);
			$md5 = md5($serialized);

			$content = $md5 . ':';
			$content .= 'gzcompress:';
			$content .= gzcompress($serialized);
		}
		return $content;
	}

	/**************************************
	 *
	 * origin: t3lib/
	 *
	 **************************************/

	/**
	 * Recursively gather all files and folders of a path.
	 *
	 * @param array $fileArr Empty input array (will have files added to it)
	 * @param string $path The path to read recursively from (absolute) (include trailing slash!)
	 * @param string $extList Comma list of file extensions: Only files with extensions in this list (if applicable) will be selected.
	 * @param boolean $regDirs If set, directories are also included in output.
	 * @param integer $recursivityLevels The number of levels to dig down...
	 * @param string $excludePattern regex pattern of files/directories to exclude
	 * @return array An array with the found files/directories.
	 * @see t3lib_div::getAllFilesAndFoldersInPath()
	 */
	protected static function getAllFilesAndFoldersInPath(array $fileArr, $path, $extList = '', $regDirs = FALSE, $recursivityLevels = 99, $excludePattern = '') {
		if ($regDirs) {
			$fileArr[] = $path;
		}
		$fileArr = array_merge($fileArr, self::getFilesInDir($path, $extList, 1, 1, $excludePattern));

		$dirs = self::get_dirs($path);
		if (is_array($dirs) && $recursivityLevels > 0) {
			foreach ($dirs as $subdirs) {
				if ((string)$subdirs != '' && (!strlen($excludePattern) || !preg_match('/^' . $excludePattern . '$/', $subdirs))) {
					$fileArr = self::getAllFilesAndFoldersInPath($fileArr, $path . $subdirs . '/', $extList, $regDirs, $recursivityLevels - 1, $excludePattern);
				}
			}
		}
		return $fileArr;
	}

	/**
	 * Returns an array with the names of folders in a specific path
	 * Will return 'error' (string) if there were an error with reading directory content.
	 *
	 * @param string $path Path to list directories from
	 * @return array Returns an array with the directory entries as values. If no path, the return value is nothing.
	 * @see t3lib_div::get_dirs()
	 */
	public static function get_dirs($path) {
		if ($path) {
			if (is_dir($path)) {
				$dir = scandir($path);
				$dirs = array();
				foreach ($dir as $entry) {
					if (is_dir($path . '/' . $entry) && $entry != '..' && $entry != '.') {
						$dirs[] = $entry;
					}
				}
			} else {
				$dirs = 'error';
			}
		}
		return $dirs;
	}

	/**
	 * Returns an array with the names of files in a specific path
	 *
	 * @param string $path Is the path to the file
	 * @param string $extensionList is the comma list of extensions to read only (blank = all)
	 * @param boolean $prependPath If set, then the path is prepended the file names. Otherwise only the file names are returned in the array
	 * @param string $order is sorting: 1= sort alphabetically, 'mtime' = sort by modification time.
	 * @param string $excludePattern A comma separated list of file names to exclude, no wildcards
	 * @return array Array of the files found
	 * @see t3lib_div::getFilesInDir()
	 */
	protected static function getFilesInDir($path, $extensionList = '', $prependPath = FALSE, $order = '', $excludePattern = '') {

		// Initialize variables:
		$filearray = array();
		$sortarray = array();
		$path = rtrim($path, '/');

		// Find files+directories:
		if (@is_dir($path)) {
			$extensionList = strtolower($extensionList);
			$d = dir($path);
			if (is_object($d)) {
				while ($entry = $d->read()) {
					if (@is_file($path . '/' . $entry)) {
						$fI = pathinfo($entry);
						$key = md5($path . '/' . $entry); // Don't change this ever - extensions may depend on the fact that the hash is an md5 of the path! (import/export extension)
						if ((!strlen($extensionList) || self::inList($extensionList, strtolower($fI['extension']))) && (!strlen($excludePattern) || !preg_match('/^' . $excludePattern . '$/', $entry))) {
							$filearray[$key] = ($prependPath ? $path . '/' : '') . $entry;
							if ($order == 'mtime') {
								$sortarray[$key] = filemtime($path . '/' . $entry);
							}
							elseif ($order) {
								$sortarray[$key] = $entry;
							}
						}
					}
				}
				$d->close();
			} else {
				return 'error opening path: "' . $path . '"';
			}
		}

		// Sort them:
		if ($order) {
			asort($sortarray);
			$newArr = array();
			foreach ($sortarray as $k => $v) {
				$newArr[$k] = $filearray[$k];
			}
			$filearray = $newArr;
		}

		// Return result
		reset($filearray);
		return $filearray;
	}

	/**
	 * Check for item in list
	 * Check if an item exists in a comma-separated list of items.
	 *
	 * @param string $list comma-separated list of items (string)
	 * @param string $item item to check for
	 * @return boolean TRUE if $item is in $list
	 * @see t3lib_div::inList()
	 */
	protected static function inList($list, $item) {
		return (strpos(',' . $list . ',', ',' . $item . ',') !== FALSE ? TRUE : FALSE);
	}
}

?>
