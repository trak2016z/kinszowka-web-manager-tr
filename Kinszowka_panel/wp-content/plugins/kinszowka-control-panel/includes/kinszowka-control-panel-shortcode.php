<?php

	add_shortcode( 'kinszowka-questions-list', 'kinszowk_control_panel_questions_view' );
	add_shortcode( 'kinszowka-categories-list', 'kinszowk_control_panel_categories_view' );

	function kinszowk_control_panel_questions_view()
	{
		
		$html = kinszowk_control_panel_get_questions();
		return $html;
	}
	function kinszowk_control_panel_categories_view()
	{
		
		$html = kinszowk_control_panel_get_categories();
		return $html;
	}

?>