<?php
	/*
	Plugin Name: Audio to ogg
	Description: This plugin is using FFMPEG executable to convert any audio to Ogg Vorbis format.
	Version:     1.0.0
	Author:      Marcin Iwaniec
	*/
	include_once( 'includes/audio-to-ogg-shortcode.php' );
	
	class AudioToOggExecutor extends Thread {
		
		public function __construct($fileName, $callback){
			$this->finishCallback = $callback;
			$this->fileName = $fileName;
		}
		public function run() {
			$newFileName = explode(".", $this->fileName)[0] . ".ogg";
			$fullNewFilePath = plugin_dir_path( __FILE__ )."ffmpeg\converted\\".$newFileName;
			
			$command = plugin_dir_path( __FILE__ )."ffmpeg/ffmpeg -y -i " .audio_to_ogg_to_convert_folder().$this->fileName." -acodec libvorbis ".$fullNewFilePath;
			
			$WshShell = new COM("WScript.Shell");
			$oExec = $WshShell->Run($command, 0, true);
			//shell_exec($command);
			if (is_callable($this->finishCallback)) 
				call_user_func($this->finishCallback, $fullNewFilePath);
			}
	}

	function audio_to_ogg_to_convert_folder()
	{
		return plugin_dir_path( __FILE__ )."ffmpeg/to-convert/";
	}
	
	function audio_to_ogg_convert($fileName, $finishCallback)
	{
		$executor = new  AudioToOggExecutor($fileName, $finishCallback);
		$executor->start();
		
	}
	function audio_to_ogg_convert_on_finish($fullNewFilePath)
	{
		$html = "<script>alert(\"Done!\");</script>";
		echo $html;
	}
	
	

?>