function buildUrlParameters(parameters){
	var query = '';
	for(key in parameters)
		query += key+'='+parameters[key] + '&';
		query = query.substring(0, query.length - 1);
		return query;
	}
function getUrlParameters() {
	var parameters = {};
	var sPageURL = decodeURIComponent(window.location.search.substring(1)),
									  sURLVariables = sPageURL.split('&'),
									  sParameterName,
									  i;

	for (i = 0; i < sURLVariables.length; i++) {
		sParameterName = sURLVariables[i].split('=');
		parameters[sParameterName[0]] = sParameterName[1];
	}
	return parameters;
}
function handleAllCheckboxCheck(checkboxesGroupName){
	var checkboxes = document.getElementsByName(checkboxesGroupName);
		
	var allCheckboxStatus = true;
	for (var i = 0; i<checkboxes.length; i++)
	{
		if (checkboxes[i].value == -1)
		{
			allCheckboxStatus = checkboxes[i].checked;
			break;
		}
	}
	for (var i = 0; i<checkboxes.length; i++)
	{
		if (checkboxes[i].value != -1)
		{
			checkboxes[i].disabled = allCheckboxStatus;
			if (checkboxes[i].disabled)
				checkboxes[i].checked = false;
		}
	}
	
}
function setCheckboxGroupValue(checkboxesGroupName, valueArr)
{
	
	var checkboxes = document.getElementsByName(checkboxesGroupName);
	
	// stan wyjœciowy
	var allCheckboxIndex = -1;
	for (var i = 0; i<checkboxes.length; i++)
	{
		checkboxes[i].checked = false;
		if (checkboxes[i].value == -1)
			allCheckboxIndex = i;
	}
	
	for (var i = 0; i<valueArr.length; i++)
	{
		if (valueArr[i]==-1)
		{
			if (allCheckboxIndex!=-1)
				checkboxes[allCheckboxIndex].checked = true;
			else
			{
				for (var i = 0; i<checkboxes.length; i++)
					checkboxes[i].checked = true;
				
				break;
			}
		}
		else
			checkboxes[valueArr[i]-1].checked = true;
	}
		
	
}
function setComboBoxValue(id, value)
{
	var element = document.getElementById(id);
	if (element.innerHTML.indexOf('value="' + value + '"') > -1)
		element.value = value;
	else
		element.selectedIndex  = 0;
}
function getCheckboxGroupValue(checkboxesGroupName)
{
	var checkboxes = document.getElementsByName(checkboxesGroupName);
	
	var finalValue = '';
	for (var i = 0; i<checkboxes.length; i++)
	{
		if (checkboxes[i].checked)
		{
			console.log(checkboxes[i].value);
			if (checkboxes[i].value == -1)
				return '-1';
			finalValue += checkboxes[i].value + ',';
		}
	}
	if (finalValue=='')
		finalValue = -1;
	else
		finalValue = finalValue.substring(0, finalValue.length - 1);
	return finalValue;
}