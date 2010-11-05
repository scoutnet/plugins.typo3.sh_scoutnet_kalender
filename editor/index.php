<?php

$LANG->includeLLFile('EXT:sh_scoutnet_kalender/editor/locallang.xml');


$BE_USER->modAccess($MCONF, 1);

require_once (t3lib_extMgm::extPath('sh_scoutnet_webservice') . 'sn/class.tx_shscoutnetwebservice_sn.php');

// ***************************
// Script Classes
// ***************************
class SC_mod_user_scoutnet_kalender_editor_index extends t3lib_SCbase {

	protected $pageinfo;

	protected $jsDateFktSet;

	/**
	 * Initializes the Module
	 *
	 * @return	void
	 */
	public function __construct() {
		parent::init();

		$this->jsDateFktSet = false;

			// initialize document
		$this->doc = t3lib_div::makeInstance('template');
		$this->doc->setModuleTemplate(t3lib_extMgm::extPath('sh_scoutnet_kalender') . 'editor/template_overview.html');
		$this->doc->backPath = $GLOBALS['BACK_PATH'];
		$this->doc->getPageRenderer()->loadScriptaculous('effects,dragdrop');
		//$this->doc->addStyleSheet( 'tx_shscoutnetkalender', t3lib_extMgm::siteRelPath('sh_scoutnet_kalender') . 'editor/style.css');
	       
		
		if (version_compare(TYPO3_version, '4.3', '<')) {
			$this->doc->addStyleSheet('tx_shscoutnetkalender', t3lib_extMgm::siteRelPath('sh_scoutnet_kalender') . 'editor/style.css');
			$this->doc->addStyleSheet('tx_shscoutnetkalender', t3lib_extMgm::siteRelPath('sh_scoutnet_kalender') . 'editor/kalender.css');
		} else {
			$this->doc->JScode .= '<link rel="stylesheet" type="text/css" href="' . t3lib_div::createVersionNumberedFilename(t3lib_div::resolveBackPath($this->doc->backPath . t3lib_extMgm::extRelPath('sh_scoutnet_kalender') . 'editor/style.css')) . '" />';
			$this->doc->JScode .= '<link rel="stylesheet" type="text/css" href="' . t3lib_div::createVersionNumberedFilename(t3lib_div::resolveBackPath($this->doc->backPath . t3lib_extMgm::extRelPath('sh_scoutnet_kalender') . 'editor/kalender.css')) . '" />';
		}

	}

	/**
	 * Creates the module's content. In this case it rather acts as a kind of #
	 * dispatcher redirecting requests to specific tasks.
	 *
	 * @return	void
	 */
	public function main() {

		
		$ids = array(4);

		$docHeaderButtons = $this->getButtons();

		$markers = array();
		$subpartMarkers = array();

		$this->doc->JScodeArray[] = '
			script_ended = 0;
			function jumpToUrl(URL) {
				document.location = URL;
			}
		';
		$this->doc->postCode='
			<script language="javascript" type="text/javascript">
				script_ended = 1;
				if (top.fsMod) {
					top.fsMod.recentIds["web"] = 0;
				}
			</script>
		';

			// compile document
		$markers['FUNC_MENU'] = t3lib_BEfunc::getFuncMenu(
				0,
				'SET[mode]',
				$this->MOD_SETTINGS['mode'],
				$this->MOD_MENU['mode']
			);

		$markers['HEADER1_LABEL'] = $GLOBALS['LANG']->getLL('header1Label');

		$mandatoryAsterisk = '<sup style="color: #ff0000">*</sup>';
		try {
			$SN = new tx_shscoutnetwebservice_sn();
			$filter = array(
				'order' => 'start_time desc',
			);

			$kalenders = $SN->get_kalender_by_global_id($ids);

		if ($_GET['action'] == 'modify') {
			print_r ($_REQUEST['mod_snk']);
			die('not Saved!!');

		}

		if ($_GET['action'] == "edit" || $_GET['action'] == "create") {
			$this->doc->setModuleTemplate(t3lib_extMgm::extPath('sh_scoutnet_kalender') . 'editor/template_edit.html');

			$event_id = $_GET['event_id'];
			if (!isset($event_id) || !is_numeric($event_id)) {
				$event_id = -1;
			}

			if ($event_id > -1) {
				$events = $SN->get_events_with_ids($ids,array($event_id));
				if (isset($events[0]))
					$event = $events[0];
			}

			if ($_GET['action'] == "create") {
				$event['id'] = -1;
			}


			if ($event['id'] == -1) {
				$subpartMarkers['CREATER_FIELD'] = '';
				$subpartMarkers['LAST_MODIFIED_FIELD'] = '';
			}

			if ($event['Last_Modified_By'] == ''){
				$subpartMarkers['LAST_MODIFIED_FIELD'] = '';
			}

			$markers['FORM_HEADER'] = '<form action="'.$this->MCONF['_'].'&action=modify" method="post" name="eventForm" id="eventForm" autocomplete="on">';
			$markers['HIDDEN_FIELDS'] = '<input type="hidden" name="mod_snk[event_id]" value="'.$event['ID'].'" />';

			$markers['BACK_TO_OVERVIEW_LINK'] = '<a href="'.$this->MCONF['_'].'">» '.$GLOBALS['LANG']->getLL('backToOverview').'</a>';

			$markers['CREATED_LABEL'] = $GLOBALS['LANG']->getLL('createdLabel');
			$markers['CREATED_BY_LABEL'] = $GLOBALS['LANG']->getLL('createdByLabel');
			$markers['CREATED_AT_LABEL'] = $GLOBALS['LANG']->getLL('createdAtLabel');
			$markers['CREATED_BY'] = $event['Created_By'];
			$markers['CREATED_AT'] = strftime("%d.%m.%Y %H:%M",$event['Created_At']);

			$markers['LAST_MODIFIED_LABEL'] = $GLOBALS['LANG']->getLL('lastModifiedLabel');
			$markers['LAST_MODIFIED_BY_LABEL'] = $GLOBALS['LANG']->getLL('lastModifiedByLabel');
			$markers['LAST_MODIFIED_AT_LABEL'] = $GLOBALS['LANG']->getLL('lastModifiedAtLabel');
			$markers['LAST_MODIFIED_BY'] = $event['Last_Modified_By'];
			$markers['LAST_MODIFIED_AT'] = strftime("%d.%m.%Y %H:%M",$event['Last_Modified_At']);

			$markers['TITLE_LABEL'] = $GLOBALS['LANG']->getLL('titleLabel');
			$markers['TITLE_MANDATORY'] = $mandatoryAsterisk; 
			$markers['TITLE_FIELD'] = $this->createTextInput("Title",$GLOBALS['LANG']->getLL('titleLabel'),$event['Title']); 

			$markers['START_DATE_LABEL'] = $GLOBALS['LANG']->getLL('startDateLabel');
			$markers['START_DATE_MANDATORY'] = $mandatoryAsterisk;
			$markers['START_DATE_FIELD'] = $this->createDateInput('StartDate',time(),$event['Start']);

			$markers['START_TIME_LABEL'] = $GLOBALS['LANG']->getLL('startTimeLabel');
			$markers['START_TIME_FIELD'] = $this->createTimeInput('StartTime',-1,time(),$event['Start']);

			$markers['END_DATE_LABEL'] = $GLOBALS['LANG']->getLL('endDateLabel');
			$markers['END_DATE_FIELD'] = $this->createDateInput('EndDate',-1,time(),$event['End']);

			$markers['END_TIME_LABEL'] = $GLOBALS['LANG']->getLL('endTimeLabel');
			$markers['END_TIME_FIELD'] = $this->createTimeInput('StartDate',-1,$event['End']);

			$markers['LOCATION_LABEL'] = $GLOBALS['LANG']->getLL('locationLabel');
			$markers['LOCATION_FIELD'] = $this->createTextInput("Location",$GLOBALS['LANG']->getLL('locationLabel'),$event['Location']); 
			
			$markers['ORGANIZER_LABEL'] = $GLOBALS['LANG']->getLL('organizerLabel');
			$markers['ORGANIZER_FIELD'] = $this->createTextInput("Organizer",$GLOBALS['LANG']->getLL('organizerLabel'),$event['Organizer']); 
			
			$markers['TARGET_GROUP_LABEL'] = $GLOBALS['LANG']->getLL('targetGroupLabel');
			$markers['TARGET_GROUP_FIELD'] = $this->createTextInput("TargetGroup",$GLOBALS['LANG']->getLL('targetGroupLabel'),$event['TargetGroup']); 

			$markers['ZIP_LABEL'] = $GLOBALS['LANG']->getLL('zipLabel');
			$markers['ZIP_FIELD'] = $this->createTextInput("Zip",$GLOBALS['LANG']->getLL('zipLabel'),$event['ZIP']); 

			$markers['LINK_TEXT_LABEL'] = $GLOBALS['LANG']->getLL('linkTextLabel');
			$markers['LINK_TEXT_FIELD'] = $this->createTextInput("LinkText",$GLOBALS['LANG']->getLL('linkTextLabel'),$event['URL_Text']); 

			$markers['LINK_URL_LABEL'] = $GLOBALS['LANG']->getLL('linkUrlLabel');
			$markers['LINK_URL_FIELD'] = $this->createTextInput("LinkUrl",$GLOBALS['LANG']->getLL('linkUrlLabel'),$event['URL']); 

			$markers['INFO_LABEL'] = $GLOBALS['LANG']->getLL('infoLabel');
			$markers['INFO_FIELD'] = $this->createTextArea("Info",$GLOBALS['LANG']->getLL('infoLabel'),$event['Description']); 

			$markers['SAVE_LABEL'] = $GLOBALS['LANG']->getLL('save');


			$markers['KEYWORDS_LABEL'] = $GLOBALS['LANG']->getLL('keywordsLabel');


			$kategories = $kalenders[0]['Used_Kategories'];

			if( !empty($event['Keywords']) ){
				foreach( $event['Keywords'] as $id => $keyword ) { 
					$kategories[$id] = $keyword;
				}   
				// this should only remove keywords that are set for the event, but belong to forced_keywords
				foreach($kalender[0]['Forced_Kategories'] as $forced_keywords_group) {
					foreach( $forced_keywords_group as $id => $keyword ) { 
						if(isset($kategories[$id])) {
							unset($kategories[$id]);
						}
					}
				}
			}
			// "sonstiges"-hack
			if(isset($kategories[1])){
				unset($kategories[1]);
			}


			uasort($kategories,'strcoll');

			$markers['KEYWORDS_FIELD'] = '';
			foreach ($kategories as $id=>$name) {
				$markers['KEYWORDS_FIELD'] .= '<input name="mod_snk[keywords]['.$id.']" type="checkbox" value="1" id="kw_'.$id.'" '.(array_key_exists($id,$event['Keywords'])?'checked':'').'><label for="kw_'.$id.'">'.$name.'</label><br>';
			}

			$markers['OWN_KEYWORDS_LABEL'] = $GLOBALS['LANG']->getLL('ownKeywordsLabel');
			// this ][ is correct, since the createTextInput creates an array
			$markers['OWN_KEYWORDS_FIELD'] = $this->createTextInput("customKeywords][",$GLOBALS['LANG']->getLL('ownKeywordsLabel'),'').'<br>'.
				$this->createTextInput("customKeywords][",$GLOBALS['LANG']->getLL('ownKeywordsLabel'),'');

			$markers['GROUP_OR_LEADER_LABEL'] = $GLOBALS['LANG']->getLL('groupOrLeaderLabel');

			$markers['GROUP_OR_LEADER_FIELD'] = '';
			foreach ($kalenders[0]['Forced_Kategories']['sections/leaders'] as $id=>$name) {
				$markers['GROUP_OR_LEADER_FIELD'] .= '<input name="mod_snk[keywords]['.$id.']" type="checkbox" value="1" id="kw_'.$id.'" '.(array_key_exists($id,$event['Keywords'])?'checked':'').'><label for="kw_'.$id.'">'.$name.'</label><br>';
			}

			if (isset($kalenders[0]['Forced_Kategories']['DPSG-Ausbildung'])) {
				$markers['DPSG_EDU_LABEL'] = $GLOBALS['LANG']->getLL('dpsgEduLabel');
				$markers['DPSG_EDU_FIELD'] = '';
				foreach ($kalenders[0]['Forced_Kategories']['DPSG-Ausbildung'] as $id=>$name) {
					$markers['DPSG_EDU_FIELD'] .= '<input name="mod_snk[keywords]['.$id.']" type="checkbox" value="1" id="kw_'.$id.'" '.(array_key_exists($id,$event['Keywords'])?'checked':'').'><label for="kw_'.$id.'">'.$name.'</label><br>';
				}
			} else {
				$subpartMarkers['DPSG_EDU_COLUMN'] = '';
			}


			$markers['MANDATORY_LABEL'] = '<span style="font-size:80%;">'.$mandatoryAsterisk.$GLOBALS['LANG']->getLL('mandatoryLabel').'</span>';

		} else {
			$markers['CONTENT'] = $this->content;

			$markers['EBENE_LONG_NAME'] = $kalenders[0]->get_Name();

			$markers['BEGIN_LABEL'] = $GLOBALS['LANG']->getLL('beginLabel');
			$markers['END_LABEL'] = $GLOBALS['LANG']->getLL('endLabel');
			$markers['TITLE_LABEL'] = $GLOBALS['LANG']->getLL('titleLabel');
			$markers['ACTION_LABEL'] = $GLOBALS['LANG']->getLL('actionLabel');

			$markers['CREATE_NEW_EVENT_LINK'] = '<a href="'.$this->MCONF['_'].'&action=create">» '.$GLOBALS['LANG']->getLL('create').'</a>';



			$event_template = t3lib_parsehtml::getSubpart($this->doc->moduleTemplate,'###EVENT_TEMPLATE###');
			$year_change_template = t3lib_parsehtml::getSubpart($this->doc->moduleTemplate,'###YEAR_CHANGE_TEMPLATE###');
			$last_modified_template = t3lib_parsehtml::getSubpart($event_template,'###LAST_MODIFIED###');


			$events = array();
			$events = $SN->get_events_for_global_id_with_filter($ids,$filter);

			$events_out = '';
			foreach ($events as $event) {

				if($previous_year != strftime('%Y',$event['Start'])) {
					$previous_year = strftime('%Y',$event['Start']);
					$events_out .= t3lib_parsehtml::substituteMarkerArray($year_change_template,array('YEAR'=>strftime('%Y',$event['Start'])),'###|###');

				}


				$start_date = substr(strftime("%A",$event['Start']),0,2).",&nbsp;".strftime("%d.%m.%Y",$event['Start']);
				$date = $start_date;
				$end_date = '';

				if (isset($event['End']) && strftime("%d%m%Y",$event['Start']) != strftime("%d%m%Y",$event['End']) ) {
					$date .= "&nbsp;-&nbsp;";
					$end_date = substr(strftime("%A",$event['End']),0,2).",&nbsp;".strftime("%d.%m.%Y",$event['End']);
					$date .= $end_date;
				}

				$time = '';
				$start_time = '';
				$end_time = '';
				if ($event['All_Day'] != 1) {
					$start_time = strftime("%H:%M",$event['Start']);
					$time = $start_time;


					if (isset($event['End']) && strftime("%H%M",$event['Start']) != strftime("%H%M",$event['End']) ) {
						$time .= "&nbsp;-&nbsp;";
						$end_time = strftime("%H:%M",$event['End']);
						$time .= $end_time;
					}
				}

				$date_with_time = $start_date.(($start_time != '')?',&nbsp;'.$start_time:'').(($end_date.$end_time != '')?' '.$GLOBALS['LANG']->getLL('to').' ':'').($end_date != ''?$end_date:'').(($end_date.$end_time != '')?',&nbsp;':'').($end_time != ''?$end_time:'');



				$event_markers = array(

					'TITEL' => nl2br(htmlentities($event['Title'],ENT_COMPAT,'UTF-8')),
					'DATE_WITH_TIME' => $date_with_time,

					'CREATED_LABEL' => $GLOBALS['LANG']->getLL('createdLabel'),
					'LAST_MODIFIED_LABEL' => $GLOBALS['LANG']->getLL('lastModifiedLabel'),

					'CREATED_BY' => $event['Created_By'],
					'CREATED_AT' => strftime("%d.%m.%Y %H:%M",$event['Created_At']),

					'LAST_MODIFIED_BY' => $event['Last_Modified_By'],
					'LAST_MODIFIED_AT' => strftime("%d.%m.%Y %H:%M",$event['Last_Modified_At']),

					'EDIT_LINK' => '<a href="'.$this->MCONF['_'].'&event_id='.$event['ID'].'&action=edit">» '.$GLOBALS['LANG']->getLL('edit').'</a>',
					'USE_AS_TEMPLATE_LINK' => '<a href="'.$this->MCONF['_'].'&event_id='.$event['ID'].'&action=create">» '.$GLOBALS['LANG']->getLL('useAsTemplate').'</a>',
					'DELETE_LINK' => '<a href="'.$this->MCONF['_'].'&event_id='.$event['ID'].'&action=delete">» '.$GLOBALS['LANG']->getLL('delete').'</a>',
				);


				$last_modified = isset($event['Last_Modified_By']) && $event['Last_Modified_By'] != ''?$last_modified_template:'';
				
				$events_out .= t3lib_parsehtml::substituteMarkerArray(t3lib_parsehtml::substituteSubpart($event_template,'###LAST_MODIFIED###',$last_modified),$event_markers,'###|###');
			}


			$markers['EVENTS'] = $events_out;


		}
		} catch(Exception $e) {
			die($GLOBALS['LANG']->getLL('snkDown').'<br><pre>'.$e.'</pre>');
		}

		// Build the <body> for the module
		$this->content = $this->doc->startPage($GLOBALS['LANG']->getLL('title'));
		$this->content .= $this->doc->moduleBody($this->pageinfo, $docHeaderButtons, $markers, $subpartMarkers);
		$this->content .= $this->doc->endPage();
		$this->content = $this->doc->insertStylesAndJS($this->content);


		return;
	}


	private function createTextInput($name, $defaultValue, $value = ""){
		$color = "black";
		if ($value == "") {
			$color = "lightgray";
			$value = $defaultValue;
		}

		return '<input maxlength="255" name="mod_snk['.$name.']" style="color:'.$color.'" type="text" value="'.$value.'" onfocus="if (this.value == \''.$defaultValue.'\') { this.value=\'\'; this.style.color=\'black\';}" onblur="if (this.value ==\'\') {this.style.color=\'lightgray\';this.value=\''.$defaultValue.'\'}">'; 
	}
	private function createTextArea($name, $defaultValue, $value = ""){
		$color = "black";
		if ($value == "") {
			$color = "lightgray";
			$value = $defaultValue;
		}

		return '<textarea cols="30" rows="8" name="mod_snk['.$name.']" style="color:'.$color.'" onfocus="if (this.value == \''.$defaultValue.'\') { this.value=\'\'; this.style.color=\'black\';}" onblur="if (this.value ==\'\') {this.style.color=\'lightgray\';this.value=\''.$defaultValue.'\'}">'.$value.'</textarea>'; 
	}

	private function createDateInput($name, $defaultValue, $value = 0){
		if ($value == 0) {
			$value = $defaultValue;
		}

		$out = '<input type="hidden" name="mod_snk['.$name.']" id="'.$name.'_value" value="'.$value.'">';

		$day_options = $month_options = $year_options = '';


		$noContent = false;
		if ($defaultValue == -1) { 
			$noContent = $value == $defaultValue;
			$day_options .= '<option value="" '.($noContent?'selected':'').'></option>';
			$month_options .= '<option value="" '.($noContent?'selected':'').'></option>';
			$year_options .= '<option value="" '.($noContent?'selected':'').'></option>';
		}

		for ($month = 1; $month <= 12; $month++) {
			$month_options .= '<option value="'.$month.'" '.(!$noContent && strftime("%m",$value) == $month?'selected':'').'>'.$GLOBALS['LANG']->getLL('mon'.$month).'</option>';
		}


		$days_in_feb = array();
		for ($year=strftime("%Y") - 5;$year <= strftime("%Y") + 10; $year++){
			$year_options .= '<option value="'.$year.'" '.(!$noContent && strftime("%Y",$value) == $year?'selected':'').'>'.$year.'</option>'; 

			$days_in_feb[$year] = 28;
			if (checkdate(2,29,$year)) 
				$days_in_feb[$year] = 29;

		}

		for ($day=1; $day <= 31; $day++){
			$day_options .= '<option value="'.$day.'" '.(!$noContent && strftime("%d",$value) == $day?'selected':'').'>'.$day.'</option>'; 
		}

		if (!$this->jsDateFktSet) {

			$this->jsDateFktSet = true;

			$fkt = 'days_in_feb = Array();';

			foreach ($days_in_feb as $year=>$days) {
				$fkt .= 'days_in_feb['.$year.'] = '.$days.';'."\n";
			}

			$fkt .= 'function setDaysForYearMon(year,mon,field) {
				oldValue = field.value;
				days_in_mon = 31;
				if (mon == 2) {
					days_in_mon = days_in_feb[year];
				}
				
				if (mon == 4 || mon == 6 || mon == 9 || mon == 11) {
					days_in_mon = 30;
				}

				while (days_in_mon < field.options.length) {
					field.options[(field.options.length - 1)] = null;
				}

				for (i = 1; i <= days_in_mon; i++){
					field.options[i] = new Option(i);
				}

				if (oldValue <= days_in_mon) {
					field.value = oldValue;
				}


			}';
			$this->doc->JScodeArray[] = $fkt;
		}

		$out .= '<select id="'.$name.'_day">'.$day_options.'</select>'.
			'<select id="'.$name.'_month" onchange="setDaysForYearMon(document.getElementById(\''.$name.'_year\').value,this.value,document.getElementById(\''.$name.'_day\'))">'.$month_options.'</select>'.
			'<select id="'.$name.'_year" onchange="setDaysForYearMon(this.value,document.getElementById(\''.$name.'_month\').value,document.getElementById(\''.$name.'_day\'))">'.$year_options.'</select>';

		return $out;
	}

	private function createTimeInput($name, $defaultValue, $value = ""){
		if ($value == "") {
			$value = $defaultValue;
		}

		$out = '<input type="hidden" name="mod_snk['.$name.']" id="'.$name.'_value" value="'.$value.'">';
	

		$hour_options = $min_options = '';

		$noContent = false;
		if ($defaultValue == -1) { 
			$noContent = $value == $defaultValue;
			$hour_options .= '<option value="" '.($noContent?'selected':'').'></option>';
			$min_options .= '<option value="" '.($noContent?'selected':'').'></option>';
		}

		for ($hour = 0; $hour < 24; $hour++) {
			$hour_options .= '<option value="'.$hour.'" '.(!$noContent && strftime("%H",$value) == $hour?'selected':'').'>'.$hour.'</option>';
		}

		for ($min = 0; $min < 60; $min+=5) {
			$min_options .= '<option value="'.$min.'" '.(!$noContent && strftime("%M",$value) - strftime("%M",$value)%5 == $min?'selected':'').'>'.$min.'</option>';
		}

		$out .= '<select id="'.$name.'_hour">'.$hour_options.'</select>'.
			'<select id="'.$name.'_min">'.$min_options.'</select>';

		return $out;
	}

	/**
	 * Prints out the module's HTML
	 *
	 * @return	void
	 */
	public function printContent() {
		echo $this->content;
	}

	/**
	 * Generates the module content by calling the selected task
	 *
	 * @return	void
	 */
	protected function renderModuleContent() {
		$title = $content = $actionContent = '';
		$chosenTask	= (string)$this->MOD_SETTINGS['function'];

			// render the taskcenter task as default
		if (empty($chosenTask) || $chosenTask == 'index') {
			$chosenTask = 'taskcenter.tasks';
		}

			// remder the task
		list($extKey, $taskClass) = explode('.', $chosenTask, 2);
		$title = $GLOBALS['LANG']->sL($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['taskcenter'][$extKey][$taskClass]['title']);

		if (class_exists($taskClass)) {
			$taskInstance = t3lib_div::makeInstance($taskClass, $this);

			if ($taskInstance instanceof tx_taskcenter_Task) {
					// check if the task is restricted to admins only
				if ($this->checkAccess($extKey, $taskClass)) {
					$actionContent .= $taskInstance->getTask();
				} else {
					$flashMessage = t3lib_div::makeInstance(
						't3lib_FlashMessage',
						$GLOBALS['LANG']->getLL('error-access', TRUE),
						$GLOBALS['LANG']->getLL('error_header'),
						t3lib_FlashMessage::ERROR
					);
					$actionContent .= $flashMessage->render();
				}
			} else {
					// error if the task is not an instance of tx_taskcenter_Task
				$flashMessage = t3lib_div::makeInstance(
					't3lib_FlashMessage',
					sprintf($GLOBALS['LANG']->getLL('error_no-instance', TRUE), $taskClass, 'tx_taskcenter_Task'),
					$GLOBALS['LANG']->getLL('error_header'),
					t3lib_FlashMessage::ERROR
				);
				$actionContent .= $flashMessage->render();
			}
		} else {
			$flashMessage = t3lib_div::makeInstance(
				't3lib_FlashMessage',
				$GLOBALS['LANG']->sL('LLL:EXT:taskcenter/task/locallang.xml:mlang_labels_tabdescr'),
				$GLOBALS['LANG']->sL('LLL:EXT:taskcenter/task/locallang.xml:mlang_tabs_tab'),
				t3lib_FlashMessage::INFO
			);
			$actionContent .= $flashMessage->render();
		}

		$content = '<div id="taskcenter-main">
						<div id="taskcenter-menu">' . $this->indexAction() . '</div>
						<div id="taskcenter-item" class="' . htmlspecialchars($extKey . '-' . $taskClass) . '">' .
							$actionContent . '
						</div>
					</div>';

		$this->content .= $content;
	}

	/**
	 * Generates the information content
	 *
	 * @return	void
	 */
	protected function renderInformationContent() {
		$content = $this->description (
			$GLOBALS['LANG']->getLL('mlang_tabs_tab'),
			$GLOBALS['LANG']->sL('LLL:EXT:taskcenter/task/locallang.xml:mlang_labels_tabdescr')
		);

		$content .= $GLOBALS['LANG']->getLL('taskcenter-about');

		if ($GLOBALS['BE_USER']->isAdmin()) {
			$content .= '<br /><br />' . $this->description (
				$GLOBALS['LANG']->getLL('taskcenter-adminheader'),
				$GLOBALS['LANG']->getLL('taskcenter-admin')
			);
		}

		$this->content .= $content;
	}

	/**
	 * Render the headline of a task including a title and an optional description.
	 *
	 * @param	string		$title: Title
	 * @param	string		$description: Description
	 * @return	string formatted title and description
	 */
	public function description($title, $description='') {
		if (!empty($description)) {
			$description = '<p class="description">' .	nl2br(htmlspecialchars($description)) . '</p><br />';
		}
		$content = $this->doc->section($title, $description, FALSE, TRUE);

		return $content;
	}

	/**
	 * Render a list of items as a nicely formated definition list including a
	 * link, icon, title and description.
	 * The keys of a single item are:
	 * 	- title:				Title of the item
	 * 	- link:					Link to the task
	 * 	- icon: 				Path to the icon or Icon as HTML if it begins with <img
	 * 	- description:	Description of the task, using htmlspecialchars()
	 * 	- descriptionHtml:	Description allowing HTML tags which will override the
	 * 											description
	 *
	 * @param	array		$items: List of items to be displayed in the definition list.
	 * @param	boolean		$mainMenu: Set it to TRUE to render the main menu
	 * @return	string	definition list
	 */
	public function renderListMenu($items, $mainMenu = FALSE) {
		$content = $section = '';
		$count = 0;

			// change the sorting of items to the user's one
		if ($mainMenu) {
			$this->doc->getPageRenderer()->addJsFile(t3lib_extMgm::extRelPath('taskcenter') . 'res/tasklist.js');
			$userSorting = unserialize($GLOBALS['BE_USER']->uc['taskcenter']['sorting']);
			if (is_array($userSorting)) {
				$newSorting = array();
				foreach($userSorting as $item) {
					if(isset($items[$item])) {
						$newSorting[] = $items[$item];
						unset($items[$item]);
					}
				}
				$items = $newSorting + $items;
			}
		}

		if (is_array($items) && count($items) > 0) {
			foreach($items as $item) {
				$title = htmlspecialchars($item['title']);

				$icon = $additionalClass = $collapsedStyle = '';
					// Check for custom icon
				if (!empty($item['icon'])) {
					if (strpos($item['icon'], '<img ') === FALSE) {
						$absIconPath = t3lib_div::getFileAbsFilename($item['icon']);
							// If the file indeed exists, assemble relative path to it
						if (file_exists($absIconPath)) {
							$icon = $GLOBALS['BACK_PATH'] . '../' . str_replace(PATH_site, '', $absIconPath);
							$icon = '<img src="' . $icon . '" title="' . $title . '" alt="' . $title . '" />';
						}
						if (@is_file($icon)) {
							$icon = '<img' . t3lib_iconworks::skinImg($GLOBALS['BACK_PATH'], $icon, 'width="16" height="16"') . ' title="' . $title . '" alt="' . $title . '" />';
						}
					} else {
						$icon = $item['icon'];
					}
				}


				$description = (!empty($item['descriptionHtml'])) ? $item['descriptionHtml'] : '<p>' . nl2br(htmlspecialchars($item['description'])) . '</p>';

				$id = $this->getUniqueKey($item['uid']);

					// collapsed & expanded menu items
				if ($mainMenu && isset($GLOBALS['BE_USER']->uc['taskcenter']['states'][$id]) && $GLOBALS['BE_USER']->uc['taskcenter']['states'][$id]) {
					$collapsedStyle = 'style="display:none"';
					$additionalClass = 'collapsed';
				} else {
					$additionalClass = 'expanded';
				}

					// first & last menu item
				if ($count == 0) {
					$additionalClass .= ' first-item';
				} elseif ($count + 1 === count($items)) {
					$additionalClass .= ' last-item';
				}

					// active menu item
				$active = ((string) $this->MOD_SETTINGS['function'] == $item['uid']) ? ' active-task' : '';

					// Main menu: Render additional syntax to sort tasks
				if ($mainMenu) {
					$dragIcon = '<img' . t3lib_iconWorks::skinImg($GLOBALS['BACK_PATH'], 'gfx/move.gif', 'width="16" height="16" hspace="2"') . ' title="' . $GLOBALS['LANG']->sL('LLL:EXT:lang/locallang_core.php:labels.move', 1) . '" alt="" />';
					$section = '<div class="down">&nbsp;</div>
								<div class="drag">' . $dragIcon . '</div>';
					$backgroundClass = 't3-row-header ';
				}

				$content .= '<li class="' . $additionalClass . $active . '" id="el_' .$id . '">
								' . $section . '
								<div class="image">' . $icon . '</div>
								<div class="' . $backgroundClass . 'link"><a href="' . $item['link'] . '">' . $title . '</a></div>
								<div class="content " ' . $collapsedStyle . '>' . $description . '</div>
							</li>';

				$count++;
			}

			$navigationId = ($mainMenu) ? 'id="task-list"' : '';

			$content = '<ul ' . $navigationId . ' class="task-list">' . $content . '</ul>';

		}

		return $content;
	}

	/**
	 * Shows an overview list of available reports.
	 *
	 * @return	string	list of available reports
	 */
	protected function indexAction() {
		$content = '';
		$tasks = array();
		$icon = t3lib_extMgm::extRelPath('taskcenter') . 'task/task.gif';

			// render the tasks only if there are any available
		if (is_array($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['taskcenter']) && count($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['taskcenter']) > 0) {
			foreach ($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['taskcenter'] as $extKey => $extensionReports) {
				foreach ($extensionReports as $taskClass => $task) {
					if (!$this->checkAccess($extKey, $taskClass)) {
						continue;
					}
					$link = 'mod.php?M=user_task&SET[function]=' . $extKey . '.' . $taskClass;
					$taskTitle = $GLOBALS['LANG']->sL($task['title']);
					$taskDescriptionHtml = '';

						// Check for custom icon
					if (!empty($task['icon'])) {
						$icon = t3lib_div::getFileAbsFilename($task['icon']);
					}

					if (class_exists($taskClass)) {
						$taskInstance = t3lib_div::makeInstance($taskClass, $this);
						if ($taskInstance instanceof tx_taskcenter_Task) {
							$taskDescriptionHtml = $taskInstance->getOverview();
						}
					}

						// generate an array of all tasks
					$uniqueKey = $this->getUniqueKey($extKey . '.' . $taskClass);
					$tasks[$uniqueKey] = array(
						'title'				=> $taskTitle,
						'descriptionHtml'	=> $taskDescriptionHtml,
						'description'		=> $GLOBALS['LANG']->sL($task['description']),
						'icon'				=> $icon,
						'link'				=> $link,
						'uid'				=> $extKey . '.' . $taskClass
					);
				}
			}

			$content .= $this->renderListMenu($tasks, TRUE);
		} else {
			$flashMessage = t3lib_div::makeInstance(
				't3lib_FlashMessage',
				$GLOBALS['LANG']->getLL('no-tasks', TRUE),
				'',
				t3lib_FlashMessage::INFO
			);
			$this->content .= $flashMessage->render();
		}

		return $content;
	}

	/**
	 * Create the panel of buttons for submitting the form or otherwise
	 * perform operations.
	 *
	 * @return	array	all available buttons as an assoc. array
	 */
	protected function getButtons() {
		$buttons = array(
			'csh' => t3lib_BEfunc::cshItem('_MOD_web_func', '', $GLOBALS['BACK_PATH']),
			'shortcut' => '',
			'open_new_window' => $this->openInNewWindow()
		);

			// Shortcut
		if ($GLOBALS['BE_USER']->mayMakeShortcut()) {
			$buttons['shortcut'] = $this->doc->makeShortcutIcon('', 'function', $this->MCONF['name']);
		}

		return $buttons;
	}

	/**
	 * Check the access to a task. Considered are:
	 *  - Admins are always allowed
	 *  - Tasks can be restriced to admins only
	 *  - Tasks can be blinded for Users with TsConfig taskcenter.<extensionkey>.<taskName> = 0
	 *
	 * @param	string		$extKey: Extension key
	 * @param	string		$taskClass: Name of the task
	 * @return boolean		Access to the task allowed or not
	 */
	protected function checkAccess($extKey, $taskClass) {
			// check if task is blinded with TsConfig (taskcenter.<extkey>.<taskName>
		$tsConfig = $GLOBALS['BE_USER']->getTSConfig('taskcenter.' . $extKey . '.' . $taskClass);
		if (isset($tsConfig['value']) && intval($tsConfig['value']) == 0) {
			return FALSE;
		}

		// admins are always allowed
		if ($GLOBALS['BE_USER']->isAdmin()) {
			return TRUE;
		}

			// check if task is restricted to admins
		if (intval($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['taskcenter'][$extKey][$taskClass]['admin']) == 1) {
			return FALSE;
		}

		return TRUE;
	}

	/**
	 * Returns HTML code to dislay an url in an iframe at the right side of the taskcenter
	 *
	 * @param	string		$url: url to display
	 * @param	int		$max:
	 * @return	string		code that inserts the iframe (HTML)
	 */
	public function urlInIframe($url, $max=0) {
		$this->doc->JScodeArray[] =
		'function resizeIframe(frame,max) {
			var parent = $("typo3-docbody");
			var parentHeight = $(parent).getHeight() - 0;
			var parentWidth = $(parent).getWidth() - $("taskcenter-menu").getWidth() - 50;
			$("list_frame").setStyle({height: parentHeight+"px", width: parentWidth+"px"});

		}
		// event crashes IE6 so he is excluded first
		var version = parseFloat(navigator.appVersion.split(";")[1].strip().split(" ")[1]);
		if (!(Prototype.Browser.IE && version == 6)) {
			Event.observe(window, "resize", resizeIframe, false);
		}';

		return '<iframe onload="resizeIframe(this,' . $max . ');" scrolling="auto"  width="100%" src="' . $url . '" name="list_frame" id="list_frame" frameborder="no" style="margin-top:-51px;border: none;"></iframe>';
	}

	/**
	 * Create a unique key from a string which can be used in Prototype's Sortable
	 * Therefore '_' are replaced
	 *
	 * @param	string		$string: string which is used to generate the identifier
	 * @return	string		modified string
	 */
	protected function getUniqueKey($string) {
		$search		= array('.', '_');
		$replace	= array('-', '');

		return str_replace($search, $replace, $string);
	}

	/**
	 * This method prepares the link for opening the devlog in a new window
	 *
	 * @return	string	Hyperlink with icon and appropriate JavaScript
	 */
	protected function openInNewWindow() {
		$url = t3lib_div::getIndpEnv('TYPO3_REQUEST_SCRIPT');
		$onClick = "devlogWin=window.open('" . $url . "','taskcenter','width=790,status=0,menubar=1,resizable=1,location=0,scrollbars=1,toolbar=0');return false;";
		$content = '<a href="#" onclick="' . htmlspecialchars($onClick).'">' .
					'<img' . t3lib_iconWorks::skinImg($GLOBALS['BACK_PATH'],'gfx/open_in_new_window.gif', 'width="19" height="14"') . ' title="' . $GLOBALS['LANG']->sL('LLL:EXT:lang/locallang_core.php:labels.openInNewWindow', 1) . '" class="absmiddle" alt="" />' .
					'</a>';
		return $content;
	}


}


if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/taskcenter/task/index.php']) {
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/taskcenter/task/index.php']);
}



// Make instance:
$SOBE = t3lib_div::makeInstance('SC_mod_user_scoutnet_kalender_editor_index');

// Include files?
foreach($SOBE->include_once as $INC_FILE) {
	include_once($INC_FILE);
}

$SOBE->main();
$SOBE->printContent();

?>
