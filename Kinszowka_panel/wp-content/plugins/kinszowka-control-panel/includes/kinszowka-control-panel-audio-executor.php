<?php

class AudioToOggExecutor extends Thread {
		
	public function __construct($id, $fileName, $ffmpegPath, $callback){
		$this->finishCallback = $callback;
		$this->id = $id;
		$this->fileName = $fileName;
		$this->ffmpegPath = $ffmpegPath;
	}
	public function AssignDBData($db_host,$db_login, $db_password, $db_name, $db_charset)
	{
		$this->db_host = $db_host;
		$this->db_login = $db_login;
		$this->db_password = $db_password;
		$this->db_name = $db_name;
		$this->db_charset = $db_charset;
	}
	public function run() {
		$newFileName = explode(".", $this->fileName)[0] . "-converted.ogg";
		$command = $this->ffmpegPath."/ffmpeg -y -i " . $this->fileName." -acodec libvorbis ".$newFileName;
		$WshShell = new COM("WScript.Shell");
		$oExec = $WshShell->Run($command, 0, true);
		//shell_exec($command);
		if (is_callable($this->finishCallback)) 
			call_user_func($this->finishCallback, $newFileName, $this->id, $this->db_host, $this->db_login,$this->db_password,$this->db_name, $this->db_charset);
		}
}


?>