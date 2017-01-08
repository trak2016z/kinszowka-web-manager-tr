<?php

	add_shortcode( 'kinszowk-questions-list', 'kinszowk_control_panel_questions_view' );

	function kinszowk_control_panel_questions_view()
	{
		
		$html = kinszowk_control_panel_get_questions();
		return $html;
	}

?>