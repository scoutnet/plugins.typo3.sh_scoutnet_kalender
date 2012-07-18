<?php
date_default_timezone_set('Europe/Berlin');

require_once('class.typo3_lib.php');

class t3x_generator {
	public function process($path,$_EXTKEY,$buildpath) {
		$EM_CONF = array();

		require_once($path . '/ext_emconf.php');
		$extInfo = array(
			'EM_CONF' => $EM_CONF[$_EXTKEY],
		);

		$uArr = typo3_lib::makeUploadArray($path . '/', $_EXTKEY, $extInfo);
		$backupData = typo3_lib::makeUploadDataFromArray($uArr);

		$t3xFilename = $this->getT3xFilename($_EXTKEY, $path, $extInfo);

		file_put_contents(rtrim($buildpath,'/').'/'.$t3xFilename,$backupData);
	}

	protected function getT3xFilename($extKey, $path, array $extInfo) {
		// TODO: create a meaningful filename according to $path (tag name, ...)
		return 'T3X_' . $extKey . '-' . str_replace('.', '_', $extInfo['EM_CONF']['version']) . '-z-' . date('YmdHi') . '.t3x';
	}
}

$t3x_generator = new t3x_generator();
$t3x_generator->process($argv[1],$argv[2],$argv[3]);
?>
