<?php
	/*
	Plugin Name: Kinszowka control panel
	Description: Plugin umożliwiający połączenie do bazy gry kinszówka i zarządzanie nią oraz pytaniami quizowymi.
	Version:     1.0.0
	Author:      Marcin Iwaniec
	*/
	include_once( 'includes/kinszowka-control-panel-shortcode.php');
	include_once( 'includes/kinszowka-control-panel-config.php');
	include_once( 'includes/kinszowka-control-panel-utils.php' );
	include_once( 'resources/kinszowka-control-panel-resources.php');

	
	function load_popup_script(){
		global $fileSizeMax, $questionMaxLength, $answerMaxLength, $categoryMaxLength;
		
		wp_register_script('popup-js',plugins_url( 'js/popup.js', __FILE__ ),array('jquery'));
		wp_register_script('imgPreview-js',plugins_url( 'js/imgPreview.js', __FILE__ ),array('jquery'));
		wp_register_script('utils-js',plugins_url( 'js/utils.js', __FILE__ ),array('jquery'));
		wp_register_script('soundHandler-js',plugins_url( 'js/soundHandler.js', __FILE__ ),array('jquery'));
		
		
		wp_enqueue_script('popup-js');
		wp_enqueue_script('imgPreview-js');
		wp_enqueue_script('utils-js');
		wp_enqueue_script('soundHandler-js');
		
		$wnm_custom = array( 
		'star_path' => plugins_url( 'resources/star-icon.png', __FILE__ ),
		'star_empty_path' => plugins_url( 'resources/star-empty-icon.png', __FILE__ ),
		'plugins_path' => WP_PLUGIN_DIR,
		'add_question_path' => plugins_url( 'includes/kinszowka-control-panel-add-question.php', __FILE__ ),
		'delete_question_path' => plugins_url( 'includes/kinszowka-control-panel-delete-question.php', __FILE__ ),
		'file_size_max' => $fileSizeMax,
		'question_max_length' => $questionMaxLength,
		'answer_max_length' => $answerMaxLength,
		'category_max_length' => $categoryMaxLength
		);
		
		wp_localize_script( 'popup-js', 'wnm_custom', $wnm_custom );
	}
	
	if (!has_action( 'wp_enqueue_scripts', 'load_popup_script' ))
		add_action( 'wp_enqueue_scripts', 'load_popup_script' );
	
	function kinszowk_control_panel_get_questions(){
		global $imageStarEmptyHTML, $fileSizeMax, $questionMaxLength, $answerMaxLength;
		
		//connecting to db
		$connectResult = db_connect();
		if ($connectResult!==true)
			return $connectResult;
		
		//roles
		
		$canList = have_role("subscriber") || have_role("author") || have_role("editor") || have_role("administrator");
		$canManipulate = have_role("author") || have_role("editor") || have_role("administrator");
		
		if (!$canList)
			return "Permission denied...";
		
		// default config
		$defaultLang = "PL";
		
		// defualt set
		$limit = 10;
		$page = 1;
		$type = "-1";
		$diff = "-1";
		$cats = "-1";
		$accepted = -1;
		$blocked = -1;
		$error = -1;
		$owner = -1; 
		$showID = -1; 
		$lang = $defaultLang;
		
		// getting get data
		if (isset($_GET["pag"]) && is_numeric($_GET["pag"]))
			$page = $_GET["pag"];
		if (isset($_GET["limit"]) && is_numeric($_GET["limit"]))
			$limit = $_GET["limit"];
		if (isset($_GET["lang"]))
			$lang = htmlspecialchars ($_GET["lang"]);
		if (isset($_GET["type"]) && correct_where_arr($_GET["type"]))
			$type = htmlspecialchars ($_GET["type"]);
		if (isset($_GET["diff"]) && correct_where_arr($_GET["diff"]))
			$diff = htmlspecialchars ($_GET["diff"]);
		if (isset($_GET["cats"]) && correct_where_arr($_GET["cats"]))
			$cats = htmlspecialchars ($_GET["cats"]);
		if (isset($_GET["blocked"]) && is_numeric($_GET["blocked"])){
			$blocked = $_GET["blocked"];
			if ($blocked>1)
				$blocked = 1;
			if ($blocked<-1)
				$blocked = -1;
		}
		if (isset($_GET["accepted"]) && is_numeric($_GET["accepted"])){
			$accepted = $_GET["accepted"];
			if ($accepted>1)
				$accepted = 1;
			if ($accepted<-1)
				$accepted = -1;
		}
		if (isset($_GET["processing"]) && is_numeric($_GET["processing"])){
			$error = $_GET["processing"];
			
			if ($error>1)
				$error = 1;
			if ($error<-1)
				$error = -1;
		}
		if (isset($_GET["owner"]) && is_numeric($_GET["owner"])){
			$owner = $_GET["owner"];
			if ($owner>1)
				$owner = 1;
			if ($owner<-1)
				$owner = -1;
		}
		if (isset($_GET["showID"]) && is_numeric($_GET["showID"])){
			$showID = $_GET["showID"];
			if ($showID<-1)
				$showID = -1;
		}
		// calculations
		$from = ($page-1)*$limit;
		
		// building where
		$where = "WHERE ";
		if ($type != -1)
			$where .= "T.ID IN(".$type.") AND ";
		if ($diff != -1)
			$where .= "D.ID IN(".$diff.") AND ";
		if ($cats != -1)
			$where .= "C.ID IN(".$cats.") AND ";
		if ($accepted != -1)
			$where .= "Q.ACCEPTED =".$accepted." AND ";
		if ($blocked != -1)
			$where .= "Q.BLOCKED =".$blocked." AND ";
		if ($error == 0)
			$where .= "Q.PROCESSING =-1 AND ";
		else if ($error == 1)
			$where .= "Q.PROCESSING IN(0,1) AND ";
		$where .= "1 ";
		// initial HTML build
		$html = "
		<link rel='stylesheet' type='text/css' href='".plugins_url( 'css/preview.css', __FILE__ )."'/>
		<link rel='stylesheet' type='text/css' href='".plugins_url( 'css/popup.css', __FILE__ )."'/>
		
		
		<script>
			// assign filters parameters
			function AssignFilterValues() {
				var parameters = getUrlParameters();
			
				setComboBoxValue('cbLanguage', parameters['lang']);
				setComboBoxValue('cbAccepted', parameters['accepted']);
				setComboBoxValue('cbBlocked', parameters['blocked']);
				setComboBoxValue('cbError', parameters['processing']);
				if (parameters['type']!= null)
					setCheckboxGroupValue('type_chk_group', parameters['type'].split(','));
				else
					setCheckboxGroupValue('type_chk_group',[-1]);
				if (parameters['diff']!=null)
					setCheckboxGroupValue('diff_chk_group', parameters['diff'].split(','));
				else
					setCheckboxGroupValue('diff_chk_group',[-1]);
			}
			
			
			function OnFilterClick(){
				var parameters = getUrlParameters();
				parameters['lang'] = $('#cbLanguage option:selected').val();
				parameters['type'] = getCheckboxGroupValue('type_chk_group');
				parameters['diff'] = getCheckboxGroupValue('diff_chk_group');
				parameters['accepted'] = $('#cbAccepted option:selected').val();
				parameters['blocked'] = $('#cbBlocked option:selected').val();
				parameters['processing'] = $('#cbError option:selected').val();
				var newQuery = buildUrlParameters(parameters);
				console.log(newQuery);
				location.href='?'+newQuery;
			}
		</script>";
		// popup html
		
		
		// gettig categories
		$finalLanguage = $lang;

		$catsResults = mysql_query("SELECT C.ID, C.".$finalLanguage." FROM questions_cats C");
		if ($catsResults==null)
		{
			$catsResults =mysql_query("SELECT C.ID, C.".$defaultLang." FROM questions_cats C");
			$finalLanguage = $defaultLang;
		}
		$catsOptions = "";
        while($catsRow = mysql_fetch_array($catsResults)) 
			$catsOptions .= "<option value='".$catsRow['ID']."'>".$catsRow[$finalLanguage]."</option>";
		
		$html .="
			<div class='popquestionconfig pop'>
				<div class='innermessage'>
					<p class = 'header'>Konfigurator pytania</p>
					<p>Meta dane:</p>
						<table><tr>
							<td>Kategoria: <br>
								<select id='cbCats'>".$catsOptions."</select></td>
							<td>
								Trudność:<br>
								<div class='difficulty'>
									<span id ='difficulty_4' onclick=\"selectStars(4);\">".$imageStarEmptyHTML."</span>
									<span id ='difficulty_3' onclick=\"selectStars(3);\">".$imageStarEmptyHTML."</span>
									<span id ='difficulty_2' onclick=\"selectStars(2);\">".$imageStarEmptyHTML."</span>
									<span id ='difficulty_1' onclick=\"selectStars(1);\">".$imageStarEmptyHTML."</span>
								</div>
							</td>
							<td>
								Typ:<br>
								<input type='radio' id='edit_type_val_1' name='type_radio_group' value='1' onchange =\"onRadioClick();\" />".kinszowka_control_panel_get_type_img(1)." 
								<input type='radio' id='edit_type_val_2' name='type_radio_group' value='2' onchange =\"onRadioClick();\"/>".kinszowka_control_panel_get_type_img(2)."
								<input type='radio' id='edit_type_val_3' name='type_radio_group' value='3' onchange =\"onRadioClick();\"/>".kinszowka_control_panel_get_type_img(3)."
							</td>
						</tr></table>
					
					<p id='question_data'>Dane pytania: </p>
					 
					<table>
						<thead>
							<tr>
								<td>Język</td>
								<td>Pytanie</td>
								<td>Odpowiedź A</td>
								<td>Odpowiedź B</td>
								<td>Odpowiedź C</td>
								<td>Odpowiedź D</td>
							</tr>
						</thead>
						<tbody>
							<tr>
								<td></td>
								<td></td>
								<td><input type='radio' id='answer_correctnes_A' name='answer_correctnes_group' value='1'  /></td>
								<td><input type='radio' id='answer_correctnes_B' name='answer_correctnes_group' value='2'  /></td>
								<td><input type='radio' id='answer_correctnes_C' name='answer_correctnes_group' value='3'  /></td>
								<td><input type='radio' id='answer_correctnes_D' name='answer_correctnes_group' value='4'  /></td>
							</tr>
							<tr>
								<td>PL</td>
								<td><textarea maxlength='".$questionMaxLength."' id='PL_Q'></textarea></td>
								<td><textarea maxlength='".$answerMaxLength."' id='PL_A'></textarea></td>
								<td><textarea maxlength='".$answerMaxLength."' id='PL_B'></textarea></td>
								<td><textarea maxlength='".$answerMaxLength."' id='PL_C'></textarea></td>
								<td><textarea maxlength='".$answerMaxLength."' id='PL_D'></textarea></td>
							</tr>
							<tr>
								<td>EN</td>
								<td><textarea maxlength='".$questionMaxLength."' id='EN_Q'></textarea></td>
								<td><textarea maxlength='".$answerMaxLength."' id='EN_A'></textarea></td>
								<td><textarea maxlength='".$answerMaxLength."' id='EN_B'></textarea></td>
								<td><textarea maxlength='".$answerMaxLength."' id='EN_C'></textarea></td>
								<td><textarea maxlength='".$answerMaxLength."' id='EN_D'></textarea></td>
							</tr>
						</tbody>
					</table>
					<div id='data_error' style='color: red'>Dane: Wystąpił błąd podczas procesowania danych. Spróbuj ponownie.</div>
					<div id='data_info'>Dane: Trwa procesowanie...</div>
					<audio id='audio'> </audio>
					<div id='audio_data' onmouseover=\"PlaySound('audio')\" onmouseout=\"StopSound('audio')\">Dane audio: ".kinszowka_control_panel_get_type_img(3)." </div>
					<div id='image_data'>Dane obrazu: <a id='image_data_a' class='preview' > ".kinszowka_control_panel_get_type_img(2)." </a></div>
					<br>
					<div id='data_image'>
						<input type='file' id='fileinput_image' accept='image/*' /> max 64kb
					</div>
					<div id='data_audio'>
						<input type='file' id='fileinput_audio' accept='audio/*' /> max 64kb
					</div>
					<span id='upload_error' class='upload_error'></span>
					<p id='question_data'>Dane konfiguracyjne: </p>
					<table><tr>
							<td>Zablokowany: <br>
								<select id='cbDisabledEdit'>
									<option value='0'>Nie</option>
									<option value='1'>Tak</option>
								</select></td>
							</td>
							<td>Zaakceptowany: <br>";
		if ($canManipulate)
			$html .= "			<select id='cbAcceptedEdit'>
									<option value='0'>Nie</option>
									<option value='1'>Tak</option>
								</select></td>";
		$html .= "			</td>
						</tr>
					</table>
					
						<div id='save_error' style='float: left;'></div>
						<div style='float: right;'><input style='margin: -10px 0px 0px 0px;' type='submit' value='Zapisz!' name='0' id='save_question' onclick='handleSendClick();'/> lub <a class='close_config' href='#'>Anuluj</a></div>
					<p style='margin: 60px 0px 0px 0px;'></p>
				</div>
			</div>
		";
		// === buildin filters
		$html .= "<table><tr>";
		// language
		$html .= "<td style='text-align: center; vertical-align: middle;'>Język: <select id='cbLanguage'>
						<option value='PL'>PL</option>
						<option value='EN'>EN</option>
					  </select></td>";
		// type
		$html .= "<td style='text-align: center; vertical-align: middle; min-width: 160px;'>
				  <input type='checkbox' id='type_val_1' name='type_chk_group' value='1' />".kinszowka_control_panel_get_type_img(1)." 
				  <input type='checkbox' id='type_val_2' name='type_chk_group' value='2' />".kinszowka_control_panel_get_type_img(2)."
				  <input type='checkbox' id='type_val_3' name='type_chk_group' value='3' />".kinszowka_control_panel_get_type_img(3)."</td>";
		// diff
		$html .= "<td style='vertical-align: middle; min-width: 100px;'>
				  <input type='checkbox' id 'diff_val_1' name='diff_chk_group' value='1' />".kinszowka_control_panel_get_diff_img(1)." <br />
				  <input type='checkbox' id='diff_val_2' name='diff_chk_group' value='2' />".kinszowka_control_panel_get_diff_img(2)."<br />
				  <input type='checkbox' id='diff_val_3' name='diff_chk_group' value='3' />".kinszowka_control_panel_get_diff_img(3)."<br />
				  <input type='checkbox' id='diff_val_4' name='diff_chk_group' value='4' />".kinszowka_control_panel_get_diff_img(4)."<br />
				  </td>";
		// accepted
		$html .= "<td style='text-align: center; vertical-align: middle;'>Zaakceptowane: <select id='cbAccepted'>
						<option value='-1'>Wszystkie</option>
						<option value='0'>Niezaakceptowane</option>
						<option value='1'>Zaakceptowane</option>
					  </select></td>";
		// blocked
		$html .= "<td style='text-align: center; vertical-align: middle;'>Zablokowane: <select id='cbBlocked'>
						<option value='-1'>Wszystkie</option>
						<option value='0'>Niezablokowane</option>
						<option value='1'>Zablokowane</option>
					  </select></td>";
		// error
		$html .= "<td style='text-align: center; vertical-align: middle;'>Błędne: <select id='cbError'>
						<option value='-1'>Wszystkie</option>
						<option value='0'>Błędne</option>
						<option value='1'>Tylko OK</option>
					  </select></td>";
		// building get url
		$newGet = $_GET;
		// final filter button
		$html .= "<td style='text-align: center; vertical-align: middle;'><input type='button' onclick='OnFilterClick()' value='Filtruj!' /></td>";
		$html .= "</tr></table>";
		$html .= "<script>AssignFilterValues();</script>";

		if ($showID>=0)
		{
			if ($showID == 0)
				$showID = "(SELECT MAX(Q1.ID) FROM questions Q1)";
			$SingleResult = mysql_query("SELECT Q.ID, Q.LAST_MOD, Q.ACCEPTED, Q.BLOCKED, Q.PROCESSING, Q.DATA, Q.PL, Q.EN, C.ID CID, C.".$finalLanguage." CN, D.ID DID, T.ID TID FROM questions Q 
								JOIN questions_cats C ON Q.ID_CAT = C.ID 
								JOIN questions_difficulty D ON Q.ID_DIFF = D.ID 
								JOIN questions_types T ON Q.ID_TYPE = T.ID WHERE Q.ID = ".$showID.";");
			if ($singleResultRow = mysql_fetch_array($SingleResult)) {
				$singleResultsAnswer = mysql_query("SELECT A.PL, A.EN, A.CORRECT FROM questions_answer A WHERE A.ID_QUESTIONS = ".$singleResultRow['ID']." ORDER BY A.ID");
				$singleAnswers = [
					"PL" => [],
					"EN" => [],
				];
				$correctAnswerIndex = -1;
				$index = 0;
				while($singleRowAnswer = mysql_fetch_array($singleResultsAnswer)) {
					$singleAnswers["PL"][$index] = addslashes(htmlspecialchars(trim(preg_replace('/\s+/', ' ', $singleRowAnswer["PL"]))));
					$singleAnswers["EN"][$index] = addslashes(htmlspecialchars(trim(preg_replace('/\s+/', ' ', $singleRowAnswer["EN"]))));
					if ($singleRowAnswer['CORRECT'] == 1)
						$correctAnswerIndex = $index+1;
					$index++;
				}
				
				$html.= trim(preg_replace('/\s+/', ' '," <div name=\"edit\"><script>showQuestionConfig(
				".$singleResultRow['ID'].", 
				".$singleResultRow['CID'].", 
				".$singleResultRow['DID'].", 
				".$singleResultRow['TID'].", 
				".$singleResultRow['ACCEPTED'].", 
				".$singleResultRow['BLOCKED'].", 
				".$correctAnswerIndex.", 
				".$singleResultRow['PROCESSING'].", 
				{	
					'PL': { 'Q': '".addslashes(htmlspecialchars(trim(preg_replace('/\s+/', ' ', $singleResultRow['PL']))))."',  'A': '".$singleAnswers["PL"][0]."', 'B': '".$singleAnswers["PL"][1]."', 'C': '".$singleAnswers["PL"][2]."', 'D': '".$singleAnswers["PL"][3]."' },
					'EN': { 'Q': '".addslashes(htmlspecialchars(trim(preg_replace('/\s+/', ' ', $singleResultRow['EN']))))."',  'A': '".$singleAnswers["EN"][0]."', 'B': '".$singleAnswers["EN"][1]."', 'C': '".$singleAnswers["EN"][2]."', 'D': '".$singleAnswers["EN"][3]."' }
				}, 
				".($singleResultRow['DATA']!=null ? ("'".base64_encode($singleResultRow['DATA'])."'") : "null")."
				); </script></div>"));
			}
		}
		// building table
		$html .=trim(preg_replace('/\s+/', ' ',"
		<input type=\"button\" onclick=\"fillWithData(0,1,1,1,0,0,1,1,
					{	
						'PL': { 'Q': 'Pytanie', 'A': 'Odpowiedź A', 'B': 'Odpowiedź B', 'C': 'Odpowiedź C', 'D': 'Odpowiedź D' },
						'EN': { 'Q': 'Question',  'A': 'Answer A', 'B': 'Answer B', 'C': 'Answer C', 'D': 'Answer D' }
					},
					null
					);\" name=\"edit\" value=\"Dodaj pytanie!\" />"));
		$html .="<table>
        <thead>
            <tr>
                <td>L.p.</td>
				<td>Kategoria</td>
				<td>Typ</td>
				<td>Trudność</td>
                <td>Treść</td>
				<td>Odpowiedź A</td>
				<td>Odpowiedź B</td>
				<td>Odpowiedź C</td>
				<td>Odpowiedź D</td>
				<td>Akcje</td>
            </tr>
        </thead>
        <tbody>";

		$lp = $from+1;
		$countResult =  mysql_query("SELECT COUNT(ID) C FROM questions");
		$totalCount = mysql_fetch_array($countResult)['C'];
		$maxPage = ceil((float)$totalCount/(float)$limit);
		


		$results = mysql_query("SELECT Q.ID, Q.LAST_MOD, Q.ACCEPTED, Q.BLOCKED, Q.PROCESSING, Q.DATA, Q.PL, Q.EN, C.ID CID, C.".$finalLanguage." CN, D.ID DID, T.ID TID FROM questions Q 
								JOIN questions_cats C ON Q.ID_CAT = C.ID 
								JOIN questions_difficulty D ON Q.ID_DIFF = D.ID 
								JOIN questions_types T ON Q.ID_TYPE = T.ID " . $where. " 
								ORDER BY Q.ID DESC LIMIT ".$from.", ".$limit);

        while($row = mysql_fetch_array($results)) {
			$isOwner = false;
			$canManipulateThis = $canManipulate || $isOwner;
			$content = $row[$finalLanguage];

			if ($row['TID'] == 3)
				$html .= "<audio id=\"audio_".$row['ID']."\" src=\"data:audio/ogg;base64,".base64_encode($row['DATA'])."\"></audio>";
			
			$resultsAnswer = mysql_query("SELECT A.PL, A.EN, A.CORRECT FROM questions_answer A
										  WHERE A.ID_QUESTIONS = ".$row['ID']);
			if ($row['ACCEPTED']==0)
				$html .= "<tr bgcolor='yellow'>";
			else if ($row['BLOCKED']==1)
				$html .= "<tr bgcolor='grey'>";
			else if ($row['PROCESSING']==-1)
				$html .= "<tr bgcolor='red'>";
			else
				$html .= "<tr>";
			$html .= "	<td>".$lp."</td>
						<td>".$row['CN']."</td>
						<td>";
						if ($row['TID'] == 3)
							$html .= "<p onmouseover=\"PlaySound('audio_".$row['ID']."')\" onmouseout=\"StopSound('audio_".$row['ID']."')\">";
						else if ($row['TID'] == 2)
							$html .= "<a href=data:image/jpeg;base64,".base64_encode($row['DATA'])." class='preview' >";
						
						$html .= kinszowka_control_panel_get_type_img($row['TID']);
						if ($row['TID'] == 3)
							$html .= "</p>";
						else if ($row['TID'] == 2)
							$html .= "</a>";
						
			$html .=    "</td>
						<td style='min-width: 80px'>".kinszowka_control_panel_get_diff_img($row['DID'])."</td>
						<td>".$content."</td>";
			$correctAnswerIndex = -1;
			$index = 0;
			$answers = [
				"PL" => [],
				"EN" => [],
				];
			while($rowAnswer = mysql_fetch_array($resultsAnswer)) {
				$html .= "<td style='min-width: 85px'>";
				$answers["PL"][$index] = addslashes(htmlspecialchars(trim(preg_replace('/\s+/', ' ', $rowAnswer["PL"]))));
				$answers["EN"][$index] = addslashes(htmlspecialchars(trim(preg_replace('/\s+/', ' ', $rowAnswer["EN"]))));
				if ($rowAnswer['CORRECT'] == 1)
				{
					$html .= "<b>";
					$correctAnswerIndex = $index+1;
				}
				$html .= $rowAnswer[$finalLanguage];
				if ($rowAnswer['CORRECT'] == 1)
					$html .= "</b>";
				$html .= "</td>";
				$index++;
			}
			
			$html .= "<td style=\"width:70px\">";
			
			global $imageEditHTML, $imageDeleteHTML;
	
			if ($canManipulateThis)
			{
				$html .= trim(preg_replace('/\s+/', ' ',"<a href=\"#\" onclick=\"fillWithData(
					".$row['ID'].", 
					".$row['CID'].", 
					".$row['DID'].", 
					".$row['TID'].", 
					".$row['ACCEPTED'].", 
					".$row['BLOCKED'].", 
					".$correctAnswerIndex.", 
					".$row['PROCESSING'].", 
					{	
						'PL': { 'Q': '".addslashes(htmlspecialchars(trim(preg_replace('/\s+/', ' ', $row['PL']))))."',  'A': '".$answers["PL"][0]."', 'B': '".$answers["PL"][1]."', 'C': '".$answers["PL"][2]."', 'D': '".$answers["PL"][3]."' },
						'EN': { 'Q': '".addslashes(htmlspecialchars(trim(preg_replace('/\s+/', ' ', $row['EN']))))."',  'A': '".$answers["EN"][0]."', 'B': '".$answers["EN"][1]."', 'C': '".$answers["EN"][2]."', 'D': '".$answers["EN"][3]."' }
					}, 
					".($row['DATA']!=null ? ("'".base64_encode($row['DATA'])."'") : "null")."
					);\" name=\"edit\">" . $imageEditHTML . "</a> "));
				$html .= "<a href=\"#\" onclick=\" handleDeleteClick(".$row['ID'].");\">" . $imageDeleteHTML . "</a> ";
			}
			
			
			$html .= "</td> </tr>";

			$lp++;
		}
        $html .= "</tbody>
				  </table><div name='page-setter' align='center'>";
		if ($page>1)
		{
			$newGetBegine = $_GET; $newGetBegine['pag'] = 1;
			$newGetBack = $_GET; $newGetBack['pag'] = $page-1;
			$html .= "<a href='?".http_build_query($newGetBegine)."'>≤</a> <a href='?".http_build_query($newGetBack)."'><</a> ";
		}
		$html .= $page;
		if ($page<$maxPage)
		{
			$newGetLast = $_GET; $newGetLast['pag'] = $maxPage;
			$newGetNext = $_GET; $newGetNext['pag'] = $page+1;
			$html .= " <a href='?".http_build_query($newGetNext)."'>></a> <a href='?".http_build_query($newGetLast)."'>≥</a>";
		}
		$html .= "</div>";
		return $html;
	}

	function kinszowk_control_panel_get_categories(){
		global $imageEditHTML, $categoryMaxLength;
		//connecting to db
		$connectResult = db_connect();
		if ($connectResult!==true)
			return $connectResult;
		
		//roles
		
		$canList = have_role("subscriber") || have_role("author") || have_role("editor") || have_role("administrator");
		$canManipulate = have_role("author") || have_role("editor") || have_role("administrator");
		
		if (!$canList)
			return "Permission denied...";
		
		$limit = 10;
		$page = 1;
		
		if (isset($_GET["pag"]) && is_numeric($_GET["pag"]))
			$page = $_GET["pag"];
		if (isset($_GET["limit"]) && is_numeric($_GET["limit"]))
			$limit = $_GET["limit"];
		
		// calculations
		$from = ($page-1)*$limit;
		// category configurator
		
		$html .=trim(preg_replace('/\s+/', ' ',"
		<input type=\"button\" onclick=\"fillCategoryWithData(0,'Kategoria', 'Category');\" name=\"edit\" value=\"Dodaj karegorię!\" />"));
		$html .="
			<link rel='stylesheet' type='text/css' href='".plugins_url( 'css/popup.css', __FILE__ )."'/>
			<div class='popcategoryconfig pop'>
				<div class='innermessage'>
					<p class = 'header'>Konfigurator kategorii</p>
					<p>Dane:</p>
					<div>
						PL: <input type='text' id='PL' size='".$categoryMaxLength."'> EN: <input type='text' id='EN' size='".$categoryMaxLength."'>
					</div>
					<p></p>
					<div id='save_error' style='float: left;'></div>
					<div style='float: right;'><input style='margin: -10px 0px 0px 0px;' type='submit' value='Zapisz!' name='0' id='save_category' onclick='handleSendClick();'/> lub <a class='close_config' href='#'>Anuluj</a></div>
					<p style='margin: 60px 0px 0px 0px;'></p>
				</div>
			</div>";
		$html .="<table>
        <thead>
            <tr>
                <td>L.p.</td>
				<td>PL</td>
				<td>EN</td>
				<td>Liczba pytań</td>
                <td>Średnia trudność</td>
				<td>Akcje</td>
            </tr>
        </thead>
        <tbody>";

		$lp = $from+1;
		$countResult =  mysql_query("SELECT COUNT(ID) C FROM questions");
		$totalCount = mysql_fetch_array($countResult)['C'];
		$maxPage = ceil((float)$totalCount/(float)$limit);
		


		$results = mysql_query("SELECT C.*, COUNT(Q.ID) COUNT , AVG(Q.ID_DIFF) AVG FROM `questions_cats` C 
								JOIN questions Q ON Q.ID_CAT = C.ID
								GROUP BY C.ID
								ORDER BY C.ID DESC LIMIT ".$from.", ".$limit);

        while($row = mysql_fetch_array($results)) {
			$canManipulateThis = $canManipulate;
			
			$html .= "<tr>";
			$html .= "<td>".$lp."</td>
					  <td>".$row['PL']."</td>
					  <td>".$row['EN']."</td>
					  <td>".$row['COUNT']."</td>
					  <td>".$row['AVG']."</td>
					  <td>";
					  if ($canManipulateThis)
					  {
						$html .= trim(preg_replace('/\s+/', ' ',"<a href=\"#\" onclick=\"fillCategoryWithData(
							".$row['ID'].", 
							".$row['PL'].", 
							".$row['EN']."
						);\" name=\"edit\">" . $imageEditHTML . "</a> "));
					  }
			$html .= "</td> </tr>";

			$lp++;
		}
        $html .= "</tbody>
				  </table><div name='page-setter' align='center'>";
		if ($page>1)
		{
			$newGetBegine = $_GET; $newGetBegine['pag'] = 1;
			$newGetBack = $_GET; $newGetBack['pag'] = $page-1;
			$html .= "<a href='?".http_build_query($newGetBegine)."'>≤</a> <a href='?".http_build_query($newGetBack)."'><</a> ";
		}
		$html .= $page;
		if ($page<$maxPage)
		{
			$newGetLast = $_GET; $newGetLast['pag'] = $maxPage;
			$newGetNext = $_GET; $newGetNext['pag'] = $page+1;
			$html .= " <a href='?".http_build_query($newGetNext)."'>></a> <a href='?".http_build_query($newGetLast)."'>≥</a>";
		}
		$html .= "</div>";
		return $html;
	}
	
?>