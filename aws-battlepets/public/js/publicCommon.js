/** 
	Common JS functions used by the front-end
*/

/**
	Switches the users region.
	Stores value in local storage.
	
	@param {obj} obj - Object passed in from the on click event
*/
function switchRegion(obj)
{
	var newRegion = obj.innerHTML;
	
	if (newRegion != currentRegion)
	{
		currentRegion = newRegion;
		localStorage.setItem("currentRegion", currentRegion);
		location.reload();
	}
}

/**
	Makes a call to getRegionRealmList.php to get a region appropriate
	HTML option string.
	
*/
function getRegionRealmList()
{
	var data = {
		"region": currentRegion
	};
	
	$.ajax({
		url: 'scripts/getRegionRealmList.php',
		type: 'POST',
		data: data,
		async: false,
		success:function(response){			
			realmSelectHTML = response;
		}
	});
}


/**
	Logs a bug report submitted by the user from the front-end nav bar
	
	@param {string} text - Bug report input by user
*/
function submitBugReport(text)
{
	var data = {
		"text": text,
		"region": currentRegion
	};
	
	$.ajax({
		url: 'scripts/submitBugReport.php',
		type: 'POST',
		data: data,
		async: false,
		success:function(response){			
			$('#bugReportText').val('');
			$('#rptBugModal').modal('hide');
		}
	});
}



