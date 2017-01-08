<?php

	$imageIconHTML  = "<img src='".plugins_url( 'image-icon.png', __FILE__ )."'>";
	$imageMusciHTML = "<img src='".plugins_url( 'music-icon.png', __FILE__ )."'>";
	$imageTextHTML  = "<img src='".plugins_url( 'text-icon.png', __FILE__ )."'>";
	$imageStarHTML  = "<img src='".plugins_url( 'star-icon.png', __FILE__ )."'>";
	$imageStarEmptyHTML  = "<img src='".plugins_url( 'star-empty-icon.png', __FILE__ )."'>";
	$imageEditHTML  = "<img src='".plugins_url( 'edit-icon.png', __FILE__ )."'>";
	$imageDeleteHTML  = "<img src='".plugins_url( 'delete-icon.png', __FILE__ )."'>";
	$imageDisableOnHTML  = "<img src='".plugins_url( 'disable-on-icon.png', __FILE__ )."'>";
	$imageDisableOffHTML  = "<img src='".plugins_url( 'disable-off-icon.png', __FILE__ )."'>";

	function kinszowka_control_panel_get_type_img($typeID)
	{
		global $imageIconHTML, $imageMusciHTML, $imageTextHTML;
		if ($typeID==1)
			return $imageTextHTML;
		if ($typeID==2)
			return $imageIconHTML;
		if ($typeID==3)
			return $imageMusciHTML;
	}
	function kinszowka_control_panel_get_diff_img($difdID)
	{
		global $imageStarHTML;
		if ($difdID==1)
			return $imageStarHTML;
		if ($difdID==2)
			return $imageStarHTML.$imageStarHTML;
		if ($difdID==3)
			return $imageStarHTML.$imageStarHTML.$imageStarHTML;
		if ($difdID==4)
			return $imageStarHTML.$imageStarHTML.$imageStarHTML.$imageStarHTML;
	}
?>