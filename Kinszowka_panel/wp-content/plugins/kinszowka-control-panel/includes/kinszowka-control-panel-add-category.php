<?php
	include_once( 'kinszowka-control-panel-config.php');
	include_once( 'kinszowka-control-panel-utils.php');
	if (isset($_POST))
	{
		$connectResult = db_connect();
		if ($connectResult!==true)
			echo $connectResult;
		
		$validationResult = Validation();
		if ($validationResult===true)
		{
			if (AddCategory()===true)
				echo "1:Kategoria została zapisane!";
			else
				echo "0:Wystąpił nieznany błąd podczas <br> dodawania kategorii do bazy.";
		}
		else
		{
			
			echo "0:".$validationResult;
		}
	}
	else
		echo "0:Brak wysłanych danych";
	
	function AddCategory()
	{
		global $_POST, $_FILES;

		if ($_POST["ID"]==0)
		{
			$query = "INSERT INTO questions_cats (`PL`, `EN`) VALUES 
			(
				'".$_POST["PL"]."',
				'".$_POST["EN"]."'
			)";
			
			$result = mysql_query($query);
			if ($result==null)
				return false;
			
			return true;
		}
		else
		{
			$query = "UPDATE questions_cats SET  
				`PL`='".$_POST["PL"]."', 
				`EN`='".$_POST["EN"]."'
			WHERE ID = ".$_POST["ID"];
			
			$result = mysql_query($query);
			if ($result==null)
				return false;
			return true;
		}
	}
	function Validation()
	{
		global $_POST, $_FILES, $categoryMaxLength;
		// walidacja danych
		
		if (strlen($_POST["PL"])==0)
			return "Nie podano nazwy kategorii dla PL.";
		if (strlen($_POST["EN"])==0)
			return "Nie podano nazwy kategorii dla EN.";
		
		if (strlen($_POST["PL"])>$categoryMaxLength)
			return "Nazwa kategorii dla PL została <br> przekroczona o " . (strlen($_POST["PL"]) - $categoryMaxLength) . " (max " . $categoryMaxLength . ")";
		if (strlen($_POST["EN"])>$categoryMaxLength)
			return "Nazwa kategorii dla EN została <br> przekroczona o " . (strlen($_POST["EN"]) - $categoryMaxLength) . " (max " . $categoryMaxLength . ")";
		
		if ($_POST["ID"]!=0)
		{
			// walidacja czy pytanie istnieje, czy dane się różnią i czy jest w trakcie procesowania
			$query = "SELECT * FROM `questions_cats` WHERE ID = " . $_POST["ID"];
			$result = mysql_query($query);
			if ($row = mysql_fetch_array($result))
			{
				// sprawdzanie różnic.
				$differencecs = false;
				
				if ($_POST["PL"] != addslashes ($row["PL"]))
					$differencecs = true;
				if ($_POST["EN"] != addslashes ($row["EN"]))
					$differencecs = true;
				
				if (!$differencecs)
					return "Pytanie niczym się nie różni!";
			}
			else
				$_POST["ID"] = 0;
		}
		return true;
	}
?>