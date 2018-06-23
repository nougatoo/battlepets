
var firstSearch = true;
var numRealms = 1;
var realms = [];
var characters = [];
var realmSelectHTML;
var currentRegion = "";

$(document).ready(function(){
	
	if(localStorage.getItem("currentRegion") === null)
	{
		localStorage.setItem("currentRegion", "US"); // Default to US
		currentRegion = "US";
	}
	else
	{
		currentRegion = localStorage.getItem("currentRegion");
	}
	
	getRegionRealmList();
	$('#realm1').append(realmSelectHTML);
	$('#currentRegion').html("Region: " + currentRegion);

});

function activateFindRealmButton() {	
	// Check if the users has selected two characters and realms for each of them. If they have...activate the deals button
	 $('#findRealmButton').removeClass('disabled');
}

function findNewRealm() {
		
	if(!isNumCharactersValid(firstSearch))
	{
		alert("Please enter one character and realm");
		return;
	}	
	
	if($("#findRealmButton").hasClass("disabled"))
		return;
	
	// Clear out the garbage section
	$('#realmValueTable')[0].innerHTML = "";
	$('#dataFilter').hide();
	$('#realmSpy').hide();
	 $('#findRealmButton').addClass('disabled');
	
	var stage = "";
	
	realms = [];
	characters = [];

	if(firstSearch == false)
		stage = "b";
		
	if($('#character1' + stage).val().replace(/\s/g, '')) {
		
		for(var i = 1; i <= numRealms; i++)
		{
			var aCharacter = $('#character' + i + stage).val().replace(/\s/g, '');
			var aRealm = $('#realm' + i + stage).val();
			
			if(aCharacter && aRealm)
			{
				characters.push(aCharacter);
				realms.push(aRealm);
			}
		}
	}
	
	var data = {
		"characters": characters,
		"realms": realms,
		"region": currentRegion
	};

	$('#loadingBar').show();
	$('#loadingBarb').show();
	
	$.ajax({
		url: 'getNewRealmData.php',
		type: 'POST',
		data: data,
		success:function(response){
			
			// Hide old and loading elements
			$('#row4').hide();
			$('#loadingBar').hide();
			$('#loadingBarb').hide();			
			$('#row3').show();
			
			if(firstSearch == true) {
				$('#row4col2')[0].innerHTML = "";		
				firstSearch = false;
			}
			
			// Add the response value and then show the appropriate elements
			$('#realmValueTable')[0].innerHTML += response;
			$('#dataFilter').show();
			$('#realmSpy').show();
			activateFindRealmButton();
			
			// Add a keyup function for the filter
			$("#dataFilter").on("keyup", function() {
				var value = $(this).val().toLowerCase();
				$("#myTable1 tr").filter(function() {
				  $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1)
				});
			});
			
			// Glyphcon is loading wrong way..manually hide to trigger event that corrects it
			$('#optionsCollapse').collapse("hide");
			$('[data-spy=affix]').each(function () { 
				$(this).data('bs.affix').checkPosition(); 
			});
			
			numRealms = characters.length;
			recreateCharSelection();
			
			// Repopulate the characters and realms 
			for(var i = 1; i <= numRealms; i++)
			{
				$('#character' + i + 'b').val(characters[i-1]);
				$('#realm' + i + 'b').val(realms[i-1]);
			}
		}	
	});
	
}

/**
	TODO
*/
function addRealmAuto(currentRealmNum)
{
	var appendElement = appendElement = "#charSelectFormb";

	$(appendElement).append('<div id="realmSelectDiv' + currentRealmNum + 'b" class="form-group charFormGroup" style="width:100%;">' + 
	'<input style="width:49%;margin-right: 4px;" type="text" class="form-control charInput" id="character' + currentRealmNum + 'b"  placeholder="Character">' +
	'<select style="width:49%;" class="form-control realmInput" id="realm' + currentRealmNum + 'b">' +
	realmSelectHTML +
	'</select>' +
	'</div>');

}


/**
	TODO
*/
function isNumCharactersValid(firstSearch)
{
	var versionChar = "b";
	
	if(firstSearch)
		versionChar = "";
		
	for(var i = 1; i <= numRealms; i++)
	{
		var aCharacter = $('#character' + i + versionChar).val().replace(/\s/g, '');
		var aRealm = $('#realm' + i + versionChar).val();
		
		if(aCharacter && aRealm)
		{
			// They entered at least one combination of names + characters
			return true;
		}
	}
		
	return false;
}



/** 
	TODO
*/

function recreateCharSelection()
{
	// Remove all realms - recreate from scratch
	$('#charSelectFormb').html("");
	
	for(var i = 1; i <= numRealms; i++)
	{
		addRealmAuto(i);
	}
	
}



/**
	TODO
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
	TODO
*/
function getRegionRealmList()
{
		var data = {
			"region": currentRegion
		};
		
		$.ajax({
			url: 'getRegionRealmList.php',
			type: 'POST',
			data: data,
			async: false,
			success:function(response){			
				realmSelectHTML = response;
			}
	});
}
































