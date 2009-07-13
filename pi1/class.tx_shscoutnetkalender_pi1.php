<?php
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


require_once(PATH_tslib.'class.tslib_pibase.php');
require_once('jsonRPCClient.php');

class tx_shscoutnetkalender_pi1 extends tslib_pibase {
	var $prefixId = 'tx_shscoutnetkalender_pi1';		// Same as class name
	var $scriptRelPath = 'pi1/class.tx_shscoutnetkalender_pi1.php';	// Path to this script relative to the extension dir.
	var $extKey = 'sh_scoutnet_kalender';	// The extension key.
	var $pi_checkCHash = TRUE;
	
	/**
	 * The main method of the PlugIn
	 *
	 * @param	string		$content: The PlugIn content
	 * @param	array		$conf: The PlugIn configuration
	 * @return	The content that is displayed on the website
	 */
	function main($content,$conf)	{
		$this->conf=$conf;
		$this->pi_setPiVarDefaults();
		$this->pi_loadLL();
		

		$ids_text = $this->cObj->data["tx_shscoutnetkalender_ids"];
		$kat_ids_text = $this->cObj->data["tx_shscoutnetkalender_kat_ids"];

	
		$content='<div class="rahmen_right">';

		
		$ids = split(",",$ids_text);

		$res = array();
		try {
			$SN = new jsonRPCClient("http://www.scoutnet.de/jsonrpc/server.php");

			$res = $SN->get_data_by_global_id($ids,array('events'=>array('limit'=>'20','after'=>'now()')));
		} catch(Exception $e) {
			$content .= "<span class='termin'>zZ ist der Scoutnet Kalender down.<br>Bitte versuch es zu einem sp&auml;teren Zeitpunkt noch mal</span>";
		}

		//$templatecode = $this->cObj->fileResource($templateflex_file?'uploads/tx_shscoutnetkalender/' . $templateflex_file:$this->conf['templateFile']);
		$templatecode = $this->cObj->fileResource('uploads/tx_shscoutnetkalender/template.html');

		$templatecode = $this->cObj->getSubpart($templatecode,"###TEMPLATE_SCOUTNET###");


		$headerEbene = "";
		$contentEbene = "";

		if (count($ids) > 1) {
			$headerEbene = $this->cObj->getSubpart($templatecode,"###HEADER_EBENE###");
			$contentEbene = $this->cObj->getSubpart($templatecode,"###CONTENT_EBENE###");
		}

		$templatecode = $this->cObj->substituteSubpart($templatecode,"###HEADER_EBENE###",$headerEbene);
		$templatecode = $this->cObj->substituteSubpart($templatecode,"###CONTENT_EBENE###",$contentEbene);


		$subcontent = "";
		$termin_template = $this->cObj->getSubpart($templatecode,"###TEMPLATE_TERMIN###");
		$termin_detail_template = $this->cObj->getSubpart($templatecode,"###TEMPLATE_DETAILS###");
		foreach ($res as $record) {
			if ($record['type'] === 'event') {
				$line = $record['content'];

				#print_r($record);

				/*$start_date = "";
				if (ereg ("[0-9]{2}([0-9]{2})-([0-9]{1,2})-([0-9]{1,2})", $line['Start_Date'], $regs)) {
					    $start_date = "$regs[3].$regs[2].$regs[1]";
				}*/
				$start_date = strftime("%d.%m.%y",$line['start']);



				$stufen ="";
				
				foreach ($line['stufen'] as $stufe) {
					$stufen .= "<img src='http://kalender.scoutnet.de/2.0/images/".$stufe['id'].".gif' alt='".htmlentities($stufe['bezeichnung'])."' />";
				}

				$kategorien = "";

				foreach ($line['kategories'] as $kategorie) {
					if ($kategorien != "")
						$kategorien .= ", ";
					$kategorien .= utf8_decode($kategorie);
				}

				$datum = substr(strftime("%A",$line['start']),0,2).",&nbsp;".strftime("%d.%m.",$line['start']);

				if (isset($line['end']) && strftime("%d%m%Y",$line['start']) != strftime("%d%m%Y",$line['end']) ) {
					$datum .= "&nbsp;-&nbsp;";
					$datum .= substr(strftime("%A",$line['end']),0,2).",&nbsp;".strftime("%d.%m.",$line['end']);
				}


				$zeit = "";
				if ($line['allday'] != 1) {
					$zeit = strftime("%H:%M",$line['start']);
				

					if (isset($line['end']) && strftime("%H%M",$line['start']) != strftime("%H%M",$line['end']) ) {
						$zeit .= "&nbsp;-&nbsp;";
						$zeit .= strftime("%H:%M",$line['end']);
					}
				}

				$ebene = $line['kalender']['ebene'].(($line['kalender']['ebene_id'] >= 7)?" ".$line['kalender']['name']:"");

				$ebene = str_replace(" ","&nbsp;",htmlentities(utf8_decode($ebene)));

				$subarray = array(
						'###EBENE###'=>$ebene,
						'###DATUM###'=>$datum,
						'###ZEIT###'=>$zeit,
						'###TITEL###'=>utf8_Decode($line['title']),
						'###STUFE###'=>$stufen,
						'###KATEGORIE###'=>$kategorien,
					);

				$subcontent .= $this->cObj->substituteMarkerArray($termin_template,$subarray);

				if (trim($line['Description']).trim($line['ZIP']).trim($line['Location']).trim($line['organizer']).trim($line['targetGroup']).trim($line['URL'])) {
				
					$detail_template = $termin_detail_template;
					$detail_template = $this->cObj->substituteSubpart($detail_template,"###CONTENT_DESCRIPTION###",trim($line['Description'])?$this->cObj->getSubpart($detail_template,"###CONTENT_DESCRIPTION###"):"");
					$detail_template = $this->cObj->substituteSubpart($detail_template,"###CONTENT_ORT###",trim($line['ZIP']).trim($line['Location'])?$this->cObj->getSubpart($detail_template,"###CONTENT_ORT###"):"");
					$detail_template = $this->cObj->substituteSubpart($detail_template,"###CONTENT_ORGANIZER###",trim($line['organizer'])?$this->cObj->getSubpart($detail_template,"###CONTENT_ORGANIZER###"):"");
					$detail_template = $this->cObj->substituteSubpart($detail_template,"###CONTENT_TARGET_GROUP###",trim($line['targetGroup'])?$this->cObj->getSubpart($detail_template,"###CONTENT_TARGET_GROUP###"):"");
					$detail_template = $this->cObj->substituteSubpart($detail_template,"###CONTENT_URL###",trim($line['URL'])?$this->cObj->getSubpart($detail_template,"###CONTENT_URL###"):"");

					$subarray = array(
						'###EINTRAG_ID###'=>$line['id'],
						'###DESCRIPTION###'=>utf8_decode($line['Description']),
						'###ORT###'=>htmlentities(utf8_decode($line['ZIP']." ".$line['Location'])),
						'###ORGANIZER###'=>htmlentities(utf8_decode($line['organizer'])),
						'###TARGET_GROUP###'=>htmlentities(utf8_decode($line['targetGroup'])),
						'###URL###'=>'<a target="_blank" href="'.htmlentities(utf8_decode($line['URL'])).'>'.(trim($line['URL_Text'])?htmlentities(utf8_decode($line['URL_Text'])):htmlentities(utf8_decode($line['URL']))).'</a>',
						'###AUTHOR###'=>htmlentities(utf8_decode($line['lastModifier'] != ""?$line['lastModifier']:$line['creator'])),
					);

					$subcontent .= $this->cObj->substituteMarkerArray($detail_template,$subarray);
				}

			//	."<span class='termin'><span class='termin_date'>".$start_date."</span>".
			//		" <span class='termin_text'><a href='/veranstaltungen/kalender/?no_cache=1'>".utf8_Decode($line['title'])."</a></span></span>\n";
				//
				//
				//

			}

		}

		$subarray = array (
			'###TERMIN_HINZUFUEGEN_LINK###'=>'<a href="https://www.scoutnet.de/community/kalender/events.html?task=create&amp;SSID='.$ids[0].'" target="_top">Termin&nbsp;hinzuf&uuml;gen</a>',
			'###POWERED_BY_LINK###' => 'Powered by <span><a href="http://kalender.scoutnet.de/" target="_top">ScoutNet.DE</a></span>',
			'###KALENDER_ID###' => $ids[0],
		);



		$templatecode = $this->cObj->substituteMarkerArray($templatecode,$subarray);

		$content .= $this->cObj->substituteSubpart($templatecode,"###TEMPLATE_MONAT###",$subcontent);
	
		return $this->pi_wrapInBaseClass($content);
	}
}



if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/sh_scoutnet_kalender/pi1/class.tx_shscoutnetkalender_pi1.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/sh_scoutnet_kalender/pi1/class.tx_shscoutnetkalender_pi1.php']);
}

?>
