
var sortOrder = "desc";
var currentRegion = "";

$(document).ready(function() {
	
	// Use local storage to store the users region
	if(localStorage.getItem("currentRegion") === null) {
		localStorage.setItem("currentRegion", "US"); // Default to US
		currentRegion = "US";
	}
	else {
		currentRegion = localStorage.getItem("currentRegion");
	}
	
	$('#currentRegion').html("Region: " + currentRegion);

	// Data for the getAllPetData.php ajax call
  	var data = {
		"sortBy": "market_value_hist_median",
		"sortOrder": sortOrder,
		"region": currentRegion
	};
	
  	$.ajax({
		url: 'getAllPetData.php',
		type: 'POST',
		data: data,
		success:function(response) {	
			// Attach a keyup function to the filter input
			$("#dataFilter").on("keyup", function() {
				var value = $(this).val().toLowerCase();
				$("#myTable1 tr").filter(function() {
					$(this).toggle($(this).text().toLowerCase().indexOf(value) > -1)
				});
			});	
			
			// Server call is done, attach HTML response
			$('#loadingBar').hide();
			$('#dataFilter').show();
			$('#allPetsTable')[0].innerHTML += response;
		}
	});
});


/**
	Sorts the table by recalling getAllPetData.php with a different SQL sort order.
	This was much faster than a basic sort algorithm for the array.
	
	@param {int} n - 0 if we are sorting by name, 1 if we sort by value
*/
function sortTable(n) {
	
	$('#allPetsTable')[0].innerHTML = "";
	flipSortOrder();
	
	if(n ==0) {	
		var data = {
			"sortBy": "name",
			"sortOrder": sortOrder,
			"region": currentRegion
		};
	} 
	else if (n ==1) {
		var data = {
			"sortBy": "market_value_hist_median",
			"sortOrder": sortOrder,
			"region": currentRegion
		};	
	}

	$.ajax({
		url: 'getAllPetData.php',
		type: 'POST',
		data: data,
		success:function(response){	
			// Attach a keyup function to the filter input
			$("#dataFilter").on("keyup", function() {
				var value = $(this).val().toLowerCase();
				$("#myTable1 tr").filter(function() {
					$(this).toggle($(this).text().toLowerCase().indexOf(value) > -1)
				});
			});	
			
			$('#dataFilter').show();
			$('#allPetsTable')[0].innerHTML += response;
		}
	});
}

/**
	Flips the sort order between desc and asc
	
*/
function flipSortOrder() {
	
	if (sortOrder == "desc")
		sortOrder = "asc";
	else
		sortOrder = "desc";
	
}