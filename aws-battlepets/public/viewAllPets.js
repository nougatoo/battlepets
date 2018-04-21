


var sortOrder = "desc";

$(document).ready(function(){

  	var data = {
		"sortBy": "market_value_hist_median",
		"sortOrder": sortOrder
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


function flipSortOrder() {
	
	if (sortOrder == "desc")
		sortOrder = "asc";
	else
		sortOrder = "desc";
	
}