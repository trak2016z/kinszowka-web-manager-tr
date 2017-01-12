
function deselect(e) {
	$('.pop').slideFadeToggle(function() {
		e.removeClass('selected');
	});
}
function showPopup(catIndex, diff, type, accepted, blocked, correctAnswer, processing, languagesData, base64Data, errorMessage) {
	if($("[name='edit']").hasClass('selected')) {
		deselect($("[name='edit']"));               
	} else {
		$("[name='edit']").addClass('selected');
		$('.pop').slideFadeToggle();
		fillWithData(catIndex, diff, type, accepted, blocked, correctAnswer, processing, languagesData, base64Data, errorMessage);
	}
}
function handleSendClick()
{
	if (validate())
	{
		var postScriptPath = wnm_custom.add_question_path;
		var catID = $('#cbCats').val();
		var blocked = $('#cbDisabledEdit').val();
		var accepted = $('#cbAcceptedEdit').val();
		var diffID = getStarsValue();
		var typeID = $("input:radio[name ='type_radio_group']:checked").val();
		var correct = $("input:radio[name ='answer_correctnes_group']:checked").val();
		var languagesConfig = {	
						'PL': { 'Q': $('#PL_Q').val(), 'A': $('#PL_A').val(), 'B': $('#PL_B').val(), 'C': $('#PL_C').val(), 'D': $('#PL_D').val() },
						'EN': { 'Q': $('#EN_Q').val(), 'A': $('#EN_A').val(), 'B': $('#EN_B').val(), 'C': $('#EN_C').val(), 'D': $('#EN_D').val() }
		}
		var data = null;
		
		if (typeID == 2)
			data = $("#fileinput_image")[0].files[0];
		else
			data = $("#fileinput_audio")[0].files[0];
		var fd = new FormData();
		fd.append('catID', catID);
		fd.append('blocked', blocked);
		fd.append('accepted', accepted);
		fd.append('diffID', diffID);//diffID
		fd.append('typeID', typeID);
		fd.append('correct', correct);
		fd.append('data', data);
		for(var key in languagesConfig)
		{
			fd.append(key+'_Q', $('#'+key+'_Q').val());
			fd.append(key+'_A', $('#'+key+'_A').val());
			fd.append(key+'_B', $('#'+key+'_B').val());
			fd.append(key+'_C', $('#'+key+'_C').val());
			fd.append(key+'_D', $('#'+key+'_D').val());
		}
		
		$.ajax({
			type: 'POST',
			url: postScriptPath,
			data: fd,
			processData: false,
			contentType: false,
			error: function(xhr, textStatus, errorThrown){
				alert(errorThrown);
			}
			})
			
			.done(function( data ) {
				log(data, false);
			})
			.fail(function( errorMessage) {
				log(errorMessage, true);
			})
			.always(function() {
				//alert( "finished" );
			});
	}
}
function validate()
{
	var error="";

	var fileMaxSize = wnm_custom.file_size_max;
	var languagesConfig = {	
						'PL': { 'Q': wnm_custom.question_max_length, 'A': wnm_custom.answer_max_length, 'B': wnm_custom.answer_max_length, 'C': wnm_custom.answer_max_length, 'D': wnm_custom.answer_max_length },
						'EN': { 'Q': wnm_custom.question_max_length, 'A': wnm_custom.answer_max_length, 'B': wnm_custom.answer_max_length, 'C': wnm_custom.answer_max_length, 'D': wnm_custom.answer_max_length }
					}
	
	
	if ($('#cbCats option:selected').length == 0)
		error = "Należy wybrać kategorię pytania!";
	if ($('#cbDisabledEdit option:selected').length == 0)
		$('#cbDisabledEdit').val(0);
	if ($('#cbAcceptedEdit option:selected').length == 0)
		$('#cbAcceptedEdit').val(0);

	if (getStarsValue()>4 || getStarsValue()<1)
		error = "Poziom trudności jest błędnie zaznaczony!";
	if ($("input:radio[name ='type_radio_group']:checked").val()==null)
		error = "Należy wybrać typ pytania!";
	if ($("input:radio[name ='answer_correctnes_group']:checked").val()==null)
		error = "Należy zaznaczyć poprawną odpowiedź!";
	
	for(var key in languagesConfig)
	{
		if ($('#'+key+'_Q').val().length==0)
			error = "Nie dodano zawartości do treści pytania "+key;
		
		if ($('#'+key+'_A').val().length==0)
			error = "Nie dodano zawartości do treści odpowiedzi A "+key;
		if ($('#'+key+'_B').val().length==0)
			error = "Nie dodano zawartości do treści odpowiedzi B "+key;
		if ($('#'+key+'_C').val().length==0)
			error = "Nie dodano zawartości do treści odpowiedzi C "+key;
		if ($('#'+key+'_D').val().length==0)
			error = "Nie dodano zawartości do treści odpowiedzi D "+key;
		
		if ($('#'+key+'_Q').val().length>languagesConfig[key]['Q'])
			error = "Przekroczono dozwolone znaki w treści pytania "+key+" o " + ($('#'+key+'_Q').val().length - languagesConfig[key]['Q']) + " (max "+languagesConfig[key]['Q']+")";
		
		if ($('#'+key+'_A').val().length>languagesConfig[key]['A'])
			error = "Przekroczono dozwolone znaki w treści odpowiedzi A "+key+" o " + ($('#'+key+'_A').val().length - languagesConfig[key]['A']) + " (max "+languagesConfig[key]['A']+")";
		if ($('#'+key+'_B').val().length>languagesConfig[key]['B'])
			error = "Przekroczono dozwolone znaki w treści odpowiedzi B "+key+" o " + ($('#'+key+'_B').val().length - languagesConfig[key]['B']) + " (max "+languagesConfig[key]['B']+")";
		if ($('#'+key+'_C').val().length>languagesConfig[key]['C'])
			error = "Przekroczono dozwolone znaki w treści odpowiedzi C "+key+" o " + ($('#'+key+'_C').val().length - languagesConfig[key]['C']) + " (max "+languagesConfig[key]['C']+")";
		if ($('#'+key+'_D').val().length>languagesConfig[key]['D'])
			error = "Przekroczono dozwolone znaki w treści odpowiedzi D "+key+" o " + ($('#'+key+'_D').val().length - languagesConfig[key]['D']) + " (max "+languagesConfig[key]['D']+")";
	}
	if ( $("input:radio[name ='type_radio_group']:checked").val()==2)
	{
		if ($("#fileinput_image")[0].files.length==0)
			error = "Nie wybrano pliku graficznego!";
		else if ($("#fileinput_image")[0].files[0].size>fileMaxSize)
			error = "Wybrany plik graficzny jest zbyt duży! Max "+Math.round(wnm_custom.file_size_max/1024)+" kb. Plik ma: "+Math.round($("#fileinput_image")[0].files[0].size/1024) + " kb";
	}
	if ( $("input:radio[name ='type_radio_group']:checked").val()==3)
	{
		if ($("#fileinput_audio")[0].files.length==0)
			error = "Nie wybrano pliku muzycznego!";
		else if ($("#fileinput_audio")[0].files[0].size>fileMaxSize)
			error = "Wybrany plik muzyczny jest zbyt duży! Max "+Math.round(wnm_custom.file_size_max/1024)+" kb. Plik ma: "+Math.round($("#fileinput_audio")[0].files[0].size/1024) + " kb. Przekonweruj plik na OGG Vorbis.";
	}
	
	if (error!="")
	{
		log(error, true);
		return false;
	}
	else
		return true;
}
function fillWithData(catIndex, diff, type, accepted, blocked, correctAnswer, processing, languagesData, base64Data, errorMessage)
{
	if (errorMessage!=null)
		log(errorMessage, true);
	else 
		log("", false);
	$('#cbCats').val(catIndex);
	selectStars(diff);
	$("input[name=type_radio_group][value=" + type + "]").prop('checked', true);
	
	$("input[name=answer_correctnes_group][value=" + correctAnswer + "]").prop('checked', true);
	console.log($("input[name=answer_correctnes_group][value=" + correctAnswer + "]"));
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
	var emptyStarImagePath = wnm_custom.star_empty_path;
	var stars = [$("#difficulty_1 img"),
				 $("#difficulty_2 img"),
				 $("#difficulty_3 img"),
				 $("#difficulty_4 img")];
	if (stars[0].attr('src') == wnm_custom.star_empty_path)
		return 0;
	var firstPath = stars[0].attr('src');
	for (var i = 1; i<stars.length; i++)
		if (stars[i].attr('src') != firstPath)
			return i;
	
	return stars.length;
}
function log(message, error)
{
	if (error)
		$('#save_error').css('color', 'red');
	else
		$('#save_error').css('color', '');
	$("#save_error").html(message);
}
/* upload */
function onRadioClick()
{
	$("#upload_error").text("");
	var fileMaxSize = wnm_custom.file_size_max;
	
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
		$("#fileinput_image").change(function(){
		if ($("#fileinput_image")[0].files.length!=0 && $("#fileinput_image")[0].files[0].size>fileMaxSize)
			$("#upload_error").text("Plik jest za duży!");
		
	});
	}
	if (radio_music.prop("checked"))
	{
		$("#data_image").hide();
		$("#data_audio").show();
		$("#upload_error").show();
		if ($("#fileinput_audio")[0].files.length!=0 && $("#fileinput_audio")[0].files[0].size>fileMaxSize)
			$("#upload_error").text("Plik jest za duży!");
	}
}
$("document").ready(function(){

	$("#fileinput_image").change(function(){
		if (this.files.length>0)
		{
			var file = this.files[0];
			if (file.size>wnm_custom.file_size_max)
				$("#upload_error").text("Plik jest za duży!");
		}
		else
			$("#upload_error").text("");
	});
	$("#fileinput_audio").change( function(){
		if (this.files.length>0)
		{
			var file = this.files[0];
			if (file.size>wnm_custom.file_size_max)
				$("#upload_error").text("Plik jest za duży!");
		}
		else
			$("#upload_error").text("");
	});
});
