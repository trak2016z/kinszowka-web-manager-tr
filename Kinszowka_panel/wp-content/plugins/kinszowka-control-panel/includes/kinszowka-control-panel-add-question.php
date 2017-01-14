<?php
	include_once( 'kinszowka-control-panel-config.php');
	include_once( 'kinszowka-control-panel-utils.php');
	include_once( 'kinszowka-control-panel-audio-executor.php');
	if (isset($_POST))
	{
		$connectResult = db_connect();
		if ($connectResult!==true)
			echo $connectResult;
		
		$validationResult = Validation();
		if ($validationResult===true)
		{
			if (AddQuestion()===true)
			{
				echo "1:Pytanie zostało zapisane!";
				HandleFile();
			}
			else
				echo "0:Wystąpił nieznany błąd podczas dodawania metadanych do bazy.";
		}
		else
		{
			
			echo "0:".$validationResult;
		}
	}
	else
		echo "0:Brak wysłanych danych";
	function HandleFile()
	{
		global $db_host,$db_login, $db_password, $db_name, $db_charset;
		global $_POST, $_FILES;
		if ($_POST["typeID"]==2 || $_POST["typeID"]==3 && isset($_FILES["data"]))
		{
			$fileName = $_FILES['data']['name'];
			$tmpName  = $_FILES['data']['tmp_name'];
			$fileSize = $_FILES['data']['size'];
			$fileType = $_FILES['data']['type'];
			if(!get_magic_quotes_gpc())
				$fileName = addslashes($fileName);
			
			if ($_POST["typeID"]==2)
			{
				$fp = fopen($tmpName, 'r');
				$data = addslashes(fread($fp, filesize($tmpName)));
				$query = "UPDATE questions SET `PROCESSING`=1, `DATA`='".$data."' WHERE ID = ".$_POST["ID"];
				mysql_query($query);
				
				fclose($fp);
			}
			if ($_POST["typeID"]==3)
			{
				$ffmpegPath = $_POST['pluginDir'].'/kinszowka-control-panel/ffmpeg/';
				
				
				$executor = new  AudioToOggExecutor($_POST["ID"], $tmpName, $ffmpegPath, 'on_finish');
				$executor->AssignDBData($db_host,$db_login, $db_password, $db_name, $db_charset);
				$executor->start();
			}
			
		}
		if ($_POST["typeID"]==1)
		{
			$query = "UPDATE questions SET `PROCESSING`=1 WHERE ID = ".$_POST["ID"];
			mysql_query($query);
		}
		
	}
	function on_finish($fullNewFilePath, $id, $db_host,$db_login, $db_password, $db_name, $db_charset)
	{
		$connect = mysql_connect($db_host,$db_login, $db_password);
		if (!$connect)
			return mysql_error();
		mysql_select_db($db_name);
		mysql_set_charset ($db_charset);
		
		$fp = fopen($fullNewFilePath, 'r');
		$data = addslashes(fread($fp, filesize($fullNewFilePath)));
		fclose($fp);
		$query = "UPDATE questions SET `PROCESSING`=1, `DATA`='".$data."' WHERE ID = ".$id;
		mysql_query($query);
	}
	function AddQuestion()
	{
		global $_POST, $_FILES;

		if ($_POST["ID"]==0)
		{
			$query = "INSERT INTO questions (`ID_CAT`, `ID_DIFF`, `ID_TYPE`, `LAST_MOD`, `DATA`, `ACCEPTED`, `BLOCKED`, `AUTHOR_ID`, `PL`, `EN`, `PROCESSING`) VALUES 
			(
				".$_POST["catID"].",
				".$_POST["diffID"].",
				".$_POST["typeID"].",
				CURRENT_TIMESTAMP,
				null,
				".$_POST["accepted"].",
				".$_POST["blocked"].",
				0,
				'".$_POST["PL_Q"]."',
				'".$_POST["EN_Q"]."',
				0
			)";
			
			$result = mysql_query($query);
			if ($result==null)
				return false;
			$_POST["ID"] =  mysql_insert_id();
			$leter = 'A';
			for ($i = 0; $i<4; $i++)
			{
				$correct = 0;
				if ($i+1 == $_POST["correct"])
					$correct = 1;
				$answerQuery = "INSERT INTO questions_answer(`ID_QUESTIONS`, `CORRECT`, `PL`, `EN`) VALUES (
				".$_POST["ID"].",
				".$correct.",
				'".$_POST['PL_'.$leter]."',
				'".$_POST['EN_'.$leter]."');";
				$resultAnswer = mysql_query($answerQuery);
				if ($resultAnswer==null)
					return false;
				$leter++;
			}
			
			return true;
		}
		else
		{
			$query = "UPDATE questions SET 
				`ID_CAT`=".$_POST["catID"].", 
				`ID_DIFF`=".$_POST["diffID"].", 
				`ID_TYPE`=".$_POST["typeID"].", 
				`LAST_MOD`=CURRENT_TIMESTAMP,  
				`ACCEPTED`=".$_POST["accepted"].", 
				`BLOCKED`=".$_POST["blocked"].", 
				`PL`='".$_POST["PL_Q"]."', 
				`EN`='".$_POST["EN_Q"]."'
			WHERE ID = ".$_POST["ID"];
			
			$result = mysql_query($query);
			if ($result==null)
				return false;
			else
			{
				$answersIDsResult = mysql_query("SELECT ID FROM questions_answer where ID_QUESTIONS = ".$_POST["ID"]." ORDER BY ID;");
				for ($i = 1, $letter='A'; $i<=4; $i++, $letter++)
				{
					$queryAnswer = "UPDATE questions_answer SET 
						`CORRECT`=". ($_POST['correct'] == $i ? '1' : '0') . ",  
						`PL`='".$_POST["PL_".$letter]."', 
						`EN`='".$_POST["EN_".$letter]."'
					WHERE ID = ".mysql_fetch_array($answersIDsResult)["ID"];
					$resultAnswer = mysql_query($queryAnswer);
					if ($resultAnswer==null)
						return false;
				}
			}
			return true;
		}
		
		
	}
	function Validation()
	{
		global $_POST, $_FILES, $fileSizeMax, $questionMaxLength, $answerMaxLength;
		// walidacja danych
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
		if (is_numeric($_POST["typeID"]) && $_POST["typeID"] <= 0 || $_POST["typeID"] > 3)
			return "Wybrana kategoria pyania nie istnieje!"; 
		if (is_numeric($_POST["correct"]) && $_POST["correct"] <= 0 || $_POST["correct"] > 4)
			return "Wybrana poprawna odpowiedź jest błędna (lol)!"; 
		
		foreach($languageConfig as $languageKey => $language){
			foreach($language as $key => $value){
				if (strlen($_POST[$languageKey."_".$key])== 0)
					return "Nie dodano zawartości do treści ".$languageKey." ".$key; 
				else if (strlen($_POST[$languageKey."_".$key])> $value)
					return "Przekroczono dozwolone znaki w treści pytania o ".(strlen($_POST[$languageKey."_".$key]) - $value)." (max ". $value.")"; 
				else 
					$_POST[$languageKey."_".$key] = addslashes (htmlspecialchars($_POST[$languageKey."_".$key]));
			}
		}
		
		if ($_POST["typeID"]==2 || $_POST["typeID"]==3)
		{
			if ($_POST["ID"] == 0 && !isset($_FILES["data"]))
				return "Nie dodano pliku!";
			if (isset($_FILES["data"]) && $_FILES["data"]["size"]>$fileSizeMax)
				return "Wybrany plik jest za duży!";
		}
		
		if ($_POST["ID"]!=0)
		{
			// walidacja czy pytanie istnieje, czy dane się różnią i czy jest w trakcie procesowania
			$query = "SELECT ID, ID_CAT, ID_DIFF, ID_TYPE, ACCEPTED, BLOCKED, AUTHOR_ID, PL, EN, PROCESSING FROM questions WHERE ID = " . $_POST["ID"];
			$result = mysql_query($query);
			if ($row = mysql_fetch_array($result))
			{
				if ($row["PROCESSING"]==0)
					return "Pytanie jest ciągle w trakcie przetwarzania";
				
				// sprawdzanie różnic.
				$differencecs = false;
				
				if ($_POST["catID"] != $row["ID_CAT"])
					$differencecs = true;
				if ($_POST["diffID"] != $row["ID_DIFF"])
					$differencecs = true;
				
				if ($_POST["typeID"] != $row["ID_TYPE"])
				{
					if (($_POST["typeID"]==2 || $_POST["typeID"]==3) && !isset($_FILES["data"]))
						return "Nie dodano pliku!";
					$differencecs = true;
				}
				if ($_POST["accepted"] != $row["ACCEPTED"])
					$differencecs = true;
				if ($_POST["blocked"] != $row["BLOCKED"])
					$differencecs = true;
				if ($_POST["PL_Q"] != addslashes ($row["PL"]))
					$differencecs = true;
				if ($_POST["EN_Q"] != addslashes ($row["EN"]))
					$differencecs = true;
				if (isset($_FILES['data']) && $_FILES['data']['size'] > 0)
					$differencecs = true;
				
				$queryAnswer = "SELECT PL, EN, CORRECT FROM questions_answer WHERE ID_QUESTIONS = " . $_POST["ID"] ." ORDER BY ID";
				
				$leter = 'A';
				$index = '1';
				$resultAnswer = mysql_query($queryAnswer);
				while ($rowAnswer = mysql_fetch_array($resultAnswer))
				{
					if ($_POST["PL_".$leter] != addslashes ($rowAnswer["PL"]))
						$differencecs = true;
					if ($_POST["EN_".$leter] != addslashes ($rowAnswer["EN"]))
						$differencecs = true;
					if ($rowAnswer["CORRECT"]==1 && $index != $_POST["correct"])
						$differencecs  = true;
					$leter++;
					$index++;
				}
				
				if (!$differencecs)
					return "Pytanie niczym się nie różni!";
			}
			else
				$_POST["ID"] = 0;
		}
		return true;
	}
?>