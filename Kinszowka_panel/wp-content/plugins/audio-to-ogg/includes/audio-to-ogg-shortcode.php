<?php

	add_shortcode( 'audio-to-ogg', 'audio_to_ogg_view' );

	function audio_to_ogg_view($atts)
	{
		$atts = shortcode_atts( array(
			'path' => '',
		), $atts, 'audio-to-ogg' );
		
		audio_to_ogg_convert($atts['path'], audio_to_ogg_convert_on_finish);
		//return "path = {$atts['path']}";
		$html = "Konwertowanie pliku ".$atts['path'];
		return $html;
	}

?>