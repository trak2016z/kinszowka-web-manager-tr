<?php
	include_once( 'kinszowka-control-panel-config.php' );
	
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
?>