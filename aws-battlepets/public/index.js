

// When the user scrolls down 20px from the top of the document, show the button
window.onscroll = function() {scrollFunction()};

function scrollFunction() {
    if (document.body.scrollTop > 50 || document.documentElement.scrollTop > 50) {
        document.getElementById("backFooter").style.display = "";
    } else {
        document.getElementById("backFooter").style.display = "none";
    }
}



function activateDealsButton() {
	
	// Check if the users has selected two characters and realms for each of them. If they have...activate the deals button
	 $('#findDealsButton').removeClass('disabled');
}


function findDeals() {
	
	// Clear out the garbage section
	$('#tableArea')[0].innerHTML = "";
	$('#buttonBar')[0].innerHTML = "";
	
	/*
	var char1 = $('#character1').val();
	var char2 = $('#character2').val();
	var char3 = $('#character3').val();
	var char4 = $('#character4').val();
	
	var realm1 = $('#realm1').val();
	var realm2 = $('#realm2').val();
	var realm3 = $('#realm3').val();
	var realm4 = $('#realm4').val();
	*/
	
	// For faster testing...
	
	char1 = 'Irone';
	char2 = 'valamyr';
	char3 = 'Lladox';
	char4 = "";
	
	realm1 = 'cenarion-circle';
	realm2 = 'wyrmrest-accord';
	realm3 = 'proudmoore';
	realm4 = "";
	
	var data = {
		"characters": [],
		"realms": [],
		"purpose": ""
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
	
	
	
	data["purpose"] = "tableData";
		$.ajax({
		url: 'findDeals.php',
		type: 'POST',
		data: data,
		success:function(response){			
			$('#tableArea')[0].innerHTML += response;
		}
	});
	
}

function topFunction() {
    document.body.scrollTop = 0; // For Safari
    document.documentElement.scrollTop = 0; // For Chrome, Firefox, IE and Opera
}