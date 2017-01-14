<?php
	include_once( 'kinszowka-control-panel-config.php');
	include_once( 'kinszowka-control-panel-utils.php');
	
	if (isset($_POST))
	{
		$connectResult = db_connect();
		if ($connectResult!==true)
			echo $connectResult;
		
		if (DeleteQuestion()===true)
			echo "1:Pytanie zostało usunięte!";
		else
			echo "0:Wystąpił błąd podczas usuwania pytania.";
	}
	
	function DeleteQuestion()
	{
		global $_POST;
		$query = "DELETE FROM questions WHERE `ID` = " . $_POST["ID"];
		$result = mysql_query($query);
		if ($result==null)
		{
			return false;
		}
		else
			return true;
	}
?>