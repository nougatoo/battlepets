
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

function activateDealsButton() {	
	// Check if the users has selected two characters and realms for each of them. If they have...activate the deals button
	 $('#findDealsButton').removeClass('disabled');
	 $('#findDealsButtonb').removeClass('disabled');
}


function findDeals() {
		
	if(!isNumCharactersValid(firstSearch))
	{
		alert("Please enter at least one character and realm");
		return;
	}	
	
	if($("#findDealsButton").hasClass("disabled") || $("#findDealsButtonb").hasClass("disabled"))
		return;
	
	// Clear out the garbage section
	$('#tableArea')[0].innerHTML = "";
	$('#realmSpyDynammic')[0].innerHTML = "";
	
	$('#sellHeader').hide();
	$('#dataFilter').hide();
	$('#realmSpy').hide();
	
	 $('#findDealsButton').addClass('disabled');
	 $('#findDealsButtonb').addClass('disabled');
	
	var showCommon = $('#commonSlider').is(':checked');
	var showGreen = $('#greenSlider').is(':checked');
	var showBlue = $('#blueSlider').is(':checked');
	var showEpic = $('#epicSlider').is(':checked');
	var showLeggo = $('#leggoSlider').is(':checked');
	
	var showSnipes = $('#snipesSlider').is(':checked');
	var incCollected = $('#collectedSlider').is(':checked');
	var maxBuyPerc = $('#selectMaxBuy').val();
	//var maxBuyPerc = 0.55;
	var stage = "";

	realms = [];
	characters = [];

	if(firstSearch == false)
		stage = "b";
		
	if($('#character1' + stage).val().replace(/\s/g, '') != "Test") {
		
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
		"region": currentRegion
	};
	
	data["purpose"] = "realmTabs";
	
	
	$.ajax({
		url: 'findDeals.php',
		type: 'POST',
		data: data,
		success:function(response){			
			$('#realmSpyDynammic')[0].innerHTML += response;
		}
	});
	
	$('#loadingBar').show();
	$('#loadingBarb').show();
	
	data["purpose"] = "tableData";
	$.ajax({
		url: 'findDeals.php',
		type: 'POST',
		data: data,
		success:function(response){
			$('#row4').hide();
			$('#loadingBar').hide();
			$('#loadingBarb').hide();			
			$('#row3').show();
			
			if(firstSearch == true) {
				$('#row4col2')[0].innerHTML = "";
				
				firstSearch = false;
			}
			
			$('#tableArea')[0].innerHTML += response;
			$('#dataFilter').show();
			$('#realmSpy').show()
			$('#sellHeader').show()
			activateDealsButton();
			
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
function addRealmClick(event)
{
	if(numRealms < 15) {
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
	TODO
*/
function addRealmClickb(event)
{
	if(numRealms < 15) {
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































