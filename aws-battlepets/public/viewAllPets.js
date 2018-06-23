
var sortOrder = "desc";
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
	
	$('#currentRegion').html("Region: " + currentRegion);

  	var data = {
		"sortBy": "market_value_hist_median",
		"sortOrder": sortOrder,
		"region": currentRegion
	};
	
  	$.ajax({
		url: 'getAllPetData.php',
		type: 'POST',
		data: data,
		success:function(response){	
			$("#dataFilter").on("keyup", function() {
				var value = $(this).val().toLowerCase();
				$("#myTable1 tr").filter(function() {
					$(this).toggle($(this).text().toLowerCase().indexOf(value) > -1)
				});

			});	
			
			$('#loadingBar').hide();
			$('#dataFilter').show();
			$('#allPetsTable')[0].innerHTML += response;
		}
	});
	
});



function sortTable(n) {
	
	$('#allPetsTable')[0].innerHTML = "";
	flipSortOrder();
	
	if(n ==0) {	
		var data = {
			"sortBy": "name",
			"sortOrder": sortOrder
		};
	} 
	else if (n ==1) {
		var data = {
			"sortBy": "market_value_hist_median",
			"sortOrder": sortOrder
		};	
	}

	$.ajax({
		url: 'getAllPetData.php',
		type: 'POST',
		data: data,
		success:function(response){	
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
	TODO
*/
function flipSortOrder() {
	
	if (sortOrder == "desc")
		sortOrder = "asc";
	else
		sortOrder = "desc";
	
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