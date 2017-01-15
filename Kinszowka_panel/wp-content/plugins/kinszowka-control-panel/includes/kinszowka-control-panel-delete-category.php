<?php
	include_once( 'kinszowka-control-panel-config.php');
	include_once( 'kinszowka-control-panel-utils.php');
	
	if (isset($_POST))
	{
		$connectResult = db_connect();
		if ($connectResult!==true)
			echo $connectResult;
		
		if (DeleteCategory()===true)
			echo "1:Kategoria została usunięte!";
		else
			echo "0:Wystąpił błąd podczas <br> usuwania kategorii.";
	}
	
	function DeleteCategory()
	{
		global $_POST;
		$query = "DELETE FROM questions_cats WHERE `ID` = " . $_POST["ID"];
		$result = mysql_query($query);
		if ($result==null)
		{
			return false;
		}
		else
			return true;
	}
?>