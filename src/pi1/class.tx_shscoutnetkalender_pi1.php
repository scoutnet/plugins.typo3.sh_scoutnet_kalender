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


require_once(\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath('sh_scoutnet_webservice') . 'sn/class.tx_shscoutnetwebservice_sn.php');

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
		$GLOBALS["TSFE"]->set_no_cache();

		$this->conf=$conf;
		$this->pi_setPiVarDefaults();
		$this->pi_loadLL();

		
		$cssFile = $GLOBALS['TSFE']->tmpl->getFileName($this->conf["cssFile"]);

		$content = '<link rel="stylesheet" type="text/css" href="'.$cssFile.'" media="screen" />'."\n".
			'<script type="text/javascript" src="http://kalender.scoutnet.de/2.0/templates/scoutnet/behavior.js"></script>'."\n".
			'<script type="text/javascript" src="http://kalender.scoutnet.de/js/base2-p.js"></script>'."\n".
			'<script type="text/javascript" src="http://kalender.scoutnet.de/js/base2-dom-p.js"></script>'."\n".
			'<style type="text/css" media="all"> .snk-termin-infos{ display:none; }</style>'."\n".
			'<script type="text/javascript">'."\n".
				'base2.DOM.bind(document);'."\n".
				'snk_init();'."\n".
				'document.addEventListener(\'DOMContentLoaded\', function(){ return snk_finish(\'\'); }, false);'."\n".
			'</script>'."\n";
		
		$ids = explode(",",$this->cObj->data["tx_shscoutnetkalender_ids"]);

		$events = array();
		try {
			$SN = new tx_shscoutnetwebservice_sn();

			$filter = array(
				'limit'=> isset($this->conf["limit"]) ? $this->conf["limit"] : 999,
				'after'=>'now()',
			);

			if (isset($this->cObj->data["tx_shscoutnetkalender_kat_ids"]) && trim($this->cObj->data["tx_shscoutnetkalender_kat_ids"])) {
				$filter['kategories'] = explode(",",$this->cObj->data["tx_shscoutnetkalender_kat_ids"]);
			}

			if (isset($this->cObj->data["tx_shscoutnetkalender_stufen_ids"]) && trim($this->cObj->data["tx_shscoutnetkalender_stufen_ids"])) {
				$filter['stufen'] = explode(",",$this->cObj->data["tx_shscoutnetkalender_stufen_ids"]);
			}

			if (isset($this->piVars['addids']) && count($this->piVars['addids']) > 0 && is_array($this->piVars['addids'])) {
				$ids = array_merge($ids,$this->piVars["addids"]);
			}

			$events = $SN->get_events_for_global_id_with_filter($ids,$filter);

			$optionalKalenders = Array();
			if (isset($this->cObj->data["tx_shscoutnetkalender_optids"]) && trim($this->cObj->data["tx_shscoutnetkalender_optids"])) {
				$optids = explode(",",$this->cObj->data["tx_shscoutnetkalender_optids"]);
				$optionalKalenders = $SN->get_kalender_by_global_id($optids);
			}


			//$templatecode = $this->cObj->fileResource($templateflex_file?'uploads/tx_shscoutnetkalender/' . $templateflex_file:$this->conf['templateFile']);

			$templatecode = $this->cObj->fileResource($this->conf["templateFile"]);

			$templatecode = $this->cObj->getSubpart($templatecode,"###TEMPLATE_SCOUTNET###");


			$stammesAuswahl = "";

			$addKalenderForm = "";

			if (count($optionalKalenders) > 0) {
				$stammesAuswahl = $this->cObj->getSubpart($templatecode,"###STAMMESAUSWAHL###");

				$addKalenderForm = '<form action="'.$this->pi_getPageLink($GLOBALS['TSFE']->id).'" method="post">';
				$i = 0;
				foreach ($optionalKalenders as $kalender) { 
					$addKalenderForm .= '<input onchange="form.submit();" '.(in_array($kalender['ID'],$ids)?'checked':'').' name="'.$this->prefixId.'[addids][]" value="'.$kalender['ID'].'" id="add_id_'.$kalender['ID'].'" title="'.$kalender->get_Name().'" type="checkbox" /><label for="add_id_'.$kalender['ID'].'">&nbsp;'.$kalender->get_Name().'</label>'."\n";
					$i++;

					if ($i > 3) {
						$i = 0;
						$addKalenderForm .= '<br>';
					}
				}
				$addKalenderForm .= '</form><br>';

				$stammesAuswahl = $this->cObj->substituteMarkerArray($stammesAuswahl,array(
					"###ADD_KALENDER_FORM###"=>$addKalenderForm,
					'###STAMMESAUSWAHL_LABEL###' => $this->pi_getLL('stammesAuswahl'),
				));
			}

			$templatecode = $this->cObj->substituteSubpart($templatecode,"###STAMMESAUSWAHL###",$stammesAuswahl);


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

			foreach ($events as $event) {
				$new_monat = strftime("%Y%m",$event['Start']);

				if ($new_monat != $monat) {
					$subarray = array(
						'###MONATS_NAME###'=>htmlentities(strftime("%B '%y",$event['Start']), ENT_COMPAT | ENT_HTML401, 'UTF-8'),
					);

					$subcontent .= $this->cObj->substituteMarkerArray($monats_header_template,$subarray);
					$monat = $new_monat;
				}

				$stufen = $event->get_Stufen_Images();

				$kategorien = "";

				foreach ($event['Keywords'] as $kategorie) {
					if ($kategorien != "")
						$kategorien .= ", ";
					$kategorien .= $kategorie;
				}

				$datum = substr(strftime("%A",$event['Start']),0,2).",&nbsp;".strftime("%d.%m.",$event['Start']);

				if (isset($event['End']) && strftime("%d%m%Y",$event['Start']) != strftime("%d%m%Y",$event['End']) ) {
					$datum .= "&nbsp;-&nbsp;";
					$datum .= substr(strftime("%A",$event['End']),0,2).",&nbsp;".strftime("%d.%m.",$event['End']);
				}


				$zeit = "";
				if ($event['All_Day'] != 1) {
					$zeit = strftime("%H:%M",$event['Start']);


					if (isset($event['End']) && strftime("%H%M",$event['Start']) != strftime("%H%M",$event['End']) ) {
						$zeit .= "&nbsp;-&nbsp;";
						$zeit .= strftime("%H:%M",$event['End']);
					}
				}

				$ebene = $event['Kalender']->get_long_Name();

				$ebene = str_replace(" ","&nbsp;",$ebene);

				$showDetails = trim($event['Description']).trim($event['ZIP']).trim($event['Location']).trim($event['Organizer']).trim($event['Target_Group']).trim($event['URL']);

				$event_id = null;
				if (isset($this->piVars['event_id']) && intval($this->piVars['event_id']) > 0) {
					$event_id = intval($this->piVars["event_id"]);
				}

				$titel = ($showDetails?'<a href="#snk-termin-'.$event['ID'].'" name="snk-termin-'.$event['ID'].'" class="snk-termin-link'.($event_id === intval($event['ID'])?'-opened':'').'" onclick="if(snk_show_termin) return snk_show_termin('.$event['ID'].',this);">':'').nl2br(htmlentities($event['Title'],ENT_COMPAT,'UTF-8')).($showDetails?'</a>':'');

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
					$detail_template = $this->cObj->substituteSubpart($detail_template,"###CONTENT_DESCRIPTION###",trim($event['Description'])?$this->cObj->getSubpart($detail_template,"###CONTENT_DESCRIPTION###"):"");
					$detail_template = $this->cObj->substituteSubpart($detail_template,"###CONTENT_ORT###",trim($event['ZIP']).trim($event['Location'])?$this->cObj->getSubpart($detail_template,"###CONTENT_ORT###"):"");
					$detail_template = $this->cObj->substituteSubpart($detail_template,"###CONTENT_ORGANIZER###",trim($event['Organizer'])?$this->cObj->getSubpart($detail_template,"###CONTENT_ORGANIZER###"):"");
					$detail_template = $this->cObj->substituteSubpart($detail_template,"###CONTENT_TARGET_GROUP###",trim($event['Target_Group'])?$this->cObj->getSubpart($detail_template,"###CONTENT_TARGET_GROUP###"):"");
					$detail_template = $this->cObj->substituteSubpart($detail_template,"###CONTENT_URL###",trim($event['URL'])?$this->cObj->getSubpart($detail_template,"###CONTENT_URL###"):"");

					$subarray_tmp = $subarray;
					$subarray = array(
						'###EINTRAG_ID###'=>$event['ID'],
						'###DESCRIPTION###'=>htmlentities($event['Description'],ENT_COMPAT,'UTF-8'),
						'###ORT###'=>htmlentities($event['ZIP']." ".$event['Location'],ENT_COMPAT,'UTF-8'),
						'###ORGANIZER###'=>htmlentities($event['Organizer'],ENT_COMPAT,'UTF-8'),
						'###TARGET_GROUP###'=>htmlentities($event['Target_Group'],ENT_COMPAT,'UTF-8'),
						'###URL###'=>'<a target="_blank" href="'.htmlentities($event['URL'],ENT_COMPAT,'UTF-8').'">'.(trim($event['URL_Text'])?htmlentities($event['URL_Text'],ENT_COMPAT,'UTF-8'):htmlentities($event['URL'],ENT_COMPAT,'UTF-8')).'</a>',
						'###AUTHOR###'=>$event->get_Author_name(),

						'###DESCRIPTION_LABEL###' => $this->pi_getLL('description'),
						'###ORT_LABEL###' => $this->pi_getLL('ort'),
						'###ORGANIZER_LABEL###' => $this->pi_getLL('organizer'),
						'###TARGET_GROUP_LABEL###' => $this->pi_getLL('targetGroup'),
						'###URL_LABEL###' => $this->pi_getLL('url'),
						'###AUTHOR_LABEL###' => $this->pi_getLL('author'),
						'###EINTRAG_STYLE###' => $event_id === intval($event['ID'])?' style="display: table-row;"':'',

						'###EBENE_LABEL###' => $this->pi_getLL('ebene'),
						'###DATE_LABEL###' => $this->pi_getLL('date'),
						'###TIME_LABEL###' => $this->pi_getLL('time'),
						'###TITLE_LABEL###' => $this->pi_getLL('title'),
						'###STUFE_LABEL###' => $this->pi_getLL('stufe'),
						'###CLASSES_LABEL###' => $this->pi_getLL('classes'),
					);
                                        
					// Stufen: ID der ersten Stufe mit ins Template geben
					if(is_object($event)) {
						$stufen = $event["Stufen"];
					}
					$stufen_firstchild = $stufen[0];
					if(is_object($stufen_firstchild)) {
						$stufen_id = $stufen_firstchild->offsetGet("id");
						$subarray["###STUFEN_ID###"] = $stufen_id;
					}
					
					// Label der Stufen kommasepariert ins Template geben
					if(is_array($stufen)) {
						$stufen_label = array();
						foreach ($stufen as $stufe) {
							if(is_object($stufe)) {
								$stufe_id = $stufe["id"];
								$stufen_label[] = $this->pi_getLL("label_stufe_" . $stufe_id);
							}
						}
						$subarray["###STUFEN_LABEL###"] = implode(", ", $stufen_label);
					}
					
					$subarray = array_merge($subarray, $subarray_tmp);

					$subcontent .= $this->cObj->substituteMarkerArray($detail_template,$subarray);
				}

			}

			$subarray = array (
				'###TERMIN_HINZUFUEGEN_LINK###'=>'<a href="https://www.scoutnet.de/community/kalender/events.html?task=create&amp;SSID='.$ids[0].'" target="_top">'.$this->pi_getLL('addEvent').'</a>',
				'###POWERED_BY_LINK###' => $this->pi_getLL('powered_by').' <span><a href="http://www.scoutnet.de/technik/kalender/" target="_top">ScoutNet.DE</a></span>',
				'###KALENDER_ID###' => $ids[0],


				'###EBENE_LABEL###' => $this->pi_getLL('ebene'),
				'###DATE_LABEL###' => $this->pi_getLL('date'),
				'###TIME_LABEL###' => $this->pi_getLL('time'),
				'###TITLE_LABEL###' => $this->pi_getLL('title'),
				'###STUFE_LABEL###' => $this->pi_getLL('stufe'),
				'###CLASSES_LABEL###' => $this->pi_getLL('classes'),
			);



			$templatecode = $this->cObj->substituteMarkerArray($templatecode,$subarray);

			$content .= $this->cObj->substituteSubpart($templatecode,"###TEMPLATE_CONTENT###",$subcontent);
		} catch(Exception $e) {
			$content .= '<span class="termin">'.$this->pi_getLL('snkDown').'('.$e->getMessage().')'.'</span>';
		}
	
		return $this->pi_wrapInBaseClass($content);
	}
}



if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/sh_scoutnet_kalender/pi1/class.tx_shscoutnetkalender_pi1.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/sh_scoutnet_kalender/pi1/class.tx_shscoutnetkalender_pi1.php']);
}

?>
