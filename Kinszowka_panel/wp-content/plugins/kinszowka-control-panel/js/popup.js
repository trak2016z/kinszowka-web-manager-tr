
function deselect(e) {
	$('.pop').slideFadeToggle(function() {
		e.removeClass('selected');
	});
}
function fillWithData(catIndex, diff, type, accepted, blocked, correctAnswer, processing, languagesData, base64Data)
{
	
	$('#cbCats').val(catIndex);
	selectStars(diff);
	$("input[name=type_radio_group][value=" + type + "]").prop('checked', true);
	$("input[name=answer_correctnes_group][value=" + correctAnswer + "]").prop('checked', true);
	$('#cbDisabledEdit').val(blocked);
	$('#cbAcceptedEdit').val(accepted);
	onRadioClick();
	
	for(var key in languagesData)
	{
		$('#'+key+'_Q').val(languagesData[key]['Q']);
		$('#'+key+'_A').val(languagesData[key]['A']);
		$('#'+key+'_B').val(languagesData[key]['B']);
		$('#'+key+'_C').val(languagesData[key]['C']);
		$('#'+key+'_D').val(languagesData[key]['D']);
	}
	
	if (type == 1)
	{
		$('#data_label').hide();
		$('#audio_data').hide();
		$('#image_data').hide();
		
		$('#data_info').hide();
		$('#data_error').hide();
	}
	if (type == 2 && processing!=1)
	{
		$('#data_label').show();
		$('#audio_data').hide();
		$('#image_data').hide();
		
		$('#data_info').hide();
		$('#data_error').hide();
		if (processing == 0)
			$('#data_info').show();
		if (processing == -1)
			$('#data_error').show();
	}
	else if (type == 2 && base64Data!=null)
	{
		$('#data_label').show();
		$('#audio_data').hide();
		$('#image_data').show();
		$('#image_data_a').attr('href','data:image/jpeg;base64,'+base64Data);
		
		$('#data_info').hide();
		$('#data_error').hide();
	}
	if (type == 3 && processing!=1)
	{
		$('#data_label').show();
		$('#audio_data').hide();
		$('#image_data').hide();
		
		$('#data_info').hide();
		$('#data_error').hide();
		if (processing == 0)
			$('#data_info').show();
		if (processing == -1)
			$('#data_error').show();

	}
	else if (type == 3 && base64Data!=null)
	{
		$('#data_label').show();
		$('#audio_data').show();
		$('#image_data').hide();
		$('#audio').attr('src','data:audio/ogg;base64,'+base64Data);
		
		$('#data_info').hide();
		$('#data_error').hide();
	}
	
}

$(function() {

	$("[name='edit']").on('click', function() {
		if($(this).hasClass('selected')) {
			deselect($(this));               
		} else {
			$(this).addClass('selected');
			$('.pop').slideFadeToggle();
		}
		return false;
	});
	
	$('.close').on('click', function() {
		deselect($("[name='edit']"));
		return false;
	});
	
});

$.fn.slideFadeToggle = function(easing, callback) {
	return this.animate({ opacity: 'toggle', height: 'toggle' }, 'fast', easing, callback);
};

/* stars */
function selectStars(selectedIndex)
{
	var startImagePath = wnm_custom.star_path;
	var emptyStarImagePath = wnm_custom.star_empty_path;
	var stars = [$("#difficulty_1 img"),
				 $("#difficulty_2 img"),
				 $("#difficulty_3 img"),
				 $("#difficulty_4 img")];
	
	for (var i = 0; i<selectedIndex; i++)
		stars[i].attr('src',startImagePath);
	for (var i = selectedIndex; i<stars.length; i++)
		stars[i].attr('src', emptyStarImagePath);
}
function getStarsValue()
{
	var stars = [$("#difficulty_1 img"),
				 $("#difficulty_2 img"),
				 $("#difficulty_3 img"),
				 $("#difficulty_4 img")];
	var firstPath = stars[0].attr('src');
	for (var i = 1; i<stars.length; i++)
		if (stars[i].attr('src') != firstPath)
			return i;
	
	return stars.length;
}

/* upload */
function onRadioClick()
{
	
	var radio_text = $("#edit_type_val_1");
	var radio_image = $("#edit_type_val_2");
	var radio_music = $("#edit_type_val_3");
	console.log(radio_text.prop("checked"));
	if (radio_text.prop("checked"))
	{
		$("#data_image").hide();
		$("#data_audio").hide();
		$("#upload_error").hide();
	}
	if (radio_image.prop("checked"))
	{
		$("#data_image").show();
		$("#data_audio").hide();
		$("#upload_error").show();
	}
	if (radio_music.prop("checked"))
	{
		$("#data_image").hide();
		$("#data_audio").show();
		$("#upload_error").show();
	}
}
$("document").ready(function(){

	$("#fileinput_image").change(function(){
		var file = this.files[0];
		if (file.size>64*1024*1024)
			$("#upload_error").text("Plik jest za duży!");
		
	});
	$("#fileinput_audio").change( function(){
		var file = this.files[0];
		if (file.size>64*1024*1024)
			$("#upload_error").text("Plik jest za duży!");
		
	});
});
