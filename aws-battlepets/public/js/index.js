
var firstSearch = true; // Is this the first search?
var numRealms = 1; // number of realms in the search. Minimum 1
var maxRealmNum = 15; // Maximum number of realms the user can search at once
var realms = []; // Array of realms the user selected
var characters = []; // The characterst that the user selected
var realmSelectHTML; // The <option>'s for the realm select menus
var currentRegion = ""; // Current region the user has selected

$(document).ready(function(){
	
	// Use local storage to store the users region
	if(localStorage.getItem("currentRegion") === null)
	{
		localStorage.setItem("currentRegion", "US"); // Default to US
		currentRegion = "US";
	}
	else
	{
		currentRegion = localStorage.getItem("currentRegion");
	}
	
	// Sets the realmSelectHTML for future use
	getRegionRealmList();
	$('#realm1').append(realmSelectHTML); 
	$('#currentRegion').html("Region: " + currentRegion);

});

/**
	Removes the disabled class for the two find deals buttons
	
*/
function activateDealsButton() {	
	 $('#findDealsButton').removeClass('disabled');
	 $('#findDealsButtonb').removeClass('disabled');
}


/**
	Finds Deals the realms and characters that the user has selcted.
	Makes calls to findDeals.php and uses the html that's sent back from the ajax calls.
	
*/
function findDeals() {
		
	// User must enter at least one character and realm combination
	if(!isNumCharactersValid(firstSearch)) {
		alert("Please enter at least one character and realm");
		return;
	}	
	
	// Prevents the user from spamming calls that would stress the db
	if($("#findDealsButton").hasClass("disabled") || $("#findDealsButtonb").hasClass("disabled"))
		return;
	
	// Clear out the garbage section
	$('#tableArea')[0].innerHTML = "";
	$('#realmSpyDynammic')[0].innerHTML = "";
	
	// Hide elements while we load the new ones
	$('#sellHeader').hide();
	$('#dataFilter').hide();
	$('#realmSpy').hide();
	
	// Disable the deals button while we load the current search
	 $('#findDealsButton').addClass('disabled');
	 $('#findDealsButtonb').addClass('disabled');
	
	var showCommon = $('#commonSlider').is(':checked'); // @depreciated
	var showGreen = $('#greenSlider').is(':checked'); // @depreciated
	var showBlue = $('#blueSlider').is(':checked'); // @depreciated
	var showEpic = $('#epicSlider').is(':checked'); // @depreciated
	var showLeggo = $('#leggoSlider').is(':checked');	 // @depreciated
	var showSnipes = $('#snipesSlider').is(':checked'); // @depreciated
	var incCollected = $('#collectedSlider').is(':checked'); // @depreciated
	var maxBuyPerc = $('#selectMaxBuy').val();
	var minSellPrice = $('#minSellPrice').val();
	var stage = ""; // Used to determine if the user is based the initial stage of the application ("b" if they are past their first search)
	var realms = [];
	var characters = [];

	if(firstSearch == false)
		stage = "b";
		
	// Escape user input and build the characters and realms arrays
	if($('#character1' + stage).val().replace(/\s/g, '').toLocaleLowerCase() != "test") {
		for(var i = 1; i <= numRealms; i++) {
			var aCharacter = $('#character' + i + stage).val().replace(/\s/g, '');
			var aRealm = $('#realm' + i + stage).val();
			
			if(aCharacter && aRealm)
			{
				characters.push(aCharacter);
				realms.push(aRealm);
			}
		}
	}
	else {
		// For faster testing...
		characters.push('Irone');
		characters.push('Valamyr');
		characters.push('Lladox');
		characters.push('Åurd');
		characters.push('Qamp');
		
		realms.push('cenarion-circle');
		realms.push('wyrmrest-accord');
		realms.push('proudmoore');
		realms.push('emerald-dream');
		realms.push('moon-guard');
	}	
	
	// Data to to be passed to findDeals.php ajax call
	var data = {
		"characters": characters,
		"realms": realms,
		"purpose": "",
		showCommon: true,
		showGreen: true,
		showBlue: true,
		showEpic: true,
		showLeggo: true,
		showSnipes: true,
		incCollected: true,
		maxBuyPerc: maxBuyPerc,
		minSellPrice: minSellPrice,
		"region": currentRegion
	};
	
	data["purpose"] = "realmTabs";	
	$.ajax({
		url: 'scripts/findDeals.php',
		type: 'POST',
		data: data,
		success:function(response){			
			$('#realmSpyDynammic')[0].innerHTML += response;
		}
	});
	
	// Show loading bar
	$('#loadingBar').show();
	$('#loadingBarb').show();
	
	data["purpose"] = "tableData";
	$.ajax({
		url: 'scripts/findDeals.php',
		type: 'POST',
		data: data,
		success:function(response){
			
			// Hide loading information and initial stage row (row4)
			$('#row4').hide();
			$('#loadingBar').hide();
			$('#loadingBarb').hide();			
			$('#row3').show();
			
			if(firstSearch == true) {
				$('#row4col2')[0].innerHTML = "";
				firstSearch = false;
			}
			
			// Search is done - start showing elements to user
			$('#tableArea')[0].innerHTML += response; // The meat of the page. All the deals for all realms
			$('#dataFilter').show();
			$('#realmSpy').show()
			$('#sellHeader').show()
			activateDealsButton();
			
			// Add a keyup function on the filter input. Filters as the user enters data
			$("#dataFilter").on("keyup", function() {
				var value = $(this).val().toLowerCase();
				$("#myTable1 tr").filter(function() {
				  $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1)
				});
			});
			
			// Glyphcon is loading wrong way..manually hide to trigger event that corrects it
			$('#optionsCollapse').collapse("hide");
			$('#legendCollapse').collapse("hide");
			$('[data-spy=affix]').each(function () { 
				$(this).data('bs.affix').checkPosition(); 
			});
				
			numRealms = characters.length;
			recreateCharSelection();
			
			// Repopulate the characters and realms 
			for(var i = 1; i <= numRealms; i++) {
				$('#character' + i + 'b').val(characters[i-1]);
				$('#realm' + i + 'b').val(realms[i-1]);
			}
		}
		
		
	});
	
}


/**
	Gets called when the user clicks the "+ Add Realm" button.
	Adds a new realms using the appropriate realm options for the the users region (realmSelectHTML) up to a max of 15.
	This function is called in the initial view where the user hasn't made their first search yet.
	TODO: Refactor this and the addRealmClickb function into 1;
	
*/
function addRealmClick()
{
	if(numRealms < maxRealmNum) {
		numRealms++;
		
		var appendElement = appendElement = "#charSelectForm";

		$(appendElement).append('<div id="realmSelectDiv' +numRealms + '" class="form-group charFormGroup" style="width:100%;">' + 
		'<input style="width:49%;margin-right: 4px;" type="text" class="form-control charInput" id="character' + numRealms + '"  placeholder="Character ' + numRealms + '">' +
		'<select style="width:49%;" class="form-control realmInput" id="realm' + numRealms + '">' +
		realmSelectHTML +
		'</select>' +
		'</div>');
	}
	else {
		alert("Maximum Number of realms");
	}
}

/**
	Gets called when the user clicks the "+ Add Realm" button.
	Adds a new realms using the appropriate realm options for the the users region (realmSelectHTML) up to a max of 15.
	This function is called AFTER the initial view where the user has already made their first search.
	TODO: Refactor this and the addRealmClick function into 1;
	
*/
function addRealmClickb()
{
	if(numRealms < maxRealmNum) {
		numRealms++;
		
		var appendElement = appendElement = "#charSelectFormb";

		$(appendElement).append('<div id="realmSelectDiv' +numRealms + 'b" class="form-group charFormGroup" style="width:100%;">' + 
		'<input style="width:49%;margin-right: 4px;" type="text" class="form-control charInput" id="character' + numRealms + 'b"  placeholder="Character ' + numRealms + '">' +
		'<select style="width:49%;" class="form-control realmInput" id="realm' + numRealms + 'b">' +
		realmSelectHTML +
		'</select>' +
		'</div>');
	}
	else {
		alert("Maximum Number of realms");
	}
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































