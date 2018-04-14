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

function activateDealsButton() {
	
	// Check if the users has selected two characters and realms for each of them. If they have...activate the deals button
	 $('#findDealsButton').removeClass('disabled');
}


function findDeals() {
	
	// Clear out the garbage section
	$('#tableArea')[0].innerHTML = "";
	$('#buttonBar')[0].innerHTML = "";
	
	var showCommon = $('#commonSlider').is(':checked');
	var showGreen = $('#greenSlider').is(':checked');
	var showBlue = $('#blueSlider').is(':checked');
	var showEpic = $('#epicSlider').is(':checked');
	var showLeggo = $('#leggoSlider').is(':checked');
	
	if($('#character1').val() != "Test") {
		var char1 = $('#character1').val();
		var char2 = $('#character2').val();
		var char3 = $('#character3').val();
		var char4 = $('#character4').val();
		
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
		showLeggo: showLeggo
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
	
	data["purpose"] = "buttonBar";
	
	
	$.ajax({
		url: 'findDeals.php',
		type: 'POST',
		data: data,
		success:function(response){			
			$('#buttonBar')[0].innerHTML += response;
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
			$('#buttonBar').show();
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


$(document).ready(function(){
  $("#dataFilter").on("keyup", function() {
    var value = $(this).val().toLowerCase();
    $("#myTable1 tr").filter(function() {
      $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1)
    });
	
  });
});















