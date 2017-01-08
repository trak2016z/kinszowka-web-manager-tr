<?php
	/*
	Plugin Name: Kinszowka control panel
	Description: Plugin umożliwiający połączenie do bazy gry kinszówka i zarządzanie nią oraz pytaniami quizowymi.
	Version:     1.0.0
	Author:      Marcin Iwaniec
	*/
	include_once( 'includes/kinszowka-control-panel-shortcode.php' );
	include_once( 'includes/kinszowka-control-panel-config.php' );
	include_once( 'resources/kinszowka-control-panel-resources.php' );
	
	function kinszowk_control_panel_get_questions()
	{
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
		$owner = -1; 
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
		}
		if (isset($_GET["accepted"]) && is_numeric($_GET["accepted"])){
			$accepted = $_GET["accepted"];
			if ($accepted>1)
				$accepted = 1;
		}
		if (isset($_GET["owner"]) && is_numeric($_GET["owner"])){
			$owner = $_GET["owner"];
			if ($owner>1)
				$owner = 1;
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
		
		$where .= "1 ";
		// initial HTML build
		$html = "
		<link rel='stylesheet' type='text/css' href='".plugins_url( 'css/style.css', __FILE__ )."'/>
		<script src='".plugins_url( 'js/imgPreview.js', __FILE__ )."' ></script>
		<script src='".plugins_url( 'js/soundHandler.js', __FILE__ )."' ></script>
		<script src='".plugins_url( 'js/utils.js', __FILE__ )."' ></script>
		<script>
			// assign filters parameters
			function AssignFilterValues() {
				var parameters = getUrlParameters();
			
				setComboBoxValue('cbLanguage', parameters['lang']);
				setComboBoxValue('cbAccepted', parameters['accepted']);
				setComboBoxValue('cbBlocked', parameters['blocked']);
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
				var newQuery = buildUrlParameters(parameters);
				console.log(newQuery);
				location.href='?'+newQuery;
			}
		</script>";
		
		// === buildin filters
		$html .= "<table><tr>";
		// language
		$html .= "<td style='text-align: center; vertical-align: middle;'>Język: <select id='cbLanguage'>
						<option value='PL'>PL</option>
						<option value='EN'>EN</option>
					  </select></td>";
		// type
		$html .= "<td style='text-align: center; vertical-align: middle;'><input type='checkbox' id='type_val_1' name='type_chk_group' value='1' />".kinszowka_control_panel_get_type_img(1)." 
				  <input type='checkbox' id='type_val_2' name='type_chk_group' value='2' />".kinszowka_control_panel_get_type_img(2)."
				  <input type='checkbox' id='type_val_3' name='type_chk_group' value='3' />".kinszowka_control_panel_get_type_img(3)."</td>";
		// diff
		$html .= "<td style='vertical-align: middle;'><input type='checkbox' id 'diff_val_1' name='diff_chk_group' value='1' />".kinszowka_control_panel_get_diff_img(1)." <br />
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
		// accepted
		$html .= "<td style='text-align: center; vertical-align: middle;'>Zablokowane: <select id='cbBlocked'>
						<option value='-1'>Wszystkie</option>
						<option value='0'>Niezablokowane</option>
						<option value='1'>Zablokowane</option>
					  </select></td>";
		
		// building get url
		$newGet = $_GET;
		// final filter button
		$html .= "<td style='text-align: center; vertical-align: middle;'><input type='button' onclick='OnFilterClick()' value='Filtruj!' /></td>";
		$html .= "</tr></table>";
		$html .= "<script>AssignFilterValues();</script>";
		//$html .= "Języki: <a href='?".http_build_query($newGetPL)."'>PL</a> <a href='?".http_build_query($newGetEN)."'>EN</a>";
		
		// building table
		$html .="
		<table>
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
		
		$finalLanguage = $lang;

		$results = mysql_query("SELECT Q.ID, Q.LAST_MOD, Q.ACCEPTED, Q.BLOCKED, Q.DATA, Q.".$lang." QN, C.".$lang." CN, D.ID DID, T.ID TID FROM questions Q 
								JOIN questions_cats C ON Q.ID_CAT = C.ID 
								JOIN questions_difficulty D ON Q.ID_DIFF = D.ID 
								JOIN questions_types T ON Q.ID_TYPE = T.ID " . $where. " 
								ORDER BY Q.ID DESC LIMIT ".$from.", ".$limit);
		if ($results==null)
		{
			$results = mysql_query("SELECT Q.ID, Q.LAST_MOD, Q.ACCEPTED, Q.BLOCKED, Q.DATA, Q.".$defaultLang." QN, C.".$defaultLang." CN, D.ID DID, T.ID TID FROM questions Q 
								JOIN questions_cats C ON Q.ID_CAT = C.ID 
								JOIN questions_difficulty D ON Q.ID_DIFF = D.ID 
								JOIN questions_types T ON Q.ID_TYPE = T.ID " . $where. "
								ORDER BY Q.ID DESC LIMIT ".$from.", ".$limit);
			$finalLanguage = $defaultLang;
		}
        while($row = mysql_fetch_array($results)) {
			$isOwner = false;
			$canManipulateThis = $canManipulate || $isOwner;
            $content = $row['QN'];
			if ($row['TID'] == 3)
				$html .= "<audio id='audio_".$row['ID']."' src=data:audio/ogg;base64,".base64_encode($row['DATA'])." />";
			
			$resultsAnswer = mysql_query("SELECT A.".$finalLanguage.", A.CORRECT FROM questions_answer A
										  WHERE A.ID_QUESTIONS = ".$row['ID']);
			if ($row['ACCEPTED']==0)
				$html .= "<tr bgcolor='yellow'>";
			else if ($row['BLOCKED']==1)
				$html .= "<tr bgcolor='grey'>";
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
						<td>".$row['QN']."</td>";
			while($rowAnswer = mysql_fetch_array($resultsAnswer)) {
				$html .= "<td style='min-width: 85px'>";
				if ($rowAnswer['CORRECT'] == 1)
					$html .= "<b>";
				$html .= $rowAnswer[$finalLanguage];
				if ($rowAnswer['CORRECT'] == 1)
					$html .= "</b>";
				$html .= "</td>";
			}
			
			$html .= 	"<td style='width:70px'>";
			
			global $imageEditHTML, $imageDeleteHTML;
			if ($canManipulateThis)
			{
				$html .= $imageEditHTML . " ";
				$html .= $imageDeleteHTML;
			}
			
			
			$html .= 	"</td> </tr>";

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
	
	function db_connect()
	{
		global $db_host,$db_login, $db_password, $db_name, $db_charset;
		
		$connect = mysql_connect($db_host,$db_login, $db_password);
		if (!$connect)
			return mysql_error();
		mysql_select_db($db_name);
		mysql_set_charset ($db_charset);
		return true;
	}
	function have_role($role)
	{
		$roles = wp_get_current_user()->roles;
		for ($i = 0; $i < count($roles); $i++) 
			if ($roles[$i] == $role)
				return true;
		return false;
	}
	
	function correct_where_arr($arr)
	{
		$pieces = explode(",", $arr);
		if (count($pieces)>0)
		{
			for ($i = 0; $i<count($pieces); $i++)
				if (!is_numeric($pieces[$i]))
					return false;
		}
		else
			return false;
		
		return true;
	}



?>