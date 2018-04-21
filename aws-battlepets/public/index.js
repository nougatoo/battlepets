// TODO - mak the realms and characters arrays so that they can be dynamic in length
/*
// When the user scrolls down 20px from the top of the document, show the button
window.onscroll = function() {scrollFunction()};

function scrollFunction() {
    if (document.body.scrollTop > 500 || document.documentElement.scrollTop > 500) {
        document.getElementById("backFooter").style.display = "";
    } else {
        document.getElementById("backFooter").style.display = "none";
    }
}

*/

var realm1;
var realm2;
var realm3;
var realm4;


$(document).ready(function(){
  $("#dataFilter").on("keyup", function() {
    var value = $(this).val().toLowerCase();
    $("#myTable1 tr").filter(function() {
      $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1)
    });
	
  });
  
  // Glyphcon is loading wrong way..manually hide to trigger event that corrects it
  $('#optionsCollapse').collapse("hide");
});



function activateDealsButton() {	
	// Check if the users has selected two characters and realms for each of them. If they have...activate the deals button
	 $('#findDealsButton').removeClass('disabled');
}


function findDeals() {
	
	if($("#findDealsButton").hasClass("disabled"))
		return;
	
	// Clear out the garbage section
	$('#tableArea')[0].innerHTML = "";
	$('#realmTabs')[0].innerHTML = "";
	
	$('#dataFilter').hide();
	$('#realmTabs').hide();
	
	 $('#findDealsButton').addClass('disabled');
	
	var showCommon = $('#commonSlider').is(':checked');
	var showGreen = $('#greenSlider').is(':checked');
	var showBlue = $('#blueSlider').is(':checked');
	var showEpic = $('#epicSlider').is(':checked');
	var showLeggo = $('#leggoSlider').is(':checked');
	
	var showSnipes = $('#snipesSlider').is(':checked');
	var incCollected = $('#collectedSlider').is(':checked');
	var maxBuyPerc = $('#selectMaxBuy').val();
	
	if($('#character1').val().replace(/\s/g, '') != "Test") {
		var char1 = $('#character1').val().replace(/\s/g, '');
		var char2 = $('#character2').val().replace(/\s/g, '');
		var char3 = $('#character3').val().replace(/\s/g, '');
		var char4 = $('#character4').val().replace(/\s/g, '');
		
		realm1 = $('#realm1').val();
		realm2 = $('#realm2').val();
		realm3 = $('#realm3').val();
		realm4 = $('#realm4').val();
	}
	else {
		// For faster testing...
	
		char1 = 'Irone';
		char2 = 'valamyr';
		char3 = 'Lladox';
		char4 = 'Åurd';
		
		realm1 = 'cenarion-circle';
		realm2 = 'wyrmrest-accord';
		realm3 = 'proudmoore';
		realm4 = 'emerald-dream';	
	}
		
	var data = {
		"characters": [],
		"realms": [],
		"purpose": "",
		showCommon: showCommon,
		showGreen: showGreen,
		showBlue: showBlue,
		showEpic: showEpic,
		showLeggo: showLeggo,
		showSnipes: showSnipes,
		incCollected: incCollected,
		maxBuyPerc: maxBuyPerc
	};
	
	if(char1 && realm1) {
		data["characters"].push(char1);
		data["realms"].push(realm1);
	}
	
	if(char2 && realm2) {
		data["characters"].push(char2);	
		data["realms"].push(realm2);
	}
	
	if(char3 && realm3) {
		data["characters"].push(char3);
		data["realms"].push(realm3);
	}
	
	if(char4 && realm4) {
		data["characters"].push(char4);
		data["realms"].push(realm4);
	}
	
	data["purpose"] = "realmTabs";
	
	
	$.ajax({
		url: 'findDeals.php',
		type: 'POST',
		data: data,
		success:function(response){			
			$('#realmTabs')[0].innerHTML += response;
		}
	});
	
	$('#loadingBar').show();
	
	data["purpose"] = "tableData";
		$.ajax({
		url: 'findDeals.php',
		type: 'POST',
		data: data,
		success:function(response){
			$('#loadingBar').hide();
			$('#tableArea')[0].innerHTML += response;
			$('#dataFilter').show();
			$('#realmTabs').show();
			activateDealsButton();
			
			testFunction();
		}
	});
	
}

/**
	TODO
	
*/
function showRealmTables(mouseevent)
{
		var realmName = mouseevent.id.split("_")[1];
		var e;
		
		if(realm1 != realmName)
			document.getElementById(realm1 + "_Tables").style.display = "none";
		else
			document.getElementById(realm1 + "_Tables").style.display = "";
	
		if(realm2 != realmName)
			document.getElementById(realm2 + "_Tables").style.display = "none";
		else
			document.getElementById(realm2 + "_Tables").style.display = "";		
		
		if(realm3 != realmName)
			document.getElementById(realm3 + "_Tables").style.display = "none";
		else
			document.getElementById(realm3 + "_Tables").style.display = "";	
		
		if(realm4 != realmName)
			document.getElementById(realm4 + "_Tables").style.display = "none";
		else
			document.getElementById(realm4 + "_Tables").style.display = "";
	
}

/**
	TODO
*/
function showOptions()
{
	if($('#optionsDiv:visible').length == 0)
		$('#optionsDiv').show();
	else
		$('#optionsDiv').hide();
}


/**
	TODO
*/
function sortTable(mouseevent) {
	/*
	var table, rows, allRows,htmlRows, switching, i, x, y, shouldSwitch, startIndex, endIndex;

	table = mouseevent.parentElement.parentElement.parentElement;
	switching = true;
	htmlRows = table.getElementsByTagName("TR");
	allRows = [].slice.call(htmlRows);

	var totalIndex = [];
	totalIndex.push(0);
	// Find where the total rows are...do a different sort for each
	for(var i = 0; i <allRows.length; i++)
	{
		if(allRows[i].className == "totalRow")
			totalIndex.push(i);
	}

	for(var j = 0; j < totalIndex.length; j++)
	{	

		while (switching) {
			switching = false;
			//rows = table.getElementsByTagName("TR");

			startIndex = totalIndex[j] + 1;
				
			if((totalIndex[j]+1) > allRows.length)
				endIndex = allRows.length;
			else
				endIndex = totalIndex[j+1];
	
			for (i = startIndex; i < endIndex; i++) {
				htmlRows = table.getElementsByTagName("TR");
				allRows = [].slice.call(htmlRows);
				rows = allRows.slice(startIndex, endIndex);
				
				shouldSwitch = false;
				x = rows[i].getElementsByTagName("TD")[0];
				y = rows[i + 1].getElementsByTagName("TD")[0];

				if (x.innerHTML.toLowerCase() > y.innerHTML.toLowerCase()) {
				shouldSwitch= true;
				}
				
				if (shouldSwitch) {
					htmlRows[i].parentNode.insertBefore(htmlRows[i + 1], htmlRows[i]);
					switching = true;
				}

			}

		}
	}
	*/
}










