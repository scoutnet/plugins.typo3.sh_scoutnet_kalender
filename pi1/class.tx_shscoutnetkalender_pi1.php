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
require_once (t3lib_extMgm::extPath('sh_scoutnet_webservice') . 'sn/class.tx_shscoutnetwebservice_sn.php');


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
		

		$content = '<link rel="stylesheet" type="text/css" href="uploads/tx_shscoutnetkalender/kalender.css" media="screen" />'."\n".
			'<script type="text/javascript" src="http://kalender.scoutnet.de/2.0/templates/scoutnet/behavior.js"></script>'."\n".
			'<script type="text/javascript" src="http://kalender.scoutnet.de/js/base2-p.js"></script>'."\n".
			'<script type="text/javascript" src="http://kalender.scoutnet.de/js/base2-dom-p.js"></script>'."\n".
			'<style type="text/css" media="all"> .snk-termin-infos{ display:none; }</style>'."\n".
			'<script type="text/javascript">'."\n".
				'base2.DOM.bind(document);'."\n".
				'snk_init();'."\n".
				'document.addEventListener(\'DOMContentLoaded\', function(){ return snk_finish(\'\'); }, false);'."\n".
			'</script>'."\n";
		
		$ids = split(",",$this->cObj->data["tx_shscoutnetkalender_ids"]);

		$res = array();
		try {
			$SN = new tx_shscoutnetwebservice_sn();

			$filter = array(
				'limit'=>'20',
				'after'=>'now()',
			);

			if (isset($this->cObj->data["tx_shscoutnetkalender_kat_ids"]) && trim($this->cObj->data["tx_shscoutnetkalender_kat_ids"])) {
				$filter['kategories'] = split(",",$this->cObj->data["tx_shscoutnetkalender_kat_ids"]);
			}

			if (isset($this->cObj->data["tx_shscoutnetkalender_stufen_ids"]) && trim($this->cObj->data["tx_shscoutnetkalender_stufen_ids"])) {
				$filter['stufen'] = split(",",$this->cObj->data["tx_shscoutnetkalender_stufen_ids"]);
			}

			$res = $SN->get_events_by_global_id($ids,$filter);
		} catch(Exception $e) {
			$content .= "<span class='termin'>zZ ist der Scoutnet Kalender down.<br>Bitte versuch es zu einem sp&auml;teren Zeitpunkt noch mal</span>";
		}

		//$templatecode = $this->cObj->fileResource($templateflex_file?'uploads/tx_shscoutnetkalender/' . $templateflex_file:$this->conf['templateFile']);

		$templatecode = $this->cObj->fileResource($this->conf["templateFile"]);

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
		$monats_header_template = $this->cObj->getSubpart($templatecode,"###TEMPLATE_MONAT###");

		$monat = "0";

		foreach ($res as $line) {
			$new_monat = strftime("%Y%m",$line['Start']);

			if ($new_monat != $monat) {
				$subarray = array(
					'###MONATS_NAME###'=>strftime("%B '%y",$line['Start']),
				);

				$subcontent .= $this->cObj->substituteMarkerArray($monats_header_template,$subarray);
				$monat = $new_monat;
			}

			$stufen ="";

			foreach ($line['Stufen'] as $stufe) {
				$stufen .= $SN->get_stufe_by_id($stufe)->get_Image_URL();
			}

			$kategorien = "";

			foreach ($line['Keywords'] as $kategorie) {
					if ($kategorien != "")
						$kategorien .= ", ";
					$kategorien .= utf8_decode($kategorie);
			}

			$datum = substr(strftime("%A",$line['Start']),0,2).",&nbsp;".strftime("%d.%m.",$line['Start']);

			if (isset($line['End']) && strftime("%d%m%Y",$line['Start']) != strftime("%d%m%Y",$line['End']) ) {
				$datum .= "&nbsp;-&nbsp;";
				$datum .= substr(strftime("%A",$line['End']),0,2).",&nbsp;".strftime("%d.%m.",$line['End']);
			}


			$zeit = "";
			if ($line['All_Day'] != 1) {
				$zeit = strftime("%H:%M",$line['Start']);


				if (isset($line['End']) && strftime("%H%M",$line['Start']) != strftime("%H%M",$line['End']) ) {
					$zeit .= "&nbsp;-&nbsp;";
					$zeit .= strftime("%H:%M",$line['End']);
				}
			}

			$ebene = $SN->get_kalender_by_id($line['Kalender'])->get_long_Name();

			$ebene = str_replace(" ","&nbsp;",$ebene);

			$showDetails = trim($line['Description']).trim($line['ZIP']).trim($line['Location']).trim($line['Organizer']).trim($line['Target_Group']).trim($line['URL']);

			$titel = ($showDetails?'<a href="#snk-termin-'.$line['ID'].'" class="snk-termin-link" onclick="if(snk_show_termin) return snk_show_termin('.$line['ID'].',this);">':'').nl2br(htmlentities(utf8_Decode($line['Title']))).($showDetails?'</a>':'');

			$subarray = array(
				'###EBENE###'=>$ebene,
				'###DATUM###'=>$datum,
				'###ZEIT###'=>$zeit,
				'###TITEL###'=>$titel,
				'###STUFE###'=>$stufen,
				'###KATEGORIE###'=>$kategorien,
			);

			$subcontent .= $this->cObj->substituteMarkerArray($termin_template,$subarray);

			if ($showDetails) {

				$detail_template = $termin_detail_template;
				$detail_template = $this->cObj->substituteSubpart($detail_template,"###CONTENT_DESCRIPTION###",trim($line['Description'])?$this->cObj->getSubpart($detail_template,"###CONTENT_DESCRIPTION###"):"");
				$detail_template = $this->cObj->substituteSubpart($detail_template,"###CONTENT_ORT###",trim($line['ZIP']).trim($line['Location'])?$this->cObj->getSubpart($detail_template,"###CONTENT_ORT###"):"");
				$detail_template = $this->cObj->substituteSubpart($detail_template,"###CONTENT_ORGANIZER###",trim($line['Organizer'])?$this->cObj->getSubpart($detail_template,"###CONTENT_ORGANIZER###"):"");
				$detail_template = $this->cObj->substituteSubpart($detail_template,"###CONTENT_TARGET_GROUP###",trim($line['Target_Group'])?$this->cObj->getSubpart($detail_template,"###CONTENT_TARGET_GROUP###"):"");
				$detail_template = $this->cObj->substituteSubpart($detail_template,"###CONTENT_URL###",trim($line['URL'])?$this->cObj->getSubpart($detail_template,"###CONTENT_URL###"):"");

				$subarray = array(
					'###EINTRAG_ID###'=>$line['ID'],
					'###DESCRIPTION###'=>utf8_decode($line['Description']),
					'###ORT###'=>htmlentities(utf8_decode($line['ZIP']." ".$line['Location'])),
					'###ORGANIZER###'=>htmlentities(utf8_decode($line['Organizer'])),
					'###TARGET_GROUP###'=>htmlentities(utf8_decode($line['Target_Group'])),
					'###URL###'=>'<a target="_blank" href="'.htmlentities(utf8_decode($line['URL'])).'>'.(trim($line['URL_Text'])?htmlentities(utf8_decode($line['URL_Text'])):htmlentities(utf8_decode($line['URL']))).'</a>',
					'###AUTHOR###'=>htmlentities(utf8_decode($line['Last_Modified_By'] != ""?$line['Last_Modified_By']:$line['Created_By'])),
				);

				$subcontent .= $this->cObj->substituteMarkerArray($detail_template,$subarray);
			}

		}

		$subarray = array (
			'###TERMIN_HINZUFUEGEN_LINK###'=>'<a href="https://www.scoutnet.de/community/kalender/events.html?task=create&amp;SSID='.$ids[0].'" target="_top">Termin&nbsp;hinzuf&uuml;gen</a>',
			'###POWERED_BY_LINK###' => 'Powered by <span><a href="http://kalender.scoutnet.de/" target="_top">ScoutNet.DE</a></span>',
			'###KALENDER_ID###' => $ids[0],
		);



		$templatecode = $this->cObj->substituteMarkerArray($templatecode,$subarray);

		$content .= $this->cObj->substituteSubpart($templatecode,"###TEMPLATE_CONTENT###",$subcontent);
	
		return $this->pi_wrapInBaseClass($content);
	}
}



if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/sh_scoutnet_kalender/pi1/class.tx_shscoutnetkalender_pi1.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/sh_scoutnet_kalender/pi1/class.tx_shscoutnetkalender_pi1.php']);
}

?>
