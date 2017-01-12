<?php
	include_once( 'kinszowka-control-panel-config.php');
	include_once( 'kinszowka-control-panel-utils.php');
	

	$processing = 1;
	if (isset($_POST))
	{
		
		$connectResult = db_connect();
		if ($connectResult!==true)
			echo $connectResult;
		
		$validationResult = Validation();
		if ($validationResult===true)
		{
			echo 'OK';
		}
		else
		{
			
			die($validationResult);
		}
		// walidacja po stronie serwera
		
	}
	else
		echo "Brak wysłanych danych";
	function Validation()
	{
		global $_POST, $fileSizeMax, $questionMaxLength, $answerMaxLength;
		$languageConfig = array(
			"PL" => array("Q" => $questionMaxLength, "A" => $answerMaxLength, "B" => $answerMaxLength, "C" => $answerMaxLength, "D" => $answerMaxLength),
			"EN" => array("Q" => $questionMaxLength, "A" => $answerMaxLength, "B" => $answerMaxLength, "C" => $answerMaxLength, "D" => $answerMaxLength)
		);
		
		$catsResults = mysql_query("SELECT C.ID FROM questions_cats C where C.ID = ".$_POST["catID"].";");
		if (!($catsRow = mysql_fetch_array($catsResults))) 
			return "Wybrana kategoria nie istnieje!";
		if (is_numeric($_POST["blocked"]) && $_POST["blocked"] != 0 && $_POST["blocked"] != 1)
			$_POST["blocked"] = 0;
		if (is_numeric($_POST["accepted"]) && $_POST["accepted"] != 0 && $_POST["accepted"] != 1)
			$_POST["accepted"] = 0;
		if (is_numeric($_POST["diffID"]) && $_POST["diffID"] <= 0 || $_POST["diffID"] > 4)
			return "Wybrany pozion trudności nie istnieje!"; 
		
		return true;
	}
?>