

function activateDealsButton() {
	
	// Check if the users has selected two characters and realms for each of them. If they have...activate the deals button
	 $('#findDealsButton').removeClass('disabled');
}


function findDeals() {
	
	// Clear out the garbage section
	$('#dataSection')[0].innerHTML = "";
	
	var char1 = $('#character1').val();
	var char2 = $('#character2').val();
	var char3 = $('#character3').val();
	var char4 = $('#character4').val();
	
	var realm1 = $('#realm1').val();
	var realm2 = $('#realm2').val();
	var realm3 = $('#realm3').val();
	var realm4 = $('#realm4').val();
	
	var data = {
		"characters": [],
		"realms": []
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
	
	$.ajax({
		url: 'findDeals.php',
		type: 'POST',
		data: data,
		success:function(response){
		   $('#dataSection')[0].innerHTML += response;
		}
	});
}
