
var firstSearch = true;
var numRealms = 1;
var realms = [];
var characters = [];
var realmSelectHTML;
var currentRegion = "";

$(document).ready(function(){
	
	// Use local storage to store the users region
	if(localStorage.getItem("currentRegion") === null) {
		localStorage.setItem("currentRegion", "US"); // Default to US
		currentRegion = "US";
	}
	else {
		currentRegion = localStorage.getItem("currentRegion");
	}
	
	getRegionRealmList();
	$('#realm1').append(realmSelectHTML);
	$('#currentRegion').html("Region: " + currentRegion);

});

/**
	Removes the disabled class on the find realm button 
*/
function activateFindRealmButton() {	
	$('#findRealmButton').removeClass('disabled');
}


/**
	Finds the total value, per realm, of the pets owned by the 
	account for the character that the user entered.
	
*/
function findNewRealm() {
		
	// Make sure they entered one character and realm combination
	if(!isNumCharactersValid(firstSearch)) {
		alert("Please enter one character and realm");
		return;
	}	
	
	// If the button is disabled, the user has an active request already inflight
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
	
	// Build the character and realm array from what the user entered
	if($('#character1' + stage).val().replace(/\s/g, '')) {
		
		for(var i = 1; i <= numRealms; i++) {
			var aCharacter = $('#character' + i + stage).val().replace(/\s/g, '');
			var aRealm = $('#realm' + i + stage).val();
			
			if(aCharacter && aRealm) {
				characters.push(aCharacter);
				realms.push(aRealm);
			}
		}
	}
	
	// Data array for the getNewRealmData.php ajax call
	var data = {
		"characters": characters,
		"realms": realms,
		"region": currentRegion
	};

	// Show loading information
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
	Used to create the realm/character selection form after the user has already entered data and search.
	Used to recreate after a search.
	
	@param {int} currentRealmNum - The current realm number being created
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
	Checks that the user is entering at least one character and realm combination.
	
	@param {boolean} firstSearch - Is this the users first search?
*/
function isNumCharactersValid(firstSearch)
{
	var versionChar = "b";
	
	if(firstSearch)
		versionChar = "";
		
	for(var i = 1; i <= numRealms; i++) {
		var aCharacter = $('#character' + i + versionChar).val().replace(/\s/g, '');
		var aRealm = $('#realm' + i + versionChar).val();
		
		if(aCharacter && aRealm) {
			// They entered at least one combination of names + characters
			return true;
		}
	}
		
	return false;
}

/** 
	Recreates the realm/character selection so that the user doesn't have to enter again after searching.
	Also used to clean up empty entries that the user may have created.
	
*/
function recreateCharSelection()
{
	// Remove all realms - recreate from scratch
	$('#charSelectFormb').html("");
	
	for(var i = 1; i <= numRealms; i++) {
		addRealmAuto(i);
	}
	
}

































